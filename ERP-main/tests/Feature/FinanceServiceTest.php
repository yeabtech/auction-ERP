<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Auction;
use App\Models\KycDocument;
use App\Models\Lot;
use App\Models\Role;
use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $financeService;
    protected $bidder;
    protected $seller;
    protected $lot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->financeService = app(FinanceService::class);

        // Create roles
        Role::firstOrCreate(['name' => 'bidder']);
        Role::firstOrCreate(['name' => 'seller']);

        // Create users
        $this->bidder = User::factory()->create();
        $this->bidder->assignRole('bidder');

        $this->seller = User::factory()->create();
        $this->seller->assignRole('seller');

        // KYC approval for bidder
        KycDocument::create([
            'user_id' => $this->bidder->id,
            'document_type' => 'national_id',
            'document_number' => 'ID-123456',
            'file_path' => 'kyc/test.pdf',
            'status' => 'approved',
        ]);

        // Create Asset owned by seller
        $asset = Asset::create([
            'owner_id' => $this->seller->id,
            'title' => 'Test Asset',
            'description' => 'Test Description',
            'category' => 'Vehicles',
            'barcode' => 'AST-9999',
            'valuation_amount' => 10000,
            'warehouse_location' => 'Warehouse B',
            'status' => 'approved',
        ]);

        // Create Auction
        $auction = Auction::create([
            'title' => 'Test Finance Auction',
            'description' => 'Test Description',
            'category' => 'Vehicles',
            'type' => 'english',
            'status' => 'active',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'starting_price' => 100.00,
            'reserve_price' => 500.00,
            'bid_increment_type' => 'flat',
            'bid_increment_value' => 50.00,
            'auto_extend' => true,
            'anti_sniping_minutes' => 5,
            'created_by' => $this->seller->id,
        ]);

        // Create Lot
        $this->lot = Lot::create([
            'auction_id' => $auction->id,
            'asset_id' => $asset->id,
            'lot_number' => 'LOT-777',
            'reserve_price' => 500.00,
            'status' => 'active',
            'sequence_order' => 1,
        ]);
    }

    /** @test */
    public function it_records_deposits_correctly()
    {
        $initialBalance = $this->bidder->walletBalance();

        $deposit = $this->financeService->recordDeposit($this->bidder, 5000.00, 'stripe', 'txn_123456');

        $this->assertDatabaseHas('transactions', [
            'id' => $deposit->id,
            'user_id' => $this->bidder->id,
            'amount' => 5000.00,
            'type' => 'deposit',
            'payment_method' => 'stripe',
            'gateway_reference' => 'txn_123456',
            'status' => 'completed',
        ]);

        $this->assertEquals($initialBalance + 5000.00, $this->bidder->walletBalance());
    }

    /** @test */
    public function it_processes_winner_payments_with_commission_and_payout()
    {
        // Give bidder some money
        $this->financeService->recordDeposit($this->bidder, 10000.00, 'stripe', 'txn_111');

        // Create a bid to establish the lot current price
        $this->lot->bids()->create([
            'bidder_id' => $this->bidder->id,
            'bid_amount' => 1000.00,
            'is_proxy' => false,
            'status' => 'winning',
        ]);

        $result = $this->financeService->processWinnerPayment($this->lot, $this->bidder);

        // Current price = 1000.00
        // Commission = 1000 * 5% = 50.00
        // Vendor Payout = 1000 - 50 = 950.00
        $this->assertEquals(1000.00, $result['payment']->amount);
        $this->assertEquals(50.00, $result['commissionEntry']->amount);
        $this->assertEquals(950.00, $result['payout']->amount);

        // Verify status of lot is sold
        $this->lot->refresh();
        $this->assertEquals('sold', $this->lot->status);

        // Verify transaction logs are saved
        $this->assertDatabaseHas('transactions', [
            'id' => $result['payment']->id,
            'type' => 'purchase_payment',
            'amount' => 1000.00,
            'user_id' => $this->bidder->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $result['commissionEntry']->id,
            'type' => 'commission',
            'amount' => 50.00,
            'user_id' => $this->bidder->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $result['payout']->id,
            'type' => 'payout',
            'amount' => 950.00,
            'user_id' => $this->seller->id,
        ]);
    }

    /** @test */
    public function it_records_refunds_correctly()
    {
        $refund = $this->financeService->processRefund($this->bidder, 250.00, 'Bid deposit refund');

        $this->assertDatabaseHas('transactions', [
            'id' => $refund->id,
            'user_id' => $this->bidder->id,
            'amount' => 250.00,
            'type' => 'refund',
            'status' => 'completed',
        ]);
    }
}

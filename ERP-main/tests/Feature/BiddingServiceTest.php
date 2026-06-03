<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\KycDocument;
use App\Models\Lot;
use App\Models\Role;
use App\Models\User;
use App\Services\BiddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BiddingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $biddingService;
    protected $bidder;
    protected $seller;
    protected $auction;
    protected $lot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->biddingService = app(BiddingService::class);

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

        // Create Asset
        $asset = Asset::create([
            'owner_id' => $this->seller->id,
            'title' => 'Test Item',
            'description' => 'Test Description',
            'category' => 'Vehicles',
            'barcode' => 'AST-0001',
            'valuation_amount' => 10000,
            'warehouse_location' => 'Warehouse A',
            'status' => 'approved',
        ]);

        // Create Live Auction
        $this->auction = Auction::create([
            'title' => 'Test Auction',
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
            'auction_id' => $this->auction->id,
            'asset_id' => $asset->id,
            'lot_number' => 'LOT-001',
            'reserve_price' => 500.00,
            'status' => 'active',
            'sequence_order' => 1,
        ]);
    }

    /** @test */
    public function it_allows_placing_a_valid_bid()
    {
        $bid = $this->biddingService->placeBid($this->lot, $this->bidder, 150.00);

        $this->assertDatabaseHas('bids', [
            'id' => $bid->id,
            'bid_amount' => 150.00,
            'bidder_id' => $this->bidder->id,
            'status' => 'winning',
        ]);
    }

    /** @test */
    public function it_rejects_bids_without_kyc_approval()
    {
        $unapprovedBidder = User::factory()->create();
        $unapprovedBidder->assignRole('bidder');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Your KYC verification must be approved before bidding.');

        $this->biddingService->placeBid($this->lot, $unapprovedBidder, 150.00);
    }

    /** @test */
    public function it_rejects_bids_on_non_active_lots()
    {
        $this->lot->update(['status' => 'sold']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('This lot is not currently active for bidding.');

        $this->biddingService->placeBid($this->lot, $this->bidder, 150.00);
    }

    /** @test */
    public function it_rejects_bids_below_increment()
    {
        // First bid
        $this->biddingService->placeBid($this->lot, $this->bidder, 150.00);

        // Next bid must be at least 150.00 + 50.00 = 200.00
        $anotherBidder = User::factory()->create();
        $anotherBidder->assignRole('bidder');
        KycDocument::create([
            'user_id' => $anotherBidder->id,
            'document_type' => 'national_id',
            'document_number' => 'ID-654321',
            'file_path' => 'kyc/test2.pdf',
            'status' => 'approved',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Bid must be at least 200.00');

        $this->biddingService->placeBid($this->lot, $anotherBidder, 190.00);
    }

    /** @test */
    public function it_prevents_sellers_from_bidding_on_their_own_asset()
    {
        // Approve KYC for seller so they bypass the KYC check first
        KycDocument::create([
            'user_id' => $this->seller->id,
            'document_type' => 'national_id',
            'document_number' => 'ID-555555',
            'file_path' => 'kyc/seller_test.pdf',
            'status' => 'approved',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You cannot bid on your own asset.');

        $this->biddingService->placeBid($this->lot, $this->seller, 150.00);
    }

    /** @test */
    public function it_triggers_proxy_bidding_correctly()
    {
        // Bidder 1 places a proxy bid with a maximum of 500.00
        $bid1 = $this->biddingService->placeBid($this->lot, $this->bidder, 150.00, true, 500.00);

        // Create Bidder 2
        $bidder2 = User::factory()->create();
        $bidder2->assignRole('bidder');
        KycDocument::create([
            'user_id' => $bidder2->id,
            'document_type' => 'national_id',
            'document_number' => 'ID-999999',
            'file_path' => 'kyc/test3.pdf',
            'status' => 'approved',
        ]);

        // Bidder 2 places a regular bid of 200.00
        $bid2 = $this->biddingService->placeBid($this->lot, $bidder2, 200.00);

        // Since Bidder 1 has a proxy bid with max 500.00, it should auto-counter and outbid Bidder 2
        $bid1->refresh();
        $bid2->refresh();

        $this->assertEquals('outbid', $bid2->status);
        $this->assertEquals('winning', $bid1->status);
        // Minimum bid increment is 50.00, so proxy bid amount should be bid2 amount (200) + increment (50) = 250
        $this->assertEquals(250.00, $bid1->bid_amount);
    }
}

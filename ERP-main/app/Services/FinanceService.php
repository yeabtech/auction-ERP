<?php

namespace App\Services;

use App\Models\Lot;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    public const COMMISSION_RATE = 0.05; // 5% default commission

    /**
     * Record a deposit to a user's wallet.
     */
    public function recordDeposit(User $user, float $amount, string $method, string $ref): Transaction
    {
        return Transaction::create([
            'user_id'           => $user->id,
            'amount'            => $amount,
            'type'              => 'deposit',
            'payment_method'    => $method,
            'gateway_reference' => $ref,
            'status'            => 'completed',
            'description'       => "Wallet deposit via {$method}",
        ]);
    }

    /**
     * Process winner payment and commission payout.
     */
    public function processWinnerPayment(Lot $lot, User $winner): array
    {
        return DB::transaction(function () use ($lot, $winner) {
            $amount     = $lot->currentPrice();
            $commission = round($amount * self::COMMISSION_RATE, 2);
            $vendorPayout = $amount - $commission;

            // Debit the winner
            $payment = Transaction::create([
                'user_id'    => $winner->id,
                'lot_id'     => $lot->id,
                'auction_id' => $lot->auction_id,
                'amount'     => $amount,
                'type'       => 'purchase_payment',
                'status'     => 'completed',
                'description' => "Payment for Lot #{$lot->lot_number}: {$lot->asset->title}",
            ]);

            // Commission entry
            $commissionEntry = Transaction::create([
                'user_id'    => $winner->id,
                'lot_id'     => $lot->id,
                'auction_id' => $lot->auction_id,
                'amount'     => $commission,
                'type'       => 'commission',
                'status'     => 'completed',
                'description' => "5% system commission on Lot #{$lot->lot_number}",
            ]);

            // Payout to vendor
            $payout = Transaction::create([
                'user_id'    => $lot->asset->owner_id,
                'lot_id'     => $lot->id,
                'auction_id' => $lot->auction_id,
                'amount'     => $vendorPayout,
                'type'       => 'payout',
                'status'     => 'completed',
                'description' => "Sale proceeds for Lot #{$lot->lot_number}",
            ]);

            // Mark lot as sold
            $lot->update(['status' => 'sold']);

            AuditService::log('winner_payment_processed', Lot::class, $lot->id, null, [
                'winner_id'   => $winner->id,
                'amount'      => $amount,
                'commission'  => $commission,
                'vendor_payout' => $vendorPayout,
            ]);

            return compact('payment', 'commissionEntry', 'payout');
        });
    }

    /**
     * Process a refund to a user.
     */
    public function processRefund(User $user, float $amount, string $description = 'Refund'): Transaction
    {
        return Transaction::create([
            'user_id'    => $user->id,
            'amount'     => $amount,
            'type'       => 'refund',
            'status'     => 'completed',
            'description' => $description,
        ]);
    }
}

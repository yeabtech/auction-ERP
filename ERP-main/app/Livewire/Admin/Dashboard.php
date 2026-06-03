<?php

namespace App\Livewire\Admin;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\KycDocument;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->stats = [
            'total_users'         => User::count(),
            'total_auctions'      => Auction::count(),
            'active_auctions'     => Auction::where('status', 'active')->count(),
            'completed_auctions'  => Auction::where('status', 'completed')->count(),
            'upcoming_auctions'   => Auction::where('status', 'approved')->count(),
            'total_bids'          => Bid::count(),
            'total_revenue'       => Transaction::where('type', 'commission')->where('status', 'completed')->sum('amount'),
            'pending_kyc'         => KycDocument::where('status', 'pending')->count(),
            'highest_bid'         => Bid::max('bid_amount') ?? 0,
            'pending_payments'    => Transaction::where('status', 'pending')->count(),
        ];
    }

    public function approveKyc(int $id): void
    {
        $doc = KycDocument::findOrFail($id);
        $doc->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $this->loadStats();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'KYC document approved.']);
    }

    public function rejectKyc(int $id): void
    {
        $doc = KycDocument::findOrFail($id);
        $doc->update(['status' => 'rejected', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        $this->loadStats();
        $this->dispatch('notify', ['type' => 'warning', 'message' => 'KYC document rejected.']);
    }

    public function render()
    {
        $pendingKycDocs = KycDocument::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        $recentAuctions = Auction::with('creator')
            ->latest()
            ->take(5)
            ->get();

        $recentBids = Bid::with(['bidder', 'lot.asset'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        $monthlyRevenue = Transaction::where('type', 'commission')
            ->where('status', 'completed')
            ->selectRaw("strftime('%m', created_at) as month, SUM(amount) as total")
            ->groupByRaw("strftime('%m', created_at)")
            ->orderByRaw("strftime('%m', created_at)")
            ->pluck('total', 'month');

        return view('livewire.admin.dashboard', compact(
            'pendingKycDocs', 'recentAuctions', 'recentBids', 'monthlyRevenue'
        ))->layout('layouts.admin');
    }
}

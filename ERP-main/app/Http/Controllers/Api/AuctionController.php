<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    /**
     * Display a listing of the auctions.
     */
    public function index(Request $request)
    {
        $query = Auction::withCount('lots');

        // Filter by Search Query
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Default: active or upcoming/completed if desired, let's allow all active and draft etc.
            $query->whereIn('status', ['active', 'completed', 'approved']);
        }

        $auctions = $query->orderBy('start_time', 'desc')
                          ->paginate($request->integer('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $auctions
        ]);
    }

    /**
     * Display the specified auction with its lots.
     */
    public function show(Auction $auction)
    {
        $auction->load(['lots.asset', 'lots.bids' => function ($q) {
            $q->orderBy('bid_amount', 'desc');
        }]);

        $lotsData = $auction->lots->map(function ($lot) {
            return [
                'id' => $lot->id,
                'lot_number' => $lot->lot_number,
                'status' => $lot->status,
                'reserve_price' => $lot->reserve_price,
                'current_price' => $lot->currentPrice(),
                'bids_count' => $lot->bids()->count(),
                'asset' => [
                    'id' => $lot->asset->id,
                    'title' => $lot->asset->title,
                    'description' => $lot->asset->description,
                    'category' => $lot->asset->category,
                    'valuation_amount' => $lot->asset->valuation_amount,
                    'warehouse_location' => $lot->asset->warehouse_location,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $auction->id,
                'title' => $auction->title,
                'description' => $auction->description,
                'category' => $auction->category,
                'type' => $auction->type,
                'status' => $auction->status,
                'start_time' => $auction->start_time,
                'end_time' => $auction->end_time,
                'starting_price' => $auction->starting_price,
                'reserve_price' => $auction->reserve_price,
                'bid_increment_type' => $auction->bid_increment_type,
                'bid_increment_value' => $auction->bid_increment_value,
                'lots' => $lotsData
            ]
        ]);
    }
}

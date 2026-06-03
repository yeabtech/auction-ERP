<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Services\BiddingService;
use Illuminate\Http\Request;

class BidController extends Controller
{
    protected $biddingService;

    public function __construct(BiddingService $biddingService)
    {
        $this->biddingService = $biddingService;
    }

    /**
     * Place a bid on a lot.
     */
    public function placeBid(Request $request)
    {
        $request->validate([
            'lot_id' => 'required|exists:lots,id',
            'amount' => 'required|numeric|gt:0',
            'is_proxy' => 'nullable|boolean',
            'max_proxy_amount' => 'nullable|numeric|required_if:is_proxy,true|gt:amount',
        ]);

        $lot = Lot::findOrFail($request->lot_id);
        $user = $request->user();

        try {
            $bid = $this->biddingService->placeBid(
                $lot,
                $user,
                (float) $request->amount,
                (bool) $request->is_proxy,
                $request->filled('max_proxy_amount') ? (float) $request->max_proxy_amount : null
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Bid placed successfully',
                'data' => [
                    'id' => $bid->id,
                    'lot_id' => $bid->lot_id,
                    'bidder' => [
                        'id' => $bid->bidder->id,
                        'name' => $bid->bidder->name,
                    ],
                    'bid_amount' => $bid->bid_amount,
                    'is_proxy' => $bid->is_proxy,
                    'max_proxy_amount' => $bid->max_proxy_amount,
                    'status' => $bid->status,
                ]
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while placing your bid. Please try again.'
            ], 500);
        }
    }
}

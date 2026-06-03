<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Lot;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Roles
        $roles = [
            'super_admin'     => 'Full system access',
            'auction_manager' => 'Manages auction workflows',
            'auctioneer'      => 'Conducts live auctions',
            'finance_officer' => 'Manages finance and payouts',
            'seller'          => 'Vendor/Seller portal',
            'bidder'          => 'Bidder/Buyer portal',
            'auditor'         => 'Read-only audit access',
        ];
        foreach ($roles as $name => $desc) {
            Role::firstOrCreate(['name' => $name], ['description' => $desc]);
        }

        // 2. Create Users
        $admin = User::firstOrCreate(['email' => 'admin@erp.local'], [
            'name'     => 'Super Admin',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('super_admin');

        $auctioneer = User::firstOrCreate(['email' => 'auctioneer@erp.local'], [
            'name'     => 'Jane Auctioneer',
            'password' => Hash::make('password'),
        ]);
        $auctioneer->assignRole('auctioneer');

        $finance = User::firstOrCreate(['email' => 'finance@erp.local'], [
            'name'     => 'Finance Officer',
            'password' => Hash::make('password'),
        ]);
        $finance->assignRole('finance_officer');

        $seller = User::firstOrCreate(['email' => 'seller@erp.local'], [
            'name'     => 'ABC Vendors Ltd',
            'password' => Hash::make('password'),
        ]);
        $seller->assignRole('seller');

        $bidder1 = User::firstOrCreate(['email' => 'bidder1@erp.local'], [
            'name'     => 'Alice Bidder',
            'password' => Hash::make('password'),
        ]);
        $bidder1->assignRole('bidder');
        // Approve KYC for demo bidder
        $bidder1->kycDocuments()->firstOrCreate(
            ['document_type' => 'national_id'],
            [
                'document_number' => 'ID-123456',
                'file_path'       => 'kyc/demo_id.pdf',
                'status'          => 'approved',
                'reviewed_by'     => $admin->id,
                'reviewed_at'     => now(),
            ]
        );

        $bidder2 = User::firstOrCreate(['email' => 'bidder2@erp.local'], [
            'name'     => 'Bob Bidder',
            'password' => Hash::make('password'),
        ]);
        $bidder2->assignRole('bidder');
        $bidder2->kycDocuments()->firstOrCreate(
            ['document_type' => 'passport'],
            [
                'document_number' => 'PP-789012',
                'file_path'       => 'kyc/demo_pp.pdf',
                'status'          => 'approved',
                'reviewed_by'     => $admin->id,
                'reviewed_at'     => now(),
            ]
        );

        // 3. Give bidders wallet balance
        Transaction::firstOrCreate(['user_id' => $bidder1->id, 'type' => 'deposit', 'gateway_reference' => 'SEED-001'], [
            'amount' => 50000, 'payment_method' => 'bank_transfer', 'status' => 'completed',
            'description' => 'Demo wallet top-up',
        ]);
        Transaction::firstOrCreate(['user_id' => $bidder2->id, 'type' => 'deposit', 'gateway_reference' => 'SEED-002'], [
            'amount' => 30000, 'payment_method' => 'bank_transfer', 'status' => 'completed',
            'description' => 'Demo wallet top-up',
        ]);

        // 4. Create Assets
        $assetData = [
            ['title' => '2022 Toyota Land Cruiser', 'category' => 'Vehicle', 'valuation_amount' => 45000],
            ['title' => 'Commercial Warehouse – Plot 14B', 'category' => 'Property', 'valuation_amount' => 120000],
            ['title' => 'Caterpillar 320 Excavator', 'category' => 'Equipment', 'valuation_amount' => 85000],
            ['title' => 'Dell PowerEdge Server Rack (x10)', 'category' => 'Electronics', 'valuation_amount' => 18000],
        ];

        $assets = [];
        foreach ($assetData as $i => $data) {
            $assets[] = Asset::firstOrCreate(
                ['title' => $data['title'], 'owner_id' => $seller->id],
                array_merge($data, [
                    'description'       => "High-quality {$data['category']} available for auction.",
                    'barcode'           => 'AST-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                    'warehouse_location' => 'Warehouse A, Bay ' . ($i + 1),
                    'status'            => 'approved',
                ])
            );
        }

        // 5. Create Auction
        $auction = Auction::firstOrCreate(
            ['title' => 'Q3 2026 Asset Disposal Auction'],
            [
                'description'        => 'A premier mixed-asset disposal auction featuring vehicles, property, equipment and electronics.',
                'category'           => 'Mixed Assets',
                'type'               => 'english',
                'status'             => 'active',
                'start_time'         => now()->subHour(),
                'end_time'           => now()->addHours(48),
                'starting_price'     => 1000.00,
                'reserve_price'      => 5000.00,
                'bid_increment_type' => 'flat',
                'bid_increment_value'=> 500.00,
                'auto_extend'        => true,
                'anti_sniping_minutes' => 5,
                'created_by'         => $admin->id,
                'auctioneer_id'      => $auctioneer->id,
            ]
        );

        // 6. Create Lots and seed some bids
        foreach ($assets as $index => $asset) {
            $lot = Lot::firstOrCreate(
                ['auction_id' => $auction->id, 'asset_id' => $asset->id],
                [
                    'lot_number'    => 'LOT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'reserve_price' => $asset->valuation_amount * 0.7,
                    'status'        => 'active',
                    'sequence_order' => $index + 1,
                ]
            );

            // Seed some bids on first two lots
            if ($index < 2) {
                Bid::firstOrCreate(
                    ['lot_id' => $lot->id, 'bidder_id' => $bidder1->id, 'bid_amount' => $asset->valuation_amount * 0.55],
                    ['is_proxy' => false, 'status' => 'outbid', 'created_at' => now()->subMinutes(30)]
                );
                Bid::firstOrCreate(
                    ['lot_id' => $lot->id, 'bidder_id' => $bidder2->id, 'bid_amount' => $asset->valuation_amount * 0.65],
                    ['is_proxy' => false, 'status' => 'winning', 'created_at' => now()->subMinutes(10)]
                );
            }
        }

        // 7. System Settings
        $settings = [
            ['key' => 'commission_rate',    'value' => '5',        'group' => 'commission'],
            ['key' => 'vat_rate',           'value' => '15',       'group' => 'commission'],
            ['key' => 'default_currency',   'value' => 'USD',      'group' => 'system'],
            ['key' => 'min_deposit_amount', 'value' => '500',      'group' => 'payment'],
            ['key' => 'site_name',          'value' => 'AuctionERP','group' => 'system'],
        ];
        foreach ($settings as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }
    }
}

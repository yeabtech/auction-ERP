<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('type'); // english, dutch, reverse, sealed, timed
            $table->string('status')->default('draft'); // draft, under_review, approved, active, paused, completed, cancelled
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('starting_price', 15, 2)->default(0.00);
            $table->decimal('reserve_price', 15, 2)->default(0.00);
            $table->string('bid_increment_type')->default('flat'); // flat, percentage
            $table->decimal('bid_increment_value', 15, 2)->default(1.00);
            $table->boolean('auto_extend')->default(false);
            $table->integer('anti_sniping_minutes')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('auctioneer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('auction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('lot_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('type'); // deposit, refund, purchase_payment, commission, payout
            $table->string('payment_method')->nullable(); // stripe, paypal, chapa, bank_transfer
            $table->string('gateway_reference')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

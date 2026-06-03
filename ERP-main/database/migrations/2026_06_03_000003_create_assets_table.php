<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('barcode')->nullable();
            $table->decimal('valuation_amount', 15, 2)->default(0.00);
            $table->string('warehouse_location')->nullable();
            $table->string('status')->default('registered'); // registered, under_review, approved, sold, disposed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

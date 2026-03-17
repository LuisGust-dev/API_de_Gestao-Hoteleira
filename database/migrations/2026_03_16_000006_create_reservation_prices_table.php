<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained('reservations')->cascadeOnDelete();
            $table->unsignedBigInteger('rate_id')->nullable();
            $table->date('reference_date');
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->foreign('rate_id')->references('id')->on('rates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_prices');
    }
};

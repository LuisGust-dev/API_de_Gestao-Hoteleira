<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->unsignedBigInteger('room_reservation_id')->unique();
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->date('booked_at_date');
            $table->time('booked_at_time');
            $table->date('arrival_date');
            $table->date('departure_date');
            $table->string('currency_code', 3);
            $table->string('meal_plan')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            $table->index(['room_id', 'arrival_date', 'departure_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

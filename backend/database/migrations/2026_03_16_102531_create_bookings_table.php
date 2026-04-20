<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void {
    Schema::create('bookings', function (Blueprint $table) {
        $table->id();
        $table->date('date');
        $table->decimal('total_price', 10, 2);
        $table->string('status')->default('PENDING');
        $table->foreignId('traveler_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('tour_id')->constrained('tours')->onDelete('cascade');
        $table->foreignId('guide_id')->constrained('users')->onDelete('cascade');
        $table->timestamp('reminder_sent_at')->nullable();
        $table->string('payment_status')->default('UNPAID');
        $table->timestamp('paid_at')->nullable();
        $table->timestamp('payment_receipt_sent_at')->nullable();
        $table->string('paypal_order_id')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

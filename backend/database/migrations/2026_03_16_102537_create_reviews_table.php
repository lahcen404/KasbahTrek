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
    Schema::create('reviews', function (Blueprint $table) {
        $table->id();
        $table->integer('rating');
        $table->text('comment');
        $table->foreignId('traveler_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('tour_id')->constrained('tours')->onDelete('cascade');
        $table->timestamps();
        $table->unique(['traveler_id', 'tour_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

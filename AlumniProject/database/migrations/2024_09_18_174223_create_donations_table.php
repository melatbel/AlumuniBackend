<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id('donation_id');
            $table->text('description');
            $table->decimal('amount', 8, 2);
            $table->foreignId('donated_by')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('cause_id')->constrained('donation_causes', 'id')->onDelete('cascade');
            $table->dateTime('date_donated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};

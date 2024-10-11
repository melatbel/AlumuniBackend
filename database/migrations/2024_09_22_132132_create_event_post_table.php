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
        Schema::create('event_post', function (Blueprint $table) {
            $table->id();
            $table->string('event_title');
            $table->mediumText('description');
            $table->dateTime('dateTime'); 
            $table->string('location');
            $table->string('image');
            //$table->foreignId('created_by')->constrained('users');
            $table->foreignId('posted_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['event_title', 'description', 'dateTime', 'location']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_post');
    }
};

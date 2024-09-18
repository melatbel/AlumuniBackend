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
        Schema::create('job__posts', function (Blueprint $table) {
            $table->id('job_post_id');
            $table->string('title');
            $table->text('description');
            $table->foreignId('posted_by')->constrained('users', 'user_id')->onDelete('cascade');
            $table->string('location');
            $table->dateTime('deadline');
            $table->dateTime('date_posted');
            $table->string('contact_email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job__posts');
    }
};

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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->char('gender', 1);
            $table->year('graduation_year');
            $table->string('address_city');
            $table->string('professional_field')->nullable();
            $table->enum('role', ['alumni', 'admin','student']);
            $table->string('ID')->nullable();   //File path for ID
            $table->string('tempo')->nullable();    //File path for Tempo
            $table->string('Department');
            $table->string('profile_picture')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('social_media_link')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

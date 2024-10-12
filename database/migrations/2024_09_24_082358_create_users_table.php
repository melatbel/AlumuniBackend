<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
         
            $table->id(); // Primary key
            $table->string('first_name');
            $table->string('middle_name');
            $table->string('last_name');
            $table->string('gender');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('user_type')->default('student'); // alumni or student, with default value
            $table->string('id_path')->nullable(); // Store the file path of the uploaded ID
            $table->string('certificate_path')->nullable(); // Store the file path of the uploaded certificate
            $table->string('department');
            $table->integer('batch')->nullable(); // Optional for alumni
            $table->string('phone_number');
            $table->string('linkedin_profile')->nullable(); // LinkedIn is optional
            
            $table->timestamps(); // created_at, updated_at columns

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
               
            ]);
        });
    }
}

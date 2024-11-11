<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin','user'])->default('user');
            $table->rememberToken();
            $table->timestamps();
        });

DB::table('users')->insert([
    [
        'name' => 'Mr. User',
        'email' => 'user@gmail.com',
        'email_verified_at' => now(),
        'password' => Hash::make('12345678'), 
        'role' => 'user',
        'remember_token' => Str::random(10),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'name' => ' Mr. Admin',
        'email' => 'admin@gmail.com',
        'email_verified_at' => now(),
        'password' => Hash::make('12345678'), 
        'role' => 'admin',
        'remember_token' => Str::random(10),
        'created_at' => now(),
        'updated_at' => now(),
    ],
]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

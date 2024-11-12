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
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('occupation')->nullable();
            $table->string('password');
            $table->enum('role', ['admin','user'])->default('user');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });

DB::table('users')->insert([
    [
        'first_name' => 'Mr.',
        'last_name' => 'User',
        'email' => 'user@gmail.com',
        'occupation' => 'Web Developer',
        'email_verified_at' => now(),
        'password' => Hash::make('12345678'), 
        'role' => 'user',
        'remember_token' => Str::random(10),
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'first_name' => 'Mr.',
        'last_name' => 'Admin',
        'email' => 'admin@gmail.com',
        'occupation' => 'Bussinessman',
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

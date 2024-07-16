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
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('temp')->nullable();
            $table->string('contact')->nullable();
            $table->string('forgot_password')->nullable();
            $table->text('business')->nullable();
            $table->integer('portal_id')->nullable();
            $table->float('commission_rate')->default(10)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->string('password');
            $table->string('otp')->nullable();
            $table->text('jwt_token')->nullable();
            $table->text('jwt_password')->nullable();
            $table->boolean('is_verified')->default(false);
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

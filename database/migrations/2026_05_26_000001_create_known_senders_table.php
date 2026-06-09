<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('known_senders', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255)->index();
            $table->string('sender_email', 255);
            $table->enum('status', ['approved', 'blocked'])->default('approved');
            $table->timestamps();
            $table->unique(['user_email', 'sender_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('known_senders');
    }
};

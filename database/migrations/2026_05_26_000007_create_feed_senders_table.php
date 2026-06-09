<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feed_senders', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255)->index();
            $table->string('sender_email', 255);
            $table->timestamps();
            $table->unique(['user_email', 'sender_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_senders');
    }
};

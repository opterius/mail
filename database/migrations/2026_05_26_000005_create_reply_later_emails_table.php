<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reply_later_emails', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255)->index();
            $table->unsignedBigInteger('imap_uid');
            $table->string('mailbox', 255)->default('INBOX');
            $table->string('subject', 998)->nullable();
            $table->string('from_email', 255)->nullable();
            $table->string('from_name', 255)->nullable();
            $table->timestamps();
            $table->unique(['user_email', 'imap_uid', 'mailbox']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reply_later_emails');
    }
};

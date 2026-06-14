<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('snoozed_emails', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255)->index();
            $table->unsignedBigInteger('imap_uid');
            $table->string('mailbox', 255)->default('INBOX');
            $table->string('subject', 998)->nullable();
            $table->string('from_email', 255)->nullable();
            $table->string('from_name', 255)->nullable();
            $table->timestamp('snooze_until');
            $table->timestamps();
            $table->index(['user_email', 'snooze_until']);
            // Hot path: SnoozedEmail::isSnoozed($userEmail, $uid, $mailbox)
            // is called on every message view to decide whether to surface
            // a "still snoozed" banner. Without this composite, the query
            // falls back to the (user_email) index and table-scans the
            // user's snoozed rows.
            $table->index(['user_email', 'mailbox', 'imap_uid'], 'snoozed_emails_user_mailbox_uid_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snoozed_emails');
    }
};

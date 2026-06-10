<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sender reputation counters. One row per (user_email, sender_email)
 * pair, updated as the user interacts with messages from a given
 * sender. The combination of counters drives the trust badge shown
 * next to the From line.
 *
 * Counters are bumped from MessageController on the relevant actions
 * (received via inbox sync, replied via compose.send, etc.) - never
 * recomputed by full inbox scan.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('sender_stats', function (Blueprint $table) {
            $table->id();

            // The recipient mailbox owner.
            $table->string('user_email', 255)->index();
            // The remote sender.
            $table->string('sender_email', 255);

            // Lifetime counters.
            $table->unsignedInteger('received_count')->default(0);
            $table->unsignedInteger('replied_count')->default(0);
            $table->unsignedInteger('sent_to_count')->default(0);
            $table->unsignedInteger('deleted_unread_count')->default(0);
            $table->unsignedInteger('marked_spam_count')->default(0);
            $table->unsignedInteger('snoozed_count')->default(0);

            // First-contact timestamp - used for 'new sender' badge.
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_received_at')->nullable();
            $table->timestamp('last_replied_at')->nullable();
            $table->timestamp('last_sent_to_at')->nullable();

            $table->timestamps();

            $table->unique(['user_email', 'sender_email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sender_stats');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scheduled_emails', function (Blueprint $table) {
            $table->id();
            $table->string('user_email', 255)->index();
            $table->string('to', 2000);
            $table->string('cc', 2000)->nullable();
            $table->string('bcc', 2000)->nullable();
            $table->string('subject', 998)->nullable();
            $table->longText('body')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamp('send_at');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->text('error')->nullable();
            $table->timestamps();
            // Hot path: ProcessScheduledEmails worker scans
            //   WHERE status = 'pending' AND send_at <= NOW()
            // every minute. With (status) alone it scans every pending row;
            // the composite lets it short-circuit on the send_at range.
            $table->index(['status', 'send_at'], 'scheduled_emails_status_send_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_emails');
    }
};

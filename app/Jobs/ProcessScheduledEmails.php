<?php

namespace App\Jobs;

use App\Models\ScheduledEmail;
use App\Models\UserSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class ProcessScheduledEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $due = ScheduledEmail::where('status', 'pending')
            ->where('send_at', '<=', now())
            ->get();

        foreach ($due as $scheduled) {
            try {
                $this->sendEmail($scheduled);
                $scheduled->update(['status' => 'sent']);
            } catch (\Throwable $e) {
                Log::error("Scheduled email {$scheduled->id} failed: " . $e->getMessage());
                $scheduled->update(['status' => 'failed', 'error' => $e->getMessage()]);
            }
        }
    }

    private function sendEmail(ScheduledEmail $scheduled): void
    {
        $settings = UserSetting::where('email', $scheduled->user_email)->first();

        $smtpHost = config('mail-ui.smtp_host', env('MAIL_HOST', 'localhost'));
        $smtpPort = config('mail-ui.smtp_port', env('MAIL_PORT', 587));
        $smtpUser = $scheduled->user_email;
        $smtpPass = session('imap_password', ''); // May not be available in job context

        // Use Laravel mailer configured in .env as fallback
        $transport = Transport::fromDsn(
            sprintf('smtp://%s:%s@%s:%d', urlencode($smtpUser), urlencode($smtpPass), $smtpHost, $smtpPort)
        );

        $mailer = new \Symfony\Component\Mailer\Mailer($transport);

        $toAddresses = array_map('trim', explode(',', $scheduled->to));
        $email = (new Email())
            ->from(new Address($scheduled->user_email, $settings?->display_name ?? ''))
            ->subject($scheduled->subject ?? '(no subject)')
            ->html($scheduled->body ?? '');

        foreach ($toAddresses as $to) {
            if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $email->addTo($to);
            }
        }

        if ($scheduled->cc) {
            foreach (array_map('trim', explode(',', $scheduled->cc)) as $cc) {
                if (filter_var($cc, FILTER_VALIDATE_EMAIL)) $email->addCc($cc);
            }
        }
        if ($scheduled->bcc) {
            foreach (array_map('trim', explode(',', $scheduled->bcc)) as $bcc) {
                if (filter_var($bcc, FILTER_VALIDATE_EMAIL)) $email->addBcc($bcc);
            }
        }

        $mailer->send($email);
    }
}

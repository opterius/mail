<?php

namespace App\Jobs;

use App\Models\SenderStat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Background-bump a sender-reputation counter for one (user, sender) pair.
 *
 * Why this exists:
 *   SenderStat::bump runs up to three DB queries (SELECT + maybe INSERT +
 *   UPDATE). Inline on every message view that becomes a hard cost at
 *   tens of thousands of active accounts. The view itself doesn't read
 *   the result of this particular bump - the trust badge is computed
 *   from historical counters that this view only adds 1 to - so a
 *   second of staleness is invisible.
 *
 * The job is tiny + idempotent-ish, so we don't need retries; failures
 * just mean a single counter slips by 1.
 */
class BumpSenderStat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 5;

    public function __construct(
        public string $userEmail,
        public string $senderEmail,
        public string $field,
        public int $by = 1,
        public ?string $timestampField = null,
    ) {}

    public function handle(): void
    {
        SenderStat::bump(
            $this->userEmail,
            $this->senderEmail,
            $this->field,
            $this->by,
            $this->timestampField,
        );
    }
}

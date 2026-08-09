<?php

namespace App\Jobs;

use App\Models\HealthRecord;
use App\Notifications\HealthReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessHealthReminder implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public HealthRecord $healthRecord,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pet = $this->healthRecord->pet;
        
        if (!$pet || !$pet->user) {
            return;
        }

        $pet->user->notify(
            new HealthReminderNotification($this->healthRecord)
        );
    }
}


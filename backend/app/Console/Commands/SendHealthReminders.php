<?php

namespace App\Console\Commands;

use App\Jobs\ProcessHealthReminder;
use App\Models\HealthRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendHealthReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health:reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send health reminders for pets with upcoming health record reviews';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting health reminders job...');

        // Find all health records with reminders active and due within 3 days
        $healthRecords = HealthRecord::query()
            ->where('notifications_active', true)
            ->whereNotNull('next_due_date')
            ->whereBetween('next_due_date', [
                now(),
                now()->addDays(3),
            ])
            ->get();

        $count = $healthRecords->count();

        if ($count === 0) {
            $this->info('No pending health reminders to send.');
            return Command::SUCCESS;
        }

        $this->line("Found {$count} health record(s) requiring reminders.");

        foreach ($healthRecords as $healthRecord) {
            ProcessHealthReminder::dispatch($healthRecord);
            $this->line("Dispatched reminder for health record ID: {$healthRecord->id}");
        }

        $this->info('Health reminders job completed successfully.');
        return Command::SUCCESS;
    }
}


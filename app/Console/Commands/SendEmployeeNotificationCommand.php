<?php

namespace App\Console\Commands;

use App\Mail\EmployeeNotificationMail;
use App\Models\User;
use App\Services\OneSignalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmployeeNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-employee-notification {userId} {title} {message} {actionedBy?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send async email and OneSignal push notification to an employee in background';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId     = $this->argument('userId');
        $title      = $this->argument('title');
        $message    = $this->argument('message');
        $actionedBy = $this->argument('actionedBy') ?: null;

        $user = User::find($userId);
        if (!$user) {
            Log::warning("Background Notification: User {$userId} not found.");
            return self::FAILURE;
        }

        // 1. إرسال الإيميل الرسمي
        if (!empty($user->email)) {
            try {
                Mail::to($user->email)->send(
                    new EmployeeNotificationMail($user->name, $title, $message, $actionedBy)
                );
                Log::info("Background Notification: Email sent to {$user->email}");
            } catch (\Throwable $e) {
                Log::error("Background Notification: Email error: " . $e->getMessage());
            }
        }

        // 2. إرسال إشعار OneSignal السحابي
        try {
            OneSignalService::sendToUser($userId, $title, $message);
        } catch (\Throwable $e) {
            Log::error("Background Notification: OneSignal error: " . $e->getMessage());
        }

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CqcVisit;
use App\Jobs\SendEmailJob;
use Illuminate\Console\Command;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;

class SendCqcVisitPeriodicReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-cqc-visit-periodic-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send  CQC visit reminder emails to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendPeriodicReminders();
    }
    // Send Email Every Duration with 5 days
    private function sendPeriodicReminders()
    {
        $cqcVisits = CqcVisit::whereDate('visit_date', '>', date('Y-m-d'))->get();
        foreach ($cqcVisits as $cqcVisit) {
            $date = Carbon::parse($cqcVisit->visit_date);
            $now = Carbon::now();

            $diff = $date->diffInDays($now);
            $days_left = $diff % 5;

            if ($days_left == 0) {

                $content =  "I hope this email finds you well. We want to bring your attention to an important upcoming event: <br /> <b> CQC Visit</b> <b> Date: </b>" . Carbon::parse($cqcVisit->visit_date)->format('d-m-Y') . "<br > <b>Time:</b> " . Carbon::parse($cqcVisit->visit_date)->format('H:i:s') . ' <br /> In preparation for the visit, it is imperative that all staff members complete their related tasks promptly. This ensures we are fully prepared and can showcase our commitment to quality care.<br /> <br /> Kindly review your assigned tasks on our platform and ensure their completion before the scheduled visit. If you have any questions or face challenges, do not hesitate to reach out for assistance.<br /><br /> Your cooperation is crucial in ensuring the success of this visit. Let us work together to highlight our dedication to excellence.<br /><br /> Thank you for your prompt attention to this matter.';
                if ($cqcVisit) {
                    $companyUsers = User::where('id', $cqcVisit->created_by)->whereCompanyId($cqcVisit->company_id)->get();

                    $visitDate = Carbon::parse($cqcVisit->visit_date)->format('l, F j, Y');
                    foreach ($companyUsers as $user) {
                        $this->sendEmail($user, $cqcVisit, $visitDate, $content);
                    }
                }

                Notification::send($cqcVisit->company->users, new GeneralNotification($cqcVisit, 'CQC', 'Reminder', "Important Notice: CQC Visit scheduled for " . Carbon::parse($cqcVisit->visit_date)->format('d-m-Y') . " at " . Carbon::parse($cqcVisit->visit_date)->format('H:i:s') . ". Check your email for details and ensure readiness. ", "Urgent: CQC Visit - Preparations Required", $content));
                Notification::send($cqcVisit->company->manager, new GeneralNotification($cqcVisit, 'CQC', 'Reminder', "Urgent: CQC Visit scheduled for " . Carbon::parse($cqcVisit->visit_date)->format('d-m-Y') . " at " . Carbon::parse($cqcVisit->visit_date)->format('H:i:s') . ". Check your email for details. Ensure team readiness and contact our support team for any questions.", "Urgent: CQC Visit - Preparations Required", $content));
            }
        }
        info("Command: Send CQC Visit Reminder Periodic Reminder");
    }

    private function sendEmail($user, $cqcVisit, $visitDate, $message)
    {
        $emailData = [
            "name"          =>  $user->first_name,
            "email"         =>  $user->email,
            "subject"       =>  "Friendly Reminder: CQC Visit on " . $visitDate,
            "body"          =>  $message,
            'button_url'    =>  'https://portal.mychex.co.uk/',
            'button_text'   =>  "Login",
            'logo'          => ($cqcVisit->company?->photo != null) ? $cqcVisit->company?->photo->path : "public/logo.png",
        ];

        return SendEmailJob::dispatch($emailData);
    }
}

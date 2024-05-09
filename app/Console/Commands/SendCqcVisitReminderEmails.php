<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CqcVisit;
use App\Jobs\SendEmailJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;
use App\Services\GeneralService;

class SendCqcVisitReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-cqc-visit-reminder-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send cqc visit reminder emails to users';

    protected $generalService;

    public function __construct(GeneralService $generalService)
    {
        parent::__construct();
        $this->generalService = $generalService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Command: Send CQC Visit Reminder Reminder Email");
        $this->sendDailyReminders();
    }
    
    // RUN Daily Last 5 Days (Daily Based Reminder)
    private function sendDailyReminders()
    {
        $cqcVisits = CqcVisit::whereBetween('visit_date', [Carbon::today()->format('Y-m-d'), Carbon::now()->addDays(5)->format('Y-m-d') ])
        ->get();
        
        foreach ($cqcVisits as $cqcVisit) {
            $content = "As a reminder, our upcoming CQC Visit is scheduled for: <br /><br /> <b> Date: </b>" . Carbon::parse($cqcVisit->visit_date)->format('l, F j, Y') . "<br /><br /> <b>Time: </b>" . Carbon::parse($cqcVisit->visit_date)->format('H:i:s') . ' <br /><br /> Please take a moment to review your tasks on our platform and ensure their timely completion. <br /><br /> Your cooperation is highly appreciated.';

            if ($cqcVisit) {
                if(Carbon::parse($cqcVisit->visit_date)->format('Y-m-d') == Carbon::now()->format('Y-m-d')) {
                    $content = "This is a gentle reminder that we have a Care Quality Commission (CQC) visit scheduled for today. As you know, these visits are crucial for maintaining our standards of care and ensuring risk with regulations. <br /><br /> Here are a few important points to keep in mind: <br /><br /><b>Preparation:</b> Please ensure that all necessary documentation is up-to-date and easily accessible. This includes care plans, risk assessments, staff training records, and any other relevant paperwork. <br /><br /><b>Staff Awareness:</b> All staff should be aware of the CQC visit and be prepared to answer any questions that may arise. Remember to be honest and transparent in your responses. <br /><br />Thank you for your dedication and hard work. Let's work together to ensure a successful and positive CQC visit today.";
                }
                
                $companyUsers = User::whereCompanyId($cqcVisit->company_id)->get();
                $visitDate = Carbon::parse($cqcVisit->visit_date)->format('l, F j, Y');
                $managerNotificationContent = "Time Sensitive! Friendly reminder about the " . (Carbon::parse($cqcVisit->visit_date)->format('Y-m-d') == Carbon::now()->format('Y-m-d') ? 'today' : 'upcoming') . " visit scheduled for ". Carbon::parse($cqcVisit->visit_date)->format('Y-m-d')." at ". Carbon::parse($cqcVisit->visit_date)->format('H:i:s').". Check your email for details and ensure readiness.";
                $notManagerNotificationContent = "Friendly reminder about the " . (Carbon::parse($cqcVisit->visit_date)->format('Y-m-d') == Carbon::now()->format('Y-m-d') ? 'today' : 'upcoming') . " visit scheduled for ". Carbon::parse($cqcVisit->visit_date)->format('Y-m-d')." at ". Carbon::parse($cqcVisit->visit_date)->format('H:i:s').". Check your email for details.";
                foreach ($companyUsers as $user) {
                    $this->sendEmail($user, $cqcVisit, $visitDate, $content);
                    Notification::send($user, new GeneralNotification($cqcVisit, 'CQC', 'Reminder', ($user->getRoleNames()[0] == 'Manager' ? $managerNotificationContent : $notManagerNotificationContent), "Reminder: CQC Visit - Complete Your Tasks", $content));
                }
                Notification::send(User::where('id', $cqcVisit->created_by)->whereCompanyId($cqcVisit->company_id)->first(), new GeneralNotification($cqcVisit, 'CQC', 'Reminder', $managerNotificationContent, "Reminder: CQC Visit - Complete Your Tasks", $content));
                $this->generalService->sendFCMNotification($companyUsers, "Reminder", "Gentle reminder regarding the " . (Carbon::parse($cqcVisit->visit_date)->format('Y-m-d') == Carbon::now()->format('Y-m-d') ? 'today' : 'upcoming') . " CQC visit.");
            }
        }
    }

    private function sendEmail($user, $cqcVisit, $visitDate, $message)
    {
        $emailData = [
            "name"          =>  $user->first_name,
            "email"         =>  $user->email,
            "subject"       =>  "Reminder: CQC Visit - Complete Your Tasks",
            "body"          =>  $message,
            'button_url'    =>  'http://localhost:9000/',
            'button_text'   =>  "Login",
            'logo'          => ($cqcVisit->company->photo != null) ? $cqcVisit->company->photo->path : "public/logo.png",
        ];

        return SendEmailJob::dispatch($emailData);
    }
}

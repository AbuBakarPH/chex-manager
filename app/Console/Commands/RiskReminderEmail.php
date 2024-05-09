<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\QuestionRisk;
use Illuminate\Console\Command;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;


class RiskReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:risk-reminder-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $risks = QuestionRisk::with('assignees', 'question')->whereBetween('due_date', [date('Y-m-d', strtotime("-1 days")), date('Y-m-d', strtotime("+1 days"))])->get();
        $subject = 'Risk Reminder';
        foreach ($risks as $risk) {
            $content = "This is a gentle reminder regarding the pending Risk assigned to you. <br /><br /> Details of the pending Risk: <br /> <b>Risk Title:</b> " . $risk->question->title . "<br /><b>Description:</b>" . $risk->description . '<br /><br /> Please take the necessary steps to mitigate or resolve this Risk at your earliest convenience.<br /><br />Thank you for your attention to this matter.';
            Notification::send($risk->assignees, new GeneralNotification($risk, 'Risk', 'Reminder', "Risk Reminder: Urgent attention required! Check your email for details about an ongoing Risk. Take necessary actions promptly.", $subject, $content));
            foreach ($risk->assignees as $user) {
                $this->sendEmail($user, $subject, $content);
            }
        }
        
        Notification::send(Auth::user(), new GeneralNotification($risk, 'Risk', 'Reminder', "Urgent attention required! Check your email for details about an ongoing Risk. Coordinate with the team for prompt mitigation.", $subject, $content));
        $this->info('Risk Reminder');
    }

    public function sendEmail($model, $subject, $content)
    {
        $email_data = [
            "name"          =>  $model->first_name,
            "email"         =>  $model->email,
            "subject"       =>  $subject,
            "body"          =>  $content,
            'button_url'    =>  url('/'),
            'button_text'   =>  "Login",
            'logo'          => (Auth::user()->company != null) ? Auth::user()->company->photo->path : "public/logo.png",
        ];
        dispatch(new SendEmailJob($email_data));
    }
}

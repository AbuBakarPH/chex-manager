<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use App\Models\Config;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TaskReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:task-reminder-command';

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
        $schedules = Schedule::with(['checklist_config' => function ($query) {
            $query->where('repeat', '!=', 'daily');
        }])->where('status', 'in_progress')->get();
        foreach ($schedules as $schedule) {
            if ($schedule->checklist_config == null) {
                continue;
            }
            $date = Carbon::parse($schedule->due_date);
            $now = Carbon::now();
            $diff = $date->diffInDays($now);
            if ($schedule->due_date > date('Y-m-d')) {

                if ($schedule->checklist_config->repeat == 'weekly' && ($diff == 3 || $diff == 2 || $diff == 1)) {
                    $this->sendEmailToUser($schedule);
                } else if ($schedule->checklist_config->repeat == 'monthly' && ($diff / 5 == 3 || $diff / 5 == 2 || $diff / 5 == 1)) {
                    $this->sendEmailToUser($schedule);
                } else if ($schedule->checklist_config->repeat == 'yearly' && (round($diff / 30) == 3 || round($diff / 30) == 2 || round($diff / 30) == 1)) {
                    $this->sendEmailToUser($schedule);
                }
            }
        }
    }

    private function sendEmailToUser($schedule)
    {
        $users = User::whereIn('id', $schedule->checklist_config->staffPivot->pluck('user_id')->toArray())->get();
        foreach ($users as $user) {
            $content = '<span>This is a friendly reminder regarding the checklist ' . $schedule->checklist_config->task->name . '<br /><br /><b>Checklist Reminder Details:</b><br /> <b>Checklist Title</b>: ' . $schedule->checklist_config->task->name . '<br /> <b>Deadline:</b> ' . date('d-m-Y', strtotime('+1 day', strtotime(date('Y-m-d')))) . '<br /> <b>Peroid Type:</b> ' . $schedule->checklist_config->repeat . ' <br /><br /> If you have any questions or need further assistance, feel free to contact our team at <a href="mailto:info@mychex.co.uk"> info@mychex.co.uk</a></span>';
            $this->sendEmail($user, 'Checklist Reminder Email', $content);
        }
    }
    public function sendEmail($model, $subject, $content, $url = null)
    {
        $email_data = [
            "name"          =>  $model->name,
            "email"         =>  $model->email,
            "subject"       =>  $subject,
            "body"          =>  $content,
            'button_url'    =>  $url  == null ?  'https://portal.mychex.co.uk/' : $url,
            'button_text'   =>  "Login",
            'logo'          => ($model->company != null) ? $model->company?->photo?->path : "public/logo.png",
        ];
        dispatch(new SendEmailJob($email_data));
    }
}

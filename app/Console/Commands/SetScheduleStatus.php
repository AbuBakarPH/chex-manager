<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use Illuminate\Console\Command;
use Carbon\Carbon;


class SetScheduleStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-schedule-status';

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
        info("Command: Set Schedule Status");
        $schedules = Schedule::with('checklist_config')
            ->whereDate('due_date', '<', date('Y-m-d'))
            ->where('status', 'in_progress')
            ->get();
        foreach ($schedules as $schedule) {
            // $next_turn = Schedule::where('config_id', $schedule->config_id)->count();
            // $date = Carbon::createFromFormat('Y-m-d', $schedule->checklist_config->repeat_start_dd);

            // if ($schedule->checklist_config->repeat == 'daily') {
            //     $add_day = $date->addDays($next_turn);
            // } else if ($schedule->checklist_config->repeat == 'weekly') {
            //     $add_day = $date->addWeeks($next_turn);
            // } elseif ($schedule->checklist_config->repeat == 'monthly') {
            //     $add_day = $date->addMonths($next_turn);
            // } elseif ($schedule->checklist_config->repeat == 'yearly') {
            //     $add_day = $date->addYear($next_turn);
            // }

            // if ($schedule->checklist_config->repeat != 'daily') {
            //     $add_day = $add_day->addDays(1);
            // }

            // if ($add_day->format('Y-m-d') <= date('Y-m-d')) { 
            $schedule->update(['status' => 'in_complete']);
            // }
        }

        $this->info('Command Executed');
        return 0;
    }
}

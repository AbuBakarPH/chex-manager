<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Config;
use App\Models\Schedule;
use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StoreDailyChecklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:store-daily-checklist';

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
        info("Command: Store Daily Checklist");
        $configs = Config::where('is_active', 1)->get();
        $data = [];
        $daily_checklist = '';
        foreach ($configs as $config) {
            $exceptional_days = $config->exceptional_days;
            if (in_array(date('l'), $exceptional_days)) {
                continue;
            }

            $date = Carbon::createFromFormat('Y-m-d', $config->repeat_start_dd);
            if ($config->repeat == 'daily') {
                $add_day = $date->addDays($config->repeat_count);
            } else if ($config->repeat == 'weekly') {
                $day_name = Carbon::parse($config->repeat_start_dd)->dayName;
                $add_day = $date->addDays($config->repeat_count * 7);
            } elseif ($config->repeat == 'monthly') {
                $add_day = $date->addMonths($config->repeat_count);
            } elseif ($config->repeat == 'yearly') {
                $add_day = $date->addYear($config->repeat_count);
            }
            $config_count = Schedule::where('config_id', $config->id)->count();
            if ($add_day->format('Y-m-d') >= date('Y-m-d')) {
                if ($config->repeat == 'weekly' && $day_name == date('l') && $config->repeat_count > $config_count) {

                    $daily_checklist = Schedule::where('config_id', $config->id)
                        ->whereDate('created_at', date('Y-m-d'))->first();
                    if (is_null($daily_checklist)) {
                        $start_date = Carbon::createFromFormat('Y-m-d', $config->repeat_start_dd);
                        $due_date = null;
                        $due_date = $config->repeat_due_count ? Carbon::now()->addDays($config->repeat_due_count) : Carbon::now()->copy()->addDays(6);

                        $item['config_id'] = $config->id;
                        $item['company_id']     = $config->company_id;
                        $item['task_id']   = $config->task_id;
                        $item['created_at']     = date('Y-m-d H:i:s');
                        $item['status']     = "in_progress";
                        $item['due_date']     = $due_date;
                        array_push($data, $item);
                    }
                } else if ($config->repeat != 'weekly' && $config->repeat != 'daily'  && $add_day->format('d') == date('d')) {
                    $daily_checklist = Schedule::where('config_id', $config->id)
                        ->whereDate('created_at', date('Y-m-d'))
                        ->first();
                    if (is_null($daily_checklist)) {
                        $start_date = Carbon::createFromFormat('Y-m-d', $config->repeat_start_dd);
                        $due_date = null;
                        if ($config->repeat == "monthly") {
                            $due_date = Carbon::now()->endOfMonth();
                        }
                        if ($config->repeat == "yearly") {
                            $due_date = Carbon::now()->endOfYear();
                        }
                        if ($config->repeat_due_count) {
                            $due_date = Carbon::now()->addDays($config->repeat_due_count);
                        }

                        $item['config_id'] = $config->id;
                        $item['company_id']     = $config->company_id;
                        $item['task_id']   = $config->task_id;
                        $item['created_at']     = date('Y-m-d H:i:s');
                        $item['status']     = "in_progress";
                        $item['due_date']     = $due_date;
                        array_push($data, $item);
                    }
                } else if ($config->repeat == 'daily') {
                    $period = CarbonPeriod::create($config->repeat_start_dd, $add_day->format('Y-m-d'));
                    $datesInRange = [];
                    foreach ($period as $date) {
                        $datesInRange[] = $date->toDateString();
                    }
                    $daily_checklist = Schedule::where('config_id', $config->id)
                        ->whereDate('created_at', date('Y-m-d'))
                        ->first();
                    $new_date_range = array_pop($datesInRange);
                    if (is_null($daily_checklist) && in_array(date('Y-m-d'), $datesInRange)) {
                        $item['config_id'] = $config->id;
                        $item['company_id']     = $config->company_id;
                        $item['task_id']   = $config->task_id;
                        $item['created_at']     = date('Y-m-d H:i:s');
                        $item['status']     = "in_progress";
                        $item['due_date']     = Carbon::now();
                        array_push($data, $item);
                    }
                }
            }
        }

        Schedule::insert($data);
        $this->info('Command Executed');
        return 0;
    }
}

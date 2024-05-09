<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CheckIn;
use Illuminate\Console\Command;

class AutoCheckoutUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-checkout-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto checkout users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info("Command:Auto Checkout");
        $today = Carbon::today();
        // Get users who have checked in more than 8 hours ago and are still checked in
        $usersToCheckout  = User::whereHas('checkins', function ($q) use($today){
            $q->whereDate('created_at', $today);
        })->with(['checkins' => function ($q) use($today){
            $q->whereDate('created_at', $today);
        }])->get();

        foreach ($usersToCheckout as $user) {
            $firstCheckinTime = $user->checkins()->whereDate('created_at', $today)->whereType('in')->pluck('created_at')->first();
            $checkOutTime = $firstCheckinTime->addHours(9);
            $latestAttendance = $user->checkins()->whereDate('created_at', $today)->latest()->get();
            $lastAttendance = null;
            if ($latestAttendance->isNotEmpty()) {
                $lastAttendance = $latestAttendance[0];
            }
            if($lastAttendance->type === 'in'){
                $attendanceArr['user_id'] = $user->id; 
                $attendanceArr['type'] = 'out'; 
                $checkin = new CheckIn($attendanceArr);
                $checkin['created_at'] = $checkOutTime; 
                $checkin->save();
            }
        }

    }
}

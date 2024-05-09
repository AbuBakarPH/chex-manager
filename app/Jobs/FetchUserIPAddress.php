<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchUserIPAddress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user ;
    public $userAgent; 
    
    public function __construct($user, $userAgent)
    {
        $this->user = $user;
        $this->userAgent = $userAgent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ip_address = Http::get('https://httpbin.org/ip')->json('origin');
        $user = $this->user;
        $login_history = [
            'ip_address' => $ip_address,
            'user_agent' => $this->userAgent
        ];
        $user->login_history()->create($login_history);
    }
}

<?php

namespace App\Console\Commands;

use App\Mail\LoginAlertEMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class LastLoginCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'last:login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sixtyDaysAgo = now()->subDays(60);
        $users=User::where('role_id',3)
        ->whereHas('verification', function($q) {
           return $q;
        })
        ->where('last_login_at','<=',$sixtyDaysAgo)
        ->whereNUll('email_send_at')
        ->where('status',1)
        ->limit(5)
        ->get();
       
        foreach($users as $user){
                $user->update([
                    'email_send_at'=>now(),
                ]);
                Mail::to($user->email)->send(new LoginAlertEMail($user)); 
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Mail\ResendEmail as Resend;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class resendEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:resend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will resend the email who was faile to complete the registration';

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
        $users=  User::where('role_id',3)
                     ->whereNull('verification_deleted_at')
                     ->where('resend',0)
                     ->whereDoesntHave('verification')
                     ->limit(3)
                     ->get();
        foreach($users as $user){
            Mail::to($user->email)->send(new Resend());
            $user->resend = 1;
            $user->save();
        }

    }
}

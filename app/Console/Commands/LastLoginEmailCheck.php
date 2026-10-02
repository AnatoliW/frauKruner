<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class LastLoginEmailCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:check';

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
        $towDaysAgo = Carbon::now()->subDays(2);
        $users=User::whereNotNull('email_send_at')->where('email_send_at','<',$towDaysAgo)->limit(5)->get();
        foreach($users as $user){
            $user->update([
                'status'=>0,
                'email_send_at'=>null,
            ]);
        }
    }
}

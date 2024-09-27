<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClearInactiveUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:inactive-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Used to clear inactive users';

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
     * @return mixed
     */
    public function handle()
    {
        try{

            Log::info('Clearing inactive user for date '.now()->format('d-m-Y'));
            
            $users = User::query();

            $users->where('token_expire_at','!=','')
                    ->where('email_verified_at','=',NULL)
                    ->whereDate('token_expire_at','=',now())
                    ->each(function($user){
                        $user->delete();
                    });

            Log::info('Cleared inactive user for date '.now()->format('d-m-Y'));

        }catch(\Exception $e){
            Log::info('Failed to clear inactive user for date '.now()->format('d-m-Y'). 'reason : '.$e->getMessage());
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateUserAccountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:user {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add user';

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
        $email = $this->argument('email');
        $userName = explode('@', $email)[0];
        $password = $this->argument('password');

        \App\Models\User::create([
            'name' => $userName,
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password)
        ]);
        return 0;
    }
}

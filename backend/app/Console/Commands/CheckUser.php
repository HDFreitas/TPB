<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Plataforma\Models\User;
use Illuminate\Support\Facades\Hash;

class CheckUser extends Command
{
    protected $signature = 'user:check {email}';
    protected $description = 'Check user details';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found");
            return;
        }
        
        $this->info("User: {$user->email}");
        $this->info("Password hash: {$user->password}");
        $this->info("Check password 'password': " . (Hash::check('password', $user->password) ? 'YES' : 'NO'));
        $this->info("Check password 'admin123': " . (Hash::check('admin123', $user->password) ? 'YES' : 'NO'));
    }
}

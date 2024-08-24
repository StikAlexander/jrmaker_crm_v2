<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:list-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users with their assigned roles';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::with('roles')->get();

        $this->info('Listing all users with their assigned roles:');

        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->implode(', ');
            $this->info("User: {$user->name} ({$user->email}) - Roles: {$roles}");
        }

        return Command::SUCCESS;
    }
}

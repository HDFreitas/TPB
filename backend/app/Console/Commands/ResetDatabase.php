<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset {--force : Force the operation to run in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database with fresh migrations and seeders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('Cannot run in production without --force flag!');
            return 1;
        }

        $this->info('🗑️  Dropping all tables...');
        Artisan::call('db:wipe', ['--force' => true]);

        $this->info('🔄 Running migrations...');
        Artisan::call('migrate', ['--force' => true]);

        $this->info('🌱 Running seeders...');
        Artisan::call('db:seed', ['--force' => true]);

        // Remove o arquivo de controle para permitir seeders no próximo boot
        $seedFile = storage_path('.seeded');
        if (file_exists($seedFile)) {
            unlink($seedFile);
        }

        $this->info('✅ Database reset completed successfully!');
        
        return 0;
    }
}

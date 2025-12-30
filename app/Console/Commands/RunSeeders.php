<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunSeeders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-seeders {--force : Force run seeders even if already executed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run database seeders safely (only once unless forced)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Checking if seeders have been executed...');

        // Check if users table has data (indicating seeders were run)
        $userCount = \DB::table('users')->count();

        if ($userCount > 0 && !$this->option('force')) {
            $this->warn("⚠️  Seeders appear to have been executed already ({$userCount} users found).");
            $this->warn('Use --force flag to run anyway.');
            return;
        }

        $this->info('🔄 Running database seeders...');

        try {
            Artisan::call('db:seed', [], $this->getOutput());
            $this->info('✅ Seeders executed successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error running seeders: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

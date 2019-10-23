<?php namespace App\Console\Commands\PlexMediaManager;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class PMMFlush
 * @package App\Console\Commands\PlexMediaManager
 */
class PMMFlush extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pmm:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush anything that relates the Plex Media Manager';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command
     * @return void
     */
    public function handle() : void {
        $response = $this->confirm('Are you sure you want to clear all the tables which have any relation with Plex Media Manager?');
        if ($response) {
            $this->info('Flushing `creators` table...');
            DB::table('creators')->truncate();
            $this->info('Flushing `crew_members` table...');
            DB::table('crew_members')->truncate();
            $this->info('Flushing `genres` table...');
            DB::table('genres')->truncate();
            $this->info('Flushing `guest_stars` table...');
            DB::table('guest_stars')->truncate();
            $this->info('Flushing `networks` table...');
            DB::table('networks')->truncate();
            $this->info('Flushing `production_companies` table...');
            DB::table('production_companies')->truncate();
            $this->info('Flushing `production_countries` table...');
            DB::table('production_countries')->truncate();
            $this->info('Successfully truncated 4 tables!');
        }
    }


}

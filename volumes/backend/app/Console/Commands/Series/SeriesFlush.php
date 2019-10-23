<?php namespace App\Console\Commands\Series;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class SeriesFlush
 * @package App\Console\Commands\Series
 */
class SeriesFlush extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'series:flush';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush anything related to the series';

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
        $response = $this->confirm('Are you sure you want to clear all the tables which have any relation with Series?');
        if ($response) {
            $this->info('Flushing `episodes` table...');
            DB::table('episodes')->truncate();
            $this->info('Flushing `indexers_torrent_links` table...');
            DB::table('indexers_torrent_links')->truncate();
            $this->info('Flushing `seasons` table...');
            DB::table('seasons')->truncate();
            $this->info('Flushing `series` table...');
            DB::table('series')->truncate();
            $this->info('Flushing `series_translations` table...');
            DB::table('series_translations')->truncate();
            $this->info('Flushing `series_indexers` table...');
            DB::table('series_indexers')->truncate();
            $this->info('Flushing `series_indexers_excludes` table...');
            DB::table('series_indexers_excludes')->truncate();
            $this->info('Successfully truncated 4 tables!');
        }
    }


}

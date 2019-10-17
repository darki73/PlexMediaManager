<?php namespace App\Console\Commands\Indexers;

use App\Models\Series;
use Illuminate\Console\Command;
use \Illuminate\Database\Eloquent\Collection;

/**
 * Class IndexersSeriesCLI
 * @package App\Console\Commands\Indexers
 */
class IndexersSeriesCLI extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indexers:series-cli';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update indexers information for series';

    /**
     * Indicates whether application is ready to handle commands
     * @var bool
     */
    protected bool $ready = true;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        try {
            $series = Series::all();
        } catch (\Exception $exception) {
            $this->ready = false;
        }
    }

    /**
     * Execute the console command.
     */
    public function handle() {
        if (! $this->ready) {
            return;
        }

        $implementations = config('jackett.indexers');
        foreach ($implementations as $tracker => $class) {
            $this->info('Refreshing indexes for: ' . $tracker . ' ...');
            $class::index(Series::all());
        }
    }

}

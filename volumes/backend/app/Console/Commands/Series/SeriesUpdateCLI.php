<?php namespace App\Console\Commands\Series;

use App\Classes\Media\Processor\Processor;
use App\Classes\TheMovieDB\Endpoint\Search;
use App\Classes\TheMovieDB\TheMovieDB;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Classes\Media\Source\Source;
use App\Classes\Media\Source\Type\Series;
use Illuminate\Support\Arr;

/**
 * Class SeriesUpdateCLI
 * @package App\Console\Commands\Series
 */
class SeriesUpdateCLI extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'series:update-cli';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update information about available series without dispatching a job';

    /**
     * Initialize series storage
     * @var Series|null
     */
    private $storage = null;

    /**
     * Series list
     * @var array
     */
    private $list = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->storage = Source::series();
        $this->list = $this->storage->list();
    }

    /**
     * Execute the console command
     * @return void
     */
    public function handle() : void {
        $progressBar = $this->output->createProgressBar(\count($this->list));
        $progressBar->setFormat('[%current%/%max%] Processed Series: `%message%`.');
        $progressBar->start();
        $progressBar->setMessage(Arr::first($this->list)['name']);
        foreach ($this->list as $index => $series) {
            $this->createOrUpdateSeries($series);
            $progressBar->setMessage($series['name']);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->info('');
    }

    /**
     * Create or update series
     * @param array $series
     * @return void
     */
    protected function createOrUpdateSeries(array $series) : void {
        if (!Processor::exists(Processor::SERIES, $series['original_name'])) {
            $database = new TheMovieDB;
            $search = $database->search()->for(Search::SEARCH_SERIES, $series['name'])->year($series['year']);
            $results = $search->fetch();
            $item = $database->series()->fetchPrimaryInformation($results['id'], $series['original_name']);
            $parser = new \App\Classes\TheMovieDB\Processor\Series($item->primaryInformation());
            Processor::series($parser);
        } else {
            $this->info('');
            $now = Carbon::now();
            $element = \App\Models\Series::where('local_title', '=', $series['original_name'])->first();
            $canUpdateAgain = $element->updated_at->addDays(1)->setTime(0, 0, 0);
            $secondsDifference = $canUpdateAgain->diffInSeconds($now);
            $time = gmdate('H:i:s', $secondsDifference);
            $this->info(sprintf('`%s` has been updated recently, no actions needed. Next update in: %s', $series['name'], $time));
        }
    }

}

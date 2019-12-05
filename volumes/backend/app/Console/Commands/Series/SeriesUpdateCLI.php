<?php namespace App\Console\Commands\Series;

use App\Classes\Media\Processor\Processor;
use App\Classes\TheMovieDB\Endpoint\Search;
use App\Classes\TheMovieDB\TheMovieDB;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Classes\Media\Source\Source;
use App\Classes\Media\Source\Type\Series;
use Illuminate\Support\Arr;
use App\Models\Series as SeriesModel;

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
    protected $signature = 'series:update-cli {--force : Force the operation to update all information}';

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
    private ?Series $storage = null;

    /**
     * Series list
     * @var array
     */
    private array $list = [];

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
            $this->storage = Source::series();
            $this->list = $this->storage->list();
        } catch (\Exception $exception) {
            $this->ready = false;
        }
    }

    /**
     * Execute the console command
     * @return void
     */
    public function handle() : void {
        if (! $this->ready) {
            return;
        }

        $forceUpdate = $this->option('force');

        $progressBar = $this->output->createProgressBar(\count($this->list));
        $progressBar->setFormat('[%current%/%max%] Processed Series: `%message%`.');
        $progressBar->start();
        $progressBar->setMessage(Arr::first($this->list)['name']);
        foreach ($this->list as $index => $series) {
            $this->createOrUpdateSeries($series, $forceUpdate);
            $progressBar->setMessage($series['name']);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->info('');
    }

    /**
     * Create or update series
     * @param array $series
     * @param bool $forceUpdate
     * @return void
     */
    protected function createOrUpdateSeries(array $series, bool $forceUpdate = false) : void {
        if (! \App\Classes\Media\Processor\Processor::exists(\App\Classes\Media\Processor\Processor::SERIES, $series['original_name'])) {
            $databaseModel = \App\Models\Series::query()->where('title', '=', $series['name']);

            if ($series['year'] !== null) {
                $databaseModel = $databaseModel->where('release_date', 'LIKE', $series['year'] . '-%');
            }

            $databaseModel = $databaseModel->first();

            if (! $databaseModel) {
                $database = new TheMovieDB();
                $response = $database->search()->for(Search::SEARCH_SERIES, $series['name'])->year($series['year'])->fetch();
                if (\count($response) !== 0) {
                    [$year, $month, $day] = explode('-', $response['first_air_date']);
                    $seriesModel = SeriesModel::where('title', '=', $response['name'])->where('release_date', 'LIKE', $year . '-%')->first();
                    if ($seriesModel !== null) {
                        $seriesModel->update([
                            'local_title'       =>  $series['original_name']
                        ]);
                    }
                }
            } else {
                if ($databaseModel->local_title !== null) {
                    // php artisan series:update-cli
                    $data = (new \App\Classes\TheMovieDB\TheMovieDB)->series()->fetch($databaseModel->id);
                    $parser = new \App\Classes\TheMovieDB\Processor\Series($data);
                    \App\Classes\Media\Processor\Processor::series($parser);
                    $seasonsInformation = (new \App\Classes\TheMovieDB\TheMovieDB)->series()->seasons($data['id'], $data['seasons']);

                    foreach ($data['seasons'] as $season) {
                        $seasonId = $season['id'];
                        if (isset($season['season_number'])) {
                            $seasonNumber = $season['season_number'];
                            if ($seasonNumber > 0) {
                                try {
                                    if ($seasonNumber !== null) {
                                        foreach($seasonsInformation->seasonEpisodes($seasonNumber) as $episode) {
                                            \App\Classes\Media\Processor\Processor::episode(new \App\Classes\TheMovieDB\Processor\Episode($episode, $seasonId));
                                        }
                                    }
                                } catch (\Exception $exception) {
                                    app('log')->info('Encountered error processing ' . $databaseModel->id . ', Season ' . $seasonNumber . ' . Json Object: ' . json_encode($seasonsInformation->getAllSeasons()));
                                    die();
                                }
                            }
                        }
                    }
                } else {
                    $databaseModel->update([
                        'local_title'       =>  $series['original_name']
                    ]);
                }
            }
        } else {
            $this->info('');
            $now = Carbon::now();
            $element = SeriesModel::where('local_title', '=', $series['original_name'])->first();
            $canUpdateAgain = $element->updated_at->addDays(1)->setTime(0, 0, 0);
            $secondsDifference = $canUpdateAgain->diffInSeconds($now);
            $time = gmdate('H:i:s', $secondsDifference);
            $this->info(sprintf('`%s` has been updated recently, no actions needed. Next update in: %s', $series['name'], $time));
        }
    }

}

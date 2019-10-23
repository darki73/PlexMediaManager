<?php namespace App\Console\Commands\Series;

use App\Models\Series;
use Illuminate\Console\Command;
use App\Classes\TheMovieDB\TheMovieDB;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SeriesFetchAll
 * @package App\Console\Commands\Series
 */
class SeriesFetchAll extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'series:fetch-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update information about available series without dispatching a job';

    /**
     * List of series in the database
     * @var Collection|null
     */
    private ?Collection $seriesCollection = null;

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
            $this->seriesCollection = Series::all();
        } catch (\Exception $exception) {
            $this->ready = false;
        }
    }

    /**
     * Execute the console command
     * @return void
     */
    public function handle() : void {
        if (!$this->ready) {
            return;
        }

        $seriesIDs = [];
        foreach ($this->seriesCollection as $series) {
            $seriesIDs[] = $series->id;
        }

        $rawFile = file((new TheMovieDB)->downloader()->series()->getLocalPath(), FILE_IGNORE_NEW_LINES);
        $seriesList = [];

        foreach ($rawFile as $row) {
            $array = json_decode($row, true);
            if ($array['popularity'] > env('DUMPER_POPULARITY_THRESHOLD_SERIES')) {
                if (! in_array($array['id'], $seriesIDs)) {
                    $seriesList[] = $array['id'];
                }
            }
        }

        $progressBar = $this->output->createProgressBar(\count($seriesList));
        if (\count($seriesList) !== 0) {
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        }
        $progressBar->start();
        foreach ($seriesList as $seriesID) {
            if (
                $seriesID === 13220
                || $seriesID === 14976
                || $seriesID === 49008
                || $seriesID === 45234
                || $seriesID === 2048
                || $seriesID === 2841
            ) {
                continue;
            }

            try {
                $data = (new \App\Classes\TheMovieDB\TheMovieDB)->series()->fetch($seriesID);
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
                                app('log')->info('Encountered error processing ' . $seriesID . ', Season ' . $seasonNumber . ' . Json Object: ' . json_encode($seasonsInformation->getAllSeasons()));
                                die();
                            }
                        }
                    }
                }
            } catch (\Exception $exception) {
                app('log')->info('Shit hit the fan for series with id: ' . $seriesID);
                die();
            }
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
        if ($series['local_title'] === null) {
            return;
        }

        if (!Processor::exists(Processor::SERIES, $series['original_name']) || $forceUpdate) {
            $database = new TheMovieDB;
            $search = $database->search()->for(Search::SEARCH_SERIES, $series['name'])->year($series['year']);
            $results = $search->fetch();
            $item = $database->series()->fetch($results['id'], $series['original_name'], $forceUpdate);
            $parser = new \App\Classes\TheMovieDB\Processor\Series($item);
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

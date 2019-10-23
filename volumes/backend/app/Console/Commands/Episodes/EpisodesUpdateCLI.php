<?php namespace App\Console\Commands\Episodes;

use App\Classes\Media\Processor\Processor;
use App\Classes\TheMovieDB\Processor\Episode;
use App\Classes\TheMovieDB\TheMovieDB;
use App\Models\Season;
use App\Models\Series;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class EpisodesUpdateCLI
 * @package App\Console\Commands\Episodes
 */
class EpisodesUpdateCLI extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'episodes:update-cli';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update list of episodes for all series';

    /**
     * List of all available series
     * @var Series[]|Collection|null
     */
    protected ?Collection $series = null;

    /**
     * Indicates whether application is ready to handle commands
     * @var bool
     */
    protected bool $ready = true;

    /**
     * EpisodesUpdateCLI constructor.
     */
    public function __construct() {
        parent::__construct();
        try {
            $this->series = Series::where('local_title', '!=', null)->get();
        } catch (\Exception $exception) {
            $this->ready = false;
        }
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() : void {
        ini_set('memory_limit', '1024M');
        if (! $this->ready) {
            return;
        }
        $missingEpisodesCount = $this->countMissingEpisodes();
        $progressBar = $this->output->createProgressBar($missingEpisodesCount);
        if ($missingEpisodesCount > 0) {
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        }
        foreach ($this->series as $series) {
            $episodesTotalCount = $series->episodesTotal();
            $episodesLocalCount = $series->episodesCount();
            if ($episodesLocalCount < $episodesTotalCount) {
                $this->info('Missing ' . ($episodesTotalCount - $episodesLocalCount) . ' episodes for ' . $series->title);
                foreach ($series->seasons as $season) {
                    if (! $this->seasonIsFull($season)) {
                        $database = new TheMovieDB;
                        $search = $database->series()->season($season->series_id, $season->season_number);
                        $episodes = $search->episodes();
                        foreach ($episodes as $episode) {
                            if ($episode['season_number'] !== null) {
                                Processor::episode(new Episode($episode, $season->id));
                            }
                            $progressBar->advance();
                        }
                    }
                }
            }
        }
        $progressBar->finish();
        $this->info('');
    }

    /**
     * Retrieve number of missing episodes
     * @return int
     */
    protected function countMissingEpisodes() : int {
        $episodesCount = 0;
        foreach ($this->series as $series) {
            $expected = $series->episodesTotal();
            $actual = $series->episodesCount();
            if ($expected !== $actual) {
                $episodesCount += $expected - $actual;
            }
        }
        return $episodesCount;
    }

    /**
     * Check whether or not we have a full season details
     * @param Season $season
     * @return bool
     */
    protected function seasonIsFull(Season $season) : bool {
        $expected = $season->episodes_count;
        $actual = $season->episodes->count();
        return $expected === $actual;
    }

}

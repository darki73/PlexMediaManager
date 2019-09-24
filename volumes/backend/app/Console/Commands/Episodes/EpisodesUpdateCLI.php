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
    protected $series = null;

    /**
     * EpisodesUpdateCLI constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->series = Series::all();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() : void {
        $missingEpisodesCount = $this->countMissingEpisodes();
        $progressBar = $this->output->createProgressBar($missingEpisodesCount);
        foreach ($this->series as $series) {
            foreach ($series->seasons as $season) {
                if (! $this->seasonIsFull($season)) {
                    $database = new TheMovieDB;
                    $search = $database->series()->season($season->series_id, $season->season_number);
                    $episodes = $search->episodes();
                    foreach ($episodes as $episode) {
                        Processor::episode(new Episode($episode, $season->id));
                        $progressBar->advance();
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
            foreach ($series->seasons as $season) {
                $expected = $season->episodes_count;
                $actual = $season->episodes->count();
                if ($expected !== $actual) {
                    $episodesCount += $expected - $actual;
                }
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

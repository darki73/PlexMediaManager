<?php namespace App\Console\Commands\Episodes;

use App\Models\Series;
use Illuminate\Console\Command;
use App\Classes\Media\Source\Source;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class EpisodesSyncCLI
 * @package App\Console\Commands\Episodes
 */
class EpisodesSyncCLI extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'episodes:sync-cli';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Local Episodes With Database';

    /**
     * Initialize series storage
     * @var \App\Classes\Media\Source\Type\Series|null
     */
    private $storage = null;

    /**
     * List of locally available series
     * @var array
     */
    protected array $seriesList = [];

    /**
     * List of all available series
     * @var Series[]|Collection|null
     */
    protected ?Collection $seriesCollection = null;

    /**
     * Indicates whether application is ready to handle commands
     * @var bool
     */
    protected bool $ready = true;

    /**
     * Create a new command instance.
     * EpisodesSyncCLI constructor.
     */
    public function __construct() {
        parent::__construct();
        try {
            $this->storage = Source::series();
            $this->seriesList = $this->storage->list();
            $this->seriesCollection = Series::all();
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
        if (! $this->ready) {
            return;
        }
        $countSynced = 0;
        foreach ($this->seriesCollection as $seriesModel) {
            foreach ($this->seriesList as $series) {
                if ($seriesModel->local_title === $series['original_name']) {
                    $episodes = $seriesModel->episodes;
                    foreach ($episodes as $episode) {
                        if (isset($series['seasons'][$episode->season_number])) {
                            if (isset($series['seasons'][$episode->season_number]['episodes'][$episode->episode_number])) {
                                if (!$episode->downloaded) {
                                    $episode->update([
                                        'downloaded'    =>  true
                                    ]);
                                    $countSynced++;
                                }
                            } else {
                                continue;
                            }
                        }
                    }
                }
            }
        }
        $this->info('');
        $this->info('Synced local episodes with database. Added ' . $countSynced . ' new episodes.');
        $this->info(PHP_EOL);
    }

    /**
     * Retrieve number of missing episodes
     * @return int
     */
    protected function countMissingEpisodes() : int {
        $episodesCount = 0;
        foreach ($this->seriesCollection as $series) {
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

}

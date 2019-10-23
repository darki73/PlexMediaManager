<?php namespace App\Jobs\Update;

use App\Models\Season;
use App\Jobs\AbstractLongQueueJob;
use App\Models\Series as SeriesModel;
use App\Classes\TheMovieDB\TheMovieDB;
use App\Classes\Media\Processor\Processor;
use App\Classes\TheMovieDB\Processor\Episode;

/**
 * Class Episodes
 * @package App\Jobs\Update
 */
class Episodes extends AbstractLongQueueJob {

    /**
     * Series collection
     * @var SeriesModel[]|\Illuminate\Database\Eloquent\Collection|null
     */
    protected $seriesCollection = null;

    /**
     * Episodes constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('update', 'episodes');
        $this->seriesCollection = SeriesModel::where('local_title', '!=', null)->get();
    }

    /**
     * @inheritDoc
     */
    public function handle() : void {
        foreach ($this->seriesCollection as $series) {
            foreach ($series->seasons as $season) {
                if (! $this->seasonIsFull($season)) {
                    $api = new TheMovieDB;
                    $search = $api->series()->season($season->series_id, $season->season_number);
                    $episodes = $search->episodes();
                    foreach ($episodes as $episode) {
                        Processor::episode(
                            new Episode($episode, $season->id)
                        );
                    }
                }
            }
        }
        dispatch(new \App\Jobs\Sync\Episodes);
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

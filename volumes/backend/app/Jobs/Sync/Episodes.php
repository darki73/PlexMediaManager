<?php namespace App\Jobs\Sync;

use App\Models\Series;
use App\Jobs\AbstractLongQueueJob;
use App\Classes\Media\Source\Source;

/**
 * Class Episodes
 * @package App\Jobs\Sync
 */
class Episodes extends AbstractLongQueueJob {

    /**
     * List of locally available series
     * @var array|null
     */
    private $localSeriesList = null;

    /**
     * Series collection
     * @var Series[]|\Illuminate\Database\Eloquent\Collection|null
     */
    private $seriesCollection = null;

    /**
     * Episodes constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('sync', 'episodes');
        $this->localSeriesList = Source::series()->list();
        $this->seriesCollection = Series::where('local_title', '!=', null)->get();
    }

    /**
     * @inheritDoc
     */
    public function handle() : void {
        foreach ($this->seriesCollection as $seriesModel) {
            foreach ($this->localSeriesList as $series) {
                if ($seriesModel->local_title === $series['original_name']) {
                    $episodes = $seriesModel->episodes;
                    foreach ($episodes as $episode) {
                        if (isset($series['seasons'][$episode->season_number])) {
                            if (isset($series['seasons'][$episode->season_number]['episodes'][$episode->episode_number])) {
                                if (!$episode->downloaded) {
                                    $episode->update([
                                        'downloaded'    =>  true
                                    ]);
                                }
                            } else {
                                continue;
                            }
                        }
                    }
                }
            }
        }
    }

}

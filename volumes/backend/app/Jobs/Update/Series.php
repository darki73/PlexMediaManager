<?php namespace App\Jobs\Update;

use App\Jobs\AbstractLongQueueJob;
use App\Jobs\Download\SeriesImages;
use App\Classes\Media\Source\Source;
use Illuminate\Support\Facades\Cache;
use App\Models\Series as SeriesModel;
use App\Classes\TheMovieDB\TheMovieDB;
use App\Classes\Media\Processor\Processor;
use App\Classes\TheMovieDB\Endpoint\Search;

/**
 * Class Series
 * @package App\Jobs\Update
 */
class Series extends AbstractLongQueueJob {

    /**
     * List of local series
     * @var array|null
     */
    private $localSeriesList = null;

    /**
     * Series constructor.
     */
    public function __construct() {
        $this->setAttempts(25);
        $this->setTags('update', 'series');
        $this->localSeriesList = Source::series()->list();
    }

    /**
     * @inheritDoc
     */
    public function handle(): void {
        $oldSeriesCount = SeriesModel::count();
        foreach ($this->localSeriesList as $item) {
            if (!Processor::exists(Processor::SERIES, $item['original_name'])) {
                $database = new TheMovieDB;
                $search = $database->search()->for(Search::SEARCH_SERIES, $item['name'])->year($item['year']);
                $searchResult = $search->fetch();
                $series = $database->series()->fetchPrimaryInformation($searchResult['id'], $item['original_name']);
                Processor::series(new \App\Classes\TheMovieDB\Processor\Series($series->primaryInformation()));
            }
        }
        $newSeriesCount = SeriesModel::count();
        if($newSeriesCount > $oldSeriesCount) {
            Cache::forget('series:list');
            dispatch(new SeriesIndexers);
        }
        Episodes::withChain([
            new SeriesImages
        ])->dispatch();
    }

}

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
//        $oldSeriesCount = SeriesModel::count();
        $updatesMade = 0;
        foreach ($this->localSeriesList as $item) {
            if (!Processor::exists(Processor::SERIES, $item['original_name'])) {
                $databaseModel = \App\Models\Series::query()->where('title', '=', $item['name']);

                if ($item['year'] !== null) {
                    $databaseModel = $databaseModel->where('release_date', 'LIKE', $item['year'] . '-%');
                }
                $databaseModel = $databaseModel->first();

                if (! $databaseModel) {
                    $database = new TheMovieDB();
                    $response = $database->search()->for(Search::SEARCH_SERIES, $item['name'])->year($item['year'])->fetch();
                    if (\count($response) !== 0) {
                        [$year, $month, $day] = explode('-', $response['first_air_date']);
                        $seriesModel = \App\Models\Series::where('title', '=', $response['name'])->where('release_date', 'LIKE', $year . '-%')->first();
                        if ($seriesModel !== null) {
                            $updatesMade++;
                            $seriesModel->update([
                                'local_title'       =>  $item['original_name']
                            ]);
                        }
                    } else {
                        app('log')->info('[SeriesUpdate::handle] We need to query API, Series `' . $item['name'] . '` was not found in the database');
                    }
                } else {
                    $updatesMade++;
                    $databaseModel->update([
                        'local_title'       =>  $item['original_name']
                    ]);
                }
            }
        }
//        $newSeriesCount = SeriesModel::count();
        if($updatesMade > 0) {
            Cache::forget('series:list');
            SeriesIndexers::withChain([
                new Episodes
            ])->dispatch();
        }
    }

}

<?php namespace App\Http\Controllers\Api\Dashboard;

use App\Models\SeriesIndexerExclude;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\SeriesIndexer;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Models\SeriesIndexerTorrentLink;
use App\Http\Controllers\Api\APIController;
use App\Http\Controllers\Api\SeriesController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * Class IndexersController
 * @package App\Http\Controllers\Api\Dashboard
 */
class IndexersController extends APIController {

    /**
     * Get list of all indexers
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse {
        $forceRefresh = $request->get('refresh') !== null;
        if ($forceRefresh !== false) {
            Cache::forget('indexers:series');
        }
        $indexersCollection = Cache::remember('indexers:series', now()->addHours(6), function() {
            return SeriesIndexer::all();
        });

        $groupedIndexers = [];

        foreach ($indexersCollection as $indexer) {
            if (!array_key_exists($indexer->indexer, $groupedIndexers)) {
                $groupedIndexers[$indexer->indexer] = [];
            }
            $groupedIndexers[$indexer->indexer][] = $indexer;
        }

        $indexers = [];

        foreach ($groupedIndexers as $indexer => $items) {
            $indexers[$indexer] = [
                'name'          =>  $indexer,
                'class'         =>  config('jackett.indexers.' . $indexer, null),
                'items_count'   =>  \count($items),
                'items'         =>  []
            ];

            try {
                $seriesIdToNameRelation = Arr::pluck((new SeriesController)->cacheAllSeries(), 'title', 'id');
                $this->processItems($indexers, $indexer, $items, $seriesIdToNameRelation);
            } catch (\Exception $exception) {
                $seriesIdToNameRelation = Arr::pluck((new SeriesController)->cacheAllSeries(true), 'title', 'id');
                $this->processItems($indexers, $indexer, $items, $seriesIdToNameRelation);
            }
        }

        return $this->sendResponse('Successfully fetched list of indexers', array_values($indexers));
    }

    /**
     * Update exclusion list for the indexer and particular series
     * @param Request $request
     * @return JsonResponse
     */
    public function updateExclusionList(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'id'        =>  'required|integer',
            'excludes'  =>  'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Invalid parameters were passed', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $seriesID = $request->get('id');

        try {
            foreach ($request->get('excludes') as $season => $value) {
                $item = SeriesIndexerExclude::where('series_id', '=', $seriesID)->where('season_number', '=', $season)->first();
                if ($value) {
                    if ($item === null) {
                        SeriesIndexerExclude::create([
                            'series_id'     =>  $seriesID,
                            'season_number' =>  $season
                        ]);
                    }
                } else {
                    if ($item !== null) {
                        $item->delete();
                    }
                }
            }
        } catch (\Exception $exception) {
            return $this->sendError('Unable to update exclusion list for series', [
                'code'      =>  $exception->getCode(),
                'message'   =>  $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse('Successfully updated exclusion list for series', $request->toArray());
    }

    public function updateTorrentsList(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'id'        =>  'required|integer',
            'torrents'  =>  'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Invalid parameters were passed', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $seriesID = $request->get('id');
        $torrents = $request->get('torrents');

        try {
            foreach ($torrents as $torrent) {
                $season = $torrent['season'];
                $torrentFile = $torrent['torrent_file'];
                $model = SeriesIndexerTorrentLink::where('series_id', '=', $seriesID)->where('season', '=', $season)->first();
                if ($model === null) {
                    SeriesIndexerTorrentLink::create([
                        'series_id'     =>  $seriesID,
                        'season'        =>  $season,
                        'torrent_file'  =>  $torrentFile
                    ]);
                } else if ($model->season === $season && $model->torrent_file !== $torrentFile) {
                    SeriesIndexerTorrentLink::where('series_id', '=', $seriesID)->where('season', '=', $season)->update([
                        'torrent_file'  =>  $torrentFile
                    ]);
                }
            }
        } catch (\Exception $exception) {
            return $this->sendError('Unable to update torrents list for series', [
                'code'      =>  $exception->getCode(),
                'message'   =>  $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse('Successfully updated torrents list for the series');
    }

    /**
     * Extract torrent files
     * @param Collection $torrentFiles
     * @return array
     */
    protected function extractTorrentFiles(Collection $torrentFiles) : array {
        $files = [];
        /**
         * @var SeriesIndexerTorrentLink $file
         */
        foreach ($torrentFiles as $file) {
            $files[$file->season] = Arr::except($file->toArray(), ['series_id']);
        }
        return $files;
    }

    /**
     * Process items
     * @param array $indexers
     * @param string $indexer
     * @param array $items
     * @param array $seriesIdToNameRelation
     * @return void
     */
    protected function processItems(array & $indexers, string $indexer, array $items, array $seriesIdToNameRelation) : void {
        foreach ($items as $item) {
            $hasTorrent = $item->torrentFiles->count() !== 0;
            $indexers[$indexer]['items'][] = array_merge([
                'id'                =>  $item->series_id,
                'title'             =>  $seriesIdToNameRelation[$item->series_id],
            ], Arr::except($item->toArray(), ['series_id', 'indexer']), [
                'has_torrent'       =>  $hasTorrent,
                'torrent_files'     =>  !$hasTorrent ? [] : $this->extractTorrentFiles($item->torrentFiles),
                'excludes'          =>  $this->generateExclusionListForSeries($item)
            ]);
        }
    }

    /**
     * Get list of excluded seasons
     * @param SeriesIndexer $item
     * @return array
     */
    protected function generateExclusionListForSeries(SeriesIndexer $item) : array {
        $seasons = $item->series->seasons_count;
        $exclude = $item->excludes;
        $list = [];


        foreach ($exclude as $item) {
            $list[$item->season_number] = true;
        }

        for ($i = 1; $i <= $seasons; $i++) {
            if (! array_key_exists($i, $list)) {
                $list[$i] = false;
            }
        }

//        foreach ($seasons as $season) {
//            if (! array_key_exists($season->season_number, $list)) {
//                $list[$season->season_number] = false;
//            }
//        }

        ksort($list);
        return $list;
    }


}

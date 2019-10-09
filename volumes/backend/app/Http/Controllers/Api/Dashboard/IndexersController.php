<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\SeriesIndexer;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Models\SeriesIndexerTorrentLink;
use App\Http\Controllers\Api\APIController;
use App\Http\Controllers\Api\SeriesController;

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
        $indexersCollection = SeriesIndexer::all();
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

            $seriesIdToNameRelation = Arr::pluck((new SeriesController)->cacheAllSeries(), 'title', 'id');

            foreach ($items as $item) {
                $hasTorrent = $item->torrentFiles->count() !== 0;
                $indexers[$indexer]['items'][] = array_merge([
                    'id'                =>  $item->series_id,
                    'title'             =>  $seriesIdToNameRelation[$item->series_id]
                ], Arr::except($item->toArray(), ['series_id', 'indexer']), [
                    'has_torrent'       =>  $hasTorrent,
                    'torrent_files'     =>  !$hasTorrent ? [] : $this->extractTorrentFiles($item->torrentFiles)
                ]);
            }
        }

        return $this->sendResponse('Successfully fetched list of indexers', array_values($indexers));
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

}

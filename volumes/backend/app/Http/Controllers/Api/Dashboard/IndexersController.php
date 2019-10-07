<?php namespace App\Http\Controllers\Api\Dashboard;

use App\Models\SeriesIndexer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\APIController;

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
        $indexers = [];

        foreach ($indexersCollection as $indexer) {
            if (!array_key_exists($indexer->indexer, $indexers)) {
                $indexers[$indexer->indexer] = [];
            }

            // TODO: There should be more processing done, for now just leave it as is
            $indexers[$indexer->indexer][] = $indexer;
        }
         

        return $this->sendResponse('Successfully fetched list of indexers', $indexers);
    }

}

<?php namespace App\Http\Controllers\Api;

use App\Classes\Plex\Plex;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\Search\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class SearchController
 * @package App\Http\Controllers\Api
 */
class SearchController extends APIController {


    /**
     * Perform search on the remote search providers
     * @param Request $request
     * @return JsonResponse
     */
    public function remoteSearch(Request $request) : JsonResponse {
        $validator = Validator::make($request->all(), [
            'query'     =>  'required|string',
            'type'      =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or invalid search query provided', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $query = $request->get('query');
        $type = $request->get('type', null);

        return $this->sendResponse('Successfully fetched list of results from search endpoints', (new Search)->{$type}($query));
    }

    public function remoteSearchWithPlex(Request $request) : JsonResponse {
        $plexToken = $this->getPlexToken($request);
        if (! $plexToken) {
            return $this->sendError('X-Plex-Token header is either not set or the provided value is incorrect', [], Response::HTTP_BAD_REQUEST);
        }
        $validator = Validator::make($request->toArray(), [
            'server'    =>  'required|string',
            'category'  =>  'required',
            'query'     =>  'required|string',
            'type'      =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing required parameters', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $serverID = $request->get('server');
        $query = $request->get('query');
        $type = $request->get('type', null);
        $category = false !== strpos($request->get('category'), ',') ? array_map(function (string $category) : int {
            return (integer) $category;
        }, explode(',', $request->get('category'))) : (integer) $request->get('category');


        $searchResults = (new Search)->{$type}($query);

        $resultsFromPlex = [];
        if (is_array($category)) {
            foreach ($category as $item) {
                $resultsFromPlex = array_merge($resultsFromPlex, (new Plex)->library()->listContents($serverID, $item, $plexToken)['data']);
            }
        } else {
            $resultsFromPlex = array_merge($resultsFromPlex, (new Plex)->library()->listContents($serverID, $category, $plexToken)['data']);
        }

        foreach ($searchResults as $index => $result) {
            $title = $this->searchResultTitle($result);
            $originalTitle = $this->searchResultOriginalTitle($result);
            $releaseDate = $this->searchResultReleaseDate($result);

            if ($releaseDate === null) {
                continue;
            }

            foreach ($resultsFromPlex as $item) {
                if (
                    $title === $item['title']
                    || $originalTitle === $item['title']
                ) {
                    if ($this->areDatesMatching($releaseDate, $item['released'])) {
                        $searchResults[$index]['watch'] = $item['watch'];
                        $searchResults[$index]['exists'] = true;
                        break;
                    }
                }
            }
        }

        return $this->sendResponse('Successfully fetched list of results from search endpoints', $searchResults);
    }

    /**
     * Get title from the search result item
     * @param array $result
     * @return string
     */
    protected function searchResultTitle(array $result) : string {
        return isset($result['title']) ? $result['title'] : $result['name'];
    }

    /**
     * Get original title from the search result item
     * @param array $result
     * @return string
     */
    protected function searchResultOriginalTitle(array $result) : string {
        return isset($result['original_title']) ? $result['original_title'] : (isset($result['original_name']) ? $result['original_name'] : $result['name']);
    }

    /**
     * Get release date from the search result item
     * @param array $result
     * @return string|null
     */
    protected function searchResultReleaseDate(array $result) : ?string {
        return isset($result['release_date']) ? $result['release_date'] : (isset($result['first_air_date']) ? $result['first_air_date'] : null);
    }

    /**
     * Check if there are multiple items with the same title
     * @param array $results
     * @param string $plexTitle
     * @return array|bool
     */
    protected function hasMultipleItemsWithSameName(array $results, string $plexTitle) {
        $sameTitle = [];
        $sameOriginalTitle = [];
        foreach ($results as $result) {
            $title = $this->searchResultTitle($result);
            $originalTitle = $this->searchResultOriginalTitle($result);
            $releaseDate = $this->searchResultReleaseDate($result);
            if ($plexTitle === $title) {
                $sameTitle[$releaseDate] = $title;
            }
            if ($plexTitle === $originalTitle) {
                $sameOriginalTitle[$releaseDate] = $originalTitle;
            }
        }

        dd($sameTitle, $sameOriginalTitle);

        if (\count($sameTitle) < 2 && \count($sameOriginalTitle) < 2) {
            return false;
        }
        return true;
    }

    /**
     * Check if release dates are somewhat the same
     * @param string $searchDate
     * @param string $releaseDate
     * @return bool
     */
    protected function areDatesMatching(string $searchDate, string $releaseDate) : bool {
        [$year, $month, $day] = explode('-', $releaseDate);
        [$searchYear, $searchMonth, $searchDay] = explode('-', $searchDate);
        if (
            $year === $searchYear
            && $month === $searchMonth
            && $day === $searchDay
        ) {
            return true;
        } else if (
            $year === $searchYear
            && $month === $searchMonth
        ) {
            return true;
        } else if (
            $year === $searchYear
        ) {
            return true;
        }
        return false;
    }

}

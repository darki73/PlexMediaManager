<?php namespace App\Http\Controllers\Api;

use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

/**
 * Class SeriesController
 * @package App\Http\Controllers\Api
 */
class SeriesController extends APIMediaController {

    public function list(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched list of series', $this->getSeriesList()->map(function (Series $series, int $key) {
            return $this->getBaseSeriesInformation($series, [
                'id',
                'title',
                'original_title',
                'local_title',
                'original_language',
                'overview',
                'backdrop',
                'poster',
                'genres',
                'seasons_count',
                'runtime'
            ]);
        })->toArray());
    }

    /**
     * Get list of all series
     * @return Collection
     */
    protected function getSeriesList() : Collection {
        return Series::all();
    }

    /**
     * Get base series information
     * @param Series $series
     * @param array $only
     * @return array
     */
    private function getBaseSeriesInformation(Series $series, array $only = []) : array {
        $data = Arr::except($series->toArray(), [
            'backdrop',
            'poster',
            'genres',
            'production_companies',
            'creators',
            'networks'
        ]);
        $data['backdrop'] = $this->buildImagePath($series->backdrop, 'backdrop', [
            'type'      =>  'series',
            'id'        =>  $series->id,
            'category'  =>  'global'
        ]);
        $data['poster'] = $this->buildImagePath($series->poster, 'poster', [
            'type'      =>  'series',
            'id'        =>  $series->id,
            'category'  =>  'global'
        ]);
        $data['genres'] = $this->loadGenres($series->genres);
        $data['production_companies'] = $this->loadProductionCompanies($series->production_companies);
        $data['creators'] = $this->loadCreators($series->creators);
        $data['networks'] = $this->loadNetworks($series->networks);

        if (\count($only) > 0) {
            $data = Arr::only($data, $only);
        }


        return $data;
    }

}

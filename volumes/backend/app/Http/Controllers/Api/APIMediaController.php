<?php namespace App\Http\Controllers\Api;

use App\Models\Genre;
use App\Models\Network;
use App\Models\Creator;
use Illuminate\Support\Arr;
use App\Models\ProductionCountry;
use App\Models\ProductionCompany;
use App\Classes\TheMovieDB\TheMovieDB;

/**
 * Class APIMediaController
 * @package App\Http\Controllers\Api
 */
class APIMediaController extends APIController {

    /**
     * The Movie Database Configuration Instance
     * @var \App\Classes\TheMovieDB\Endpoint\Configuration|null
     */
    protected $configuration = null;

    /**
     * SeriesController constructor.
     */
    public function __construct() {
        $this->configuration = (new TheMovieDB)->configuration();
    }

    /**
     * Load list of genres
     * @param array|null $genres
     * @return array
     */
    protected function loadGenres(?array $genres) : array {
        if ($genres === null) {
            return [];
        }

        $list = [];

        foreach ($genres as $genre) {
            $list[] = [
                'id'    =>  $genre,
                'name'  =>  Genre::find($genre)->name
            ];
        }
        return $list;
    }

    /**
     * Load list of production companies
     * @param array|null $companies
     * @return array
     */
    protected function loadProductionCompanies(?array $companies) : array {
        if ($companies === null) {
            return [];
        }

        $list = [];

        foreach ($companies as $company) {
            $company = ProductionCompany::find($company);
            if ($company !== null) {
                $tmp = Arr::except($company->toArray(), [
                    'logo',
                    'created_at',
                    'updated_at'
                ]);

                $tmp['logo'] = $company->logo ? $this->buildImagePath($company->logo, 'company') : null;

                $list[] = $tmp;
            }
        }
        return $list;
    }

    /**
     * Load list of production countries
     * @param array|null $countries
     * @return array
     */
    protected function loadProductionCountries(?array $countries) : array {
        if ($countries === null) {
            return [];
        }

        $list = [];

        foreach ($countries as $country) {
            $company = ProductionCountry::find($country);
            if ($company !== null) {
                $tmp = Arr::except($company->toArray(), [
                    'created_at',
                    'updated_at'
                ]);
                $list[] = $tmp;
            }
        }

        return $list;
    }

    /**
     * Load list of creators
     * @param array|null $creators
     * @return array
     */
    protected function loadCreators(?array $creators) : array {
        if ($creators === null) {
            return [];
        }

        $list = [];

        foreach ($creators as $creator) {
            $creator = Creator::find($creator);
            if ($creator !== null) {
                $tmp = Arr::except($creator->toArray(), [
                    'photo',
                    'created_at',
                    'updated_at'
                ]);

                $tmp['photo'] = $creator->photo ? $this->buildImagePath($creator->photo, 'creator') : null;

                $list[] = $tmp;
            }
        }
        return $list;
    }

    /**
     * Load list of networks
     * @param array|null $networks
     * @return array
     */
    protected function loadNetworks(?array $networks) : array {
        if ($networks === null) {
            return [];
        }

        $list = [];

        foreach ($networks as $network) {
            $network = Network::find($network);
            if ($network !== null) {
                $tmp = Arr::except($network->toArray(), [
                    'logo',
                    'created_at',
                    'updated_at'
                ]);

                $tmp['logo'] = $network->logo ? $this->buildImagePath($network->logo, 'network') : null;

                $list[] = $tmp;
            }
        }
        return $list;
    }

    /**
     * Build Image Path
     * @param string $image
     * @param string $type
     * @param array|null $additionalParameters
     * @return array
     */
    protected function buildImagePath(string $image, string $type, ?array $additionalParameters = null) : array {
        $paths = [];
        $this->configuration->fetch();

        switch ($type) {
            case 'network':
                foreach ($this->configuration->getLogoSizes() as $size) {
                    $paths[$size] = 'https://' . sprintf('%s/storage/images/networks/%s/%s', env('APP_URL'), $size, $image);
                }
                break;
            case 'creator':
                foreach ($this->configuration->getProfileSizes() as $size) {
                    $paths[$size] = 'https://' . sprintf('%s/storage/images/creators/%s/%s', env('APP_URL'), $size, $image);
                }
                break;
            case 'company':
                foreach ($this->configuration->getLogoSizes() as $size) {
                    $paths[$size] = 'https://' . sprintf('%s/storage/images/companies/%s/%s', env('APP_URL'), $size, $image);
                }
                break;
            case 'backdrop':
                foreach ($this->configuration->getBackdropSizes() as $size) {
                    switch ($additionalParameters['type']) {
                        case 'movies':
                            $paths[$size] = 'https://' . sprintf('%s/storage/images/movies/%d/%s/%s', env('APP_URL'), $additionalParameters['id'], $size, $image);
                            break;
                        case 'series':
                            $paths[$size] = 'https://' . sprintf('%s/storage/images/series/%d/%s/%s/%s', env('APP_URL'), $additionalParameters['id'], $additionalParameters['category'], $size, $image);
                            break;
                    }
                }
                break;
            case 'poster':
                foreach ($this->configuration->getPosterSizes() as $size) {
                    switch ($additionalParameters['type']) {
                        case 'movies':
                            $paths[$size] = 'https://' . sprintf('%s/storage/images/movies/%d/%s/%s', env('APP_URL'), $additionalParameters['id'], $size, $image);
                            break;
                        case 'series':
                            $paths[$size] = 'https://' . sprintf('%s/storage/images/series/%d/%s/%s/%s', env('APP_URL'), $additionalParameters['id'], $additionalParameters['category'], $size, $image);
                            break;
                    }
                }
                break;
        }

        return $paths;
    }

}

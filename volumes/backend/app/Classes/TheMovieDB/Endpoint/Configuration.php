<?php namespace App\Classes\TheMovieDB\Endpoint;

use Illuminate\Support\Facades\Cache;

/**
 * Class Configuration
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Configuration extends AbstractEndpoint {

    /**
     * Fetch TMDB Configuration
     * @return array
     */
    public function fetch() : array {
        return Cache::remember('tmdb::api:configuration', now()->addHours(6), function() {
            $request = $this->client->get(sprintf('%s/%d/configuration', $this->baseURL, $this->version), [
                'query' =>  $this->options
            ]);
            return json_decode($request->getBody()->getContents(), true);
        });
    }

    /**
     * Get Backdrop Image Sizes
     * @return array
     */
    public function getBackdropSizes() : array {
        return $this->configuration['images']['backdrop_sizes'];
    }

    /**
     * Get Poster Image Sizes
     * @return array
     */
    public function getPosterSizes() : array {
        return $this->configuration['images']['poster_sizes'];
    }

    /**
     * Get Still Image Sizes
     * @return array
     */
    public function getStillSizes() : array {
        return $this->configuration['images']['still_sizes'];
    }

    /**
     * Get Logo Image Sizes
     * @return array
     */
    public function getLogoSizes() : array {
        return $this->configuration['images']['logo_sizes'];
    }

    /**
     * Get Profile Image Sizes
     * @return array
     */
    public function getProfileSizes() : array {
        return $this->configuration['images']['profile_sizes'];
    }

    /**
     * Get full remote image path
     * @param string $image
     * @param string $type
     * @return array
     */
    public function getRemoteImagePath(string $image, string $type) : array {
        $paths = [];

        switch ($type) {
            case 'backdrop':
                foreach ($this->configuration['images']['backdrop_sizes'] as $size) {
                    $paths[$size] = $this->buildRemoteImageUrl($image, $size);
                }
                break;
            case 'poster':
                foreach ($this->configuration['images']['poster_sizes'] as $size) {
                    $paths[$size] = $this->buildRemoteImageUrl($image, $size);
                }
                break;
            case 'still':
                foreach ($this->configuration['images']['still_sizes'] as $size) {
                    $paths[$size] = $this->buildRemoteImageUrl($image, $size);
                }
                break;
            default:
                break;
        }

        return $paths;
    }

    /**
     * Build URL For Remote Image
     * @param string $image
     * @param string $size
     * @return string
     */
    private function buildRemoteImageUrl(string $image, string $size) : string {
        return sprintf('%s%s/%s', $this->configuration['images']['secure_base_url'], $size, $image);
    }

}

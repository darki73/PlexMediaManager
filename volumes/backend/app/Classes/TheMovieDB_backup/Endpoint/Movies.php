<?php namespace App\Classes\TheMovieDB\Endpoint;

/**
 * Class Movies
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Movies extends AbstractEndpoint {

    /**
     * Movie Primary Information
     * @var array
     */
    protected $primaryInformation = [];

    /**
     * Fetch primary movie information
     * @param int $seriesID
     * @param string $localName
     * @return Movies|static|self|$this
     */
    public function fetchPrimaryInformation(int $seriesID, string $localName) : self {
        $request = $this->client->get(sprintf('%s/%d/movie/%d', $this->baseURL, $this->version, $seriesID), [
            'query' =>  $this->options
        ]);
        $this->primaryInformation = json_decode($request->getBody()->getContents(), true);
        $this->primaryInformation['local_name'] = $localName;
        return $this;
    }

    /**
     * Get primary information for movie
     * @return array
     */
    public function primaryInformation() : array {
        return $this->primaryInformation;
    }



}

<?php namespace App\Classes\TheMovieDB\Endpoint;

/**
 * Class Series
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Series extends AbstractEndpoint {

    /**
     * Series Primary Information
     * @var array
     */
    protected $primaryInformation = [];

    /**
     * Series Season Information
     * @var array
     */
    protected $seasonInformation = [];

    /**
     * Fetch Primary Series Information
     * @param int $seriesID
     * @param string $localName
     * @return Series
     */
    public function fetchPrimaryInformation(int $seriesID, string $localName) : self {
        $request = $this->client->get(sprintf('%s/%d/tv/%d', $this->baseURL, $this->version, $seriesID), [
            'query' =>  $this->options
        ]);
        $this->primaryInformation = json_decode($request->getBody()->getContents(), true);
        $this->primaryInformation['local_name'] = $localName;
        return $this;
    }

    /**
     * Get Series Primary Information
     * @return array
     */
    public function primaryInformation() : array {
        return $this->primaryInformation;
    }

    /**
     * Get Season Information
     * @param int $seriesID
     * @param int $seasonID
     * @return Series
     */
    public function season(int $seriesID, int $seasonID) : self {
        $request = $this->client->get(sprintf('%s/%d/tv/%d/season/%d', $this->baseURL, $this->version, $seriesID, $seasonID), [
            'query' =>  $this->options
        ]);
        $this->seasonInformation = json_decode($request->getBody()->getContents(), true);
        return $this;
    }

    /**
     * Get Season Episodes
     * @return array
     */
    public function episodes() : array {
        return $this->seasonInformation['episodes'];
    }

}

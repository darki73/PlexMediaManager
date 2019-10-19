<?php namespace App\Classes\TheMovieDB\Endpoint;

/**
 * Class Networks
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Networks extends AbstractEndpoint {

    /**
     * Network Primary Information
     * @var array
     */
    protected $primaryInformation = [];

    /**
     * Fetch Primary Series Information
     * @param int $networkID
     * @return Networks
     */
    public function fetchPrimaryInformation(int $networkID) : self {
        $request = $this->client->get(sprintf('%s/%d/network/%d', $this->baseURL, $this->version, $networkID), [
            'query' =>  $this->options
        ]);
        $this->primaryInformation = json_decode($request->getBody()->getContents(), true);
        return $this;
    }

}

<?php namespace App\Classes\Jackett\Indexers;

use App\Classes\Jackett\Enums\Quality;
use App\Models\Episode;
use App\Models\Series;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RuTracker
 * @package App\Classes\Jackett\Indexers
 */
class RuTracker extends AbstractIndexer {

    /**
     * @inheritDoc
     * @var string
     */
    protected $tracker = 'rutracker';

    /**
     * @inheritDoc
     * @var int
     */
    protected $type = self::SEARCH_SERIES;

    /**
     * Search for series
     * @param string $query
     * @return AbstractIndexer
     */
    public function series(string $query) : AbstractIndexer {
        $this->type = self::SEARCH_SERIES;
        return $this->search($query);
    }

    /**
     * Search for movie
     * @param string $query
     * @return AbstractIndexer
     */
    public function movie(string $query) : AbstractIndexer {
        $this->type = self::SEARCH_MOVIES;
        return $this->search($query);
    }


    /**
     * @inheritDoc
     * @param Collection $itemsCollection
     * @return void
     */
    public static function index(Collection $itemsCollection): void {
        // TODO: Implement index() method.
    }

    /**
     * @inheritDoc
     * @param Series $series
     * @param Episode $episode
     * @param int $quality
     * @return bool
     */
    public static function download(Series $series, Episode $episode, int $quality = Quality::FHD): bool {
        // This method is not used for the RuTracker
        return false;
    }

    /**
     * @inheritDoc
     * @return void
     */
    protected function runPreChecks(): void {
        // TODO: Implement runPreChecks() method.
    }

    /**
     * @inheritDoc
     * @param array $response
     * @return array|null
     */
    protected function processResponse(array $response): ?array {
        if (! array_key_exists('Results', $response)) {
            return []; // Just return empty array, no results found anyways
        }
        $results = $response['Results'];
        dd($results);
    }


}

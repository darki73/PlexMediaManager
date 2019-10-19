<?php namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

/**
 * Interface ISeriesRepository
 * @package App\Interfaces
 */
interface ISeriesRepository {

    /**
     * Perform search on the `series` table
     * @param string $query
     * @return Collection
     */
    public function search(string $query = '') : Collection;

}

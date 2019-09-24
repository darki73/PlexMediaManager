<?php namespace App\Classes\LogReader\Contracts;

/**
 * Interface Levelable
 * @package App\Classes\LogReader\Contracts
 */
interface Levelable {

    /**
     * Filter logs by level
     * @param string $level
     * @param array $allowed
     * @return bool
     */
    public function filter(string $level, array $allowed) : bool;

}

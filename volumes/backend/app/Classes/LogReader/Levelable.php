<?php namespace App\Clasess\LogReader;

use App\Classes\LogReader\Contracts\Levelable as LevelableInterface;

/**
 * Class Levelable
 * @package App\Clasess\LogReader
 */
class Levelable implements LevelableInterface {

    /**
     * The log accepted levels
     * @var array
     */
    protected $levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug'
    ];

    /**
     * Get log accepted levels
     * @return array
     */
    public function getAcceptedLevels() : array {
        return $this->levels;
    }

    /**
     * @inheritDoc
     * @param string $level
     * @param array|null $allowed
     * @return bool
     */
    public function filter(string $level, ?array $allowed): bool {
        if (empty($allowed)) {
            return true;
        }
        if (is_array($allowed)) {
            $merges = array_values(array_uintersect($this->levels, $allowed, 'strcasecmp'));
            if (in_array(strtolower($level), $merges)) {
                return true;
            }
        }
        return false;
    }

}

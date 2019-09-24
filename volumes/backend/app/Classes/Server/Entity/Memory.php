<?php namespace App\Classes\Server\Entity;

use Illuminate\Support\Str;

/**
 * Class Memory
 * @package App\Classes\Server\Entity
 */
class Memory {

    /**
     * Total system memory
     * @var null|integer
     */
    protected $total = null;

    /**
     * Free system memory
     * @var null|integer
     */
    protected $free = null;

    /**
     * Available system memory
     * @var null|integer
     */
    protected $available = null;

    /**
     * Used system memory
     * @var null|integer
     */
    protected $used = null;

    /**
     * Cached system memory
     * @var null|integer
     */
    protected $cached = null;

    /**
     * Memory constructor.
     */
    public function __construct() {
        $this->parseMemoryInformation();
    }

    /**
     * Get total system memory
     * @return int
     */
    public function total() : int {
        return $this->total;
    }

    /**
     * Get total system memory formatted to human readable
     * @return string
     */
    public function totalNice() : string {
        return $this->formatBytes($this->total);
    }

    /**
     * Get free system memory
     * @return int
     */
    public function free() : int {
        return $this->free;
    }

    /**
     * Get free system memory formatted to human readable
     * @return string
     */
    public function freeNice() : string {
        return $this->formatBytes($this->free);
    }

    /**
     * Get used system memory
     * @return int
     */
    public function used() : int {
        return $this->total - $this->available;
    }

    /**
     * Get used system memory formatted to human readable
     * @return string
     */
    public function usedNice() : string {
        return $this->formatBytes($this->used());
    }

    /**
     * Get available system memory
     * @return int
     */
    public function available() : int {
        return $this->available;
    }

    /**
     * Get available system memory formatted to human readable
     * @return string
     */
    public function availableNice() : string {
        return $this->formatBytes($this->available);
    }

    /**
     * Get cached system memory
     * @return int
     */
    public function cached() : int {
        return $this->cached;
    }

    /**
     * Get cached system memory formatted to human readable
     * @return string
     */
    public function cachedNice() : string {
        return $this->formatBytes($this->cached);
    }

    /**
     * Class to array
     * @return array
     */
    public function toArray() : array {
        return [
            'total'         =>  [
                'exact'     =>  $this->total,
                'nice'      =>  $this->formatBytes($this->total)
            ],
            'free'          =>  [
                'exact'     =>  $this->free,
                'nice'      =>  $this->formatBytes($this->free)
            ],
            'used'          =>  [
                'exact'     =>  $this->total - $this->available,
                'nice'      =>  $this->formatBytes($this->total - $this->available)
            ],
            'available'     =>  [
                'exact'     =>  $this->available,
                'nice'      =>  $this->formatBytes($this->available)
            ],
            'cached'        =>  [
                'exact'     =>  $this->cached,
                'nice'      =>  $this->formatBytes($this->cached)
            ]
        ];
    }

    /**
     * Parse memory information
     * @return void
     */
    protected function parseMemoryInformation() : void {
        $raw = explode(PHP_EOL, shell_exec('cat /proc/meminfo'));
        foreach ($raw as $row) {
            $totalMemory = $this->extractInformationFromString($row, 'MemTotal');
            if ($totalMemory !== null) {
                $this->total = $this->kilobytesToBytes($totalMemory);
            }

            $freeMemory = $this->extractInformationFromString($row, 'MemFree');
            if ($freeMemory !== null) {
                $this->free = $this->kilobytesToBytes($freeMemory);
            }

            $availableMemory = $this->extractInformationFromString($row, 'MemAvailable');
            if ($availableMemory !== null) {
                $this->available = $this->kilobytesToBytes($availableMemory);
            }

            $cachedMemory = $this->extractInformationFromString($row, 'Cached');
            if ($cachedMemory !== null) {
                $this->cached = $this->kilobytesToBytes($cachedMemory);
            }

        }
    }

    /**
     * Extract information from memory info row
     * @param string $source
     * @param string $query
     * @return string|null
     */
    protected function extractInformationFromString(string $source, string $query) : ?string {
        if (Str::startsWith($source, $query)) {
            return trim(str_replace([$query, ':', 'kB'], '', $source));
        }
        return null;
    }

    /**
     * Convert kilobytes to bytes
     * @param string $original
     * @return int
     */
    protected function kilobytesToBytes(string $original) : int {
        return (integer) $original * 1024;
    }

    /**
     * Convert bytes to human readable
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    protected function formatBytes(int $bytes, int $precision = 2) : string {
        $base = log($bytes, 1024);
        $suffixes = ['', 'KiB', 'MiB', 'GiB'];
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[(int) floor($base)];
    }

}

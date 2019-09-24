<?php namespace App\Classes\Server\Entity;

/**
 * Class Processor
 * @package App\Classes\Server\Entity
 */
class Processor {

    /**
     * Processor vendor
     * @var string|null
     */
    protected $vendor = null;

    /**
     * Processor model
     * @var string|null
     */
    protected $model = null;

    /**
     * Processor core count
     * @var int|null
     */
    protected $cores = null;

    /**
     * Processor thread count
     * @var int|null
     */
    protected $threads = null;

    /**
     * Processor current frequency
     * @var int|null
     */
    protected $frequency = null;

    /**
     * Processor constructor.
     */
    public function __construct() {
        $this->parseProcessorInformation();
    }

    /**
     * Get processor vendor
     * @return string
     */
    public function vendor() : string {
        return $this->vendor;
    }

    /**
     * Get processor model
     * @return string
     */
    public function model() : string {
        return $this->model;
    }

    /**
     * Get processor core count
     * @return integer
     */
    public function coreCount() : int {
        return $this->cores;
    }

    /**
     * Get processor thread count
     * @return integer
     */
    public function threadCount() : int {
        return $this->threads;
    }

    /**
     * Get processor frequency
     * @return float
     */
    public function frequency() : float {
        return $this->frequency;
    }

    /**
     * Class to array
     * @return array
     */
    public function toArray() : array {
        return [
            'vendor'        =>  $this->vendor,
            'model'         =>  $this->model,
            'cores'         =>  $this->cores,
            'threads'       =>  $this->threads,
            'frequency'     =>  $this->frequency
        ];
    }

    /**
     * Parse processor information
     * @return void
     */
    protected function parseProcessorInformation() : void {
        $vendor = 'Unknown';
        $model = $this->queryCpuInformation('model name');
        if (false !== strpos($model, 'AMD')) {
            $vendor = 'AMD';
            $model = str_replace(['AMD'], '', $model);
        } else if (false !== strpos($model, 'Intel')) {
            $vendor = 'Intel';
            $model = str_replace(['Intel', '(R)'], '', $model);
        }

        $this->vendor = $vendor;
        $this->cores = (integer) $this->queryCpuInformation('cpu cores');
        $this->threads = (integer) $this->queryCpuInformation('siblings');
        $this->frequency = (float) explode(PHP_EOL, $this->queryCpuInformation('cpu MHz'))[0];
        $this->model = trim(str_replace($this->cores . '-Core Processor', '', trim($model)));
    }

    /**
     * Get information about specific CPU parameter
     * @param string $query
     * @return string
     */
    protected function queryCpuInformation(string $query) : string {
        return trim(shell_exec("cat /proc/cpuinfo | grep '" . $query . "' | uniq | cut -f2 -d':'"));
    }

}

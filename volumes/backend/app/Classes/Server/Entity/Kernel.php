<?php namespace App\Classes\Server\Entity;

use Illuminate\Support\Str;

/**
 * Class Kernel
 * @package App\Classes\Server\Entity
 */
class Kernel {

    /**
     * Kernel version
     * @var null|string
     */
    protected $version = null;

    /**
     * Kernel OS name
     * @var null|string
     */
    protected $os = null;

    /**
     * Kernel OS version
     * @var null|string
     */
    protected $osVersion = null;

    /**
     * Kernel build date
     * @var null|string
     */
    protected $buildDate = null;

    /**
     * Kernel constructor.
     */
    public function __construct() {
        $this->parseKernelInformation();
    }

    /**
     * Get kernel version
     * @return string
     */
    public function version() : string {
        return $this->version;
    }

    /**
     * Get kernel os
     * @return string
     */
    public function os() : string {
        return $this->os;
    }

    /**
     * Get kernel OS version
     * @return string
     */
    public function osVersion() : string {
        return $this->osVersion;
    }

    /**
     * Get kernel build date
     * @return string
     */
    public function buildDate() : string {
        return $this->buildDate;
    }

    /**
     * Class to array
     * @return array
     */
    public function toArray() : array {
        return [
            'version'       =>  $this->version,
            'os'            =>  $this->os,
            'os_version'    =>  $this->osVersion,
            'build_date'    =>  $this->buildDate
        ];
    }

    /**
     * Parse kernel information
     * @return void
     */
    protected function parseKernelInformation() : void {
        $kernelString = shell_exec('cat /proc/version');
        $array = explode(' ', $kernelString);

        $this->version = trim($array[2]);
        $this->os = trim(str_replace(['(', ')'], '', $array[7]));
        $this->osVersion = trim(str_replace(')', '', Str::after($array[8], '~')));
        $this->buildDate = sprintf('%s %s, %s %s', $array[12], $array[13], str_replace(PHP_EOL, '', $array[16]), $array[14]);
    }

}

<?php namespace App\Classes\Server;

use App\Classes\Github\Github;
use App\Classes\Server\Entity\Kernel;
use App\Classes\Server\Entity\Memory;
use App\Classes\Server\Entity\Network;
use App\Classes\Server\Entity\Processor;
use Carbon\Carbon;

/**
 * Class Server
 * @package App\Classes\Server
 */
class Server {

    /**
     * Server kernel information
     * @var Kernel|null
     */
    protected ?Kernel $kernel = null;

    /**
     * Server processor information
     * @var Processor|null
     */
    protected ?Processor $processor = null;

    /**
     * Server memory information
     * @var Memory|null
     */
    protected ?Memory $memory = null;

    /**
     * Server network information
     * @var Network|null
     */
    protected ?Network $network = null;

    /**
     * Server constructor.
     */
    public function __construct() {
        $this->kernel = new Kernel;
        $this->processor = new Processor;
        $this->memory = new Memory;
        $this->network = new Network;
    }

    /**
     * Get kernel information class instance
     * @return Kernel
     */
    public function kernel() : Kernel {
        return $this->kernel;
    }

    /**
     * Get processor information class instance
     * @return Processor
     */
    public function processor() : Processor {
        return $this->processor;
    }

    /**
     * Get memory information class instance
     * @return Memory
     */
    public function memory() : Memory {
        return $this->memory;
    }

    /**
     * Get network information class instance
     * @return Network
     */
    public function network() : Network {
        return $this->network;
    }

    /**
     * Get server information
     * @return array
     */
    public function information() : array {
        return [
            'kernel'        =>  $this->kernel->toArray(),
            'processor'     =>  $this->processor->toArray(),
            'memory'        =>  $this->memory->toArray(),
            'network'       =>  $this->network->toArray(),
            'uptime'        =>  $this->getServerUptime(),
            'updates'       =>  $this->getUpdatesInformation()
        ];
    }

    /**
     * Get server uptime information
     * @return string
     */
    protected function getServerUptime() : string {
        $raw = shell_exec('uptime -p');
        return sprintf('%s %s', Carbon::now()->format('H:i'), trim(str_replace([PHP_EOL], '', $raw)));
    }

    /**
     * Get information about application updates
     * @return bool
     */
    protected function getUpdatesInformation() : bool {
        return (new Github)->updatesAvailable();
    }

}

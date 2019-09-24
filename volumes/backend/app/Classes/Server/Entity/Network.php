<?php namespace App\Classes\Server\Entity;

/**
 * Class Network
 * @package App\Classes\Server\Entity
 */
class Network {

    /**
     * IP address for the backend (API)
     * @var null|string
     */
    protected $backendIP = null;

    /**
     * @var null|string
     */
    protected $backendDomain = null;

    /**
     * IP address for the frontend (GUI)
     * @var null|string
     */
    protected $frontendIP = null;

    /**
     * @var null|string
     */
    protected $frontendDomain = null;

    /**
     * Class to array
     * @return array
     */
    public function toArray() : array {
        $nameservers = dns_get_record(env('API_DOMAIN'), DNS_NS);
        $nsAddresses = [];

        foreach ($nameservers as $nameserver) {
            $nsAddresses[] = [
                'domain'    =>  $nameserver['target'],
                'ip'        =>  gethostbyname($nameserver['target'])
            ];
        }

        return [
            'backend'       =>  [
                'remote_ip' =>  gethostbyname(env('API_DOMAIN')),
                'local_ip'  =>  $_SERVER['SERVER_ADDR'],
                'domain'    =>  env('API_DOMAIN')
            ],
            'frontend'      =>  [
                'remote_ip' =>  gethostbyname(env('MAIN_DOMAIN')),
                'local_ip'  =>  null,
                'domain'    =>  env('MAIN_DOMAIN')
            ],
            'nameservers'   =>  $nsAddresses
        ];
    }

}

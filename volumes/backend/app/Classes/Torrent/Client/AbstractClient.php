<?php namespace App\Classes\Torrent\Client;

use App\Classes\Torrent\Contract\TorrentInterface;

/**
 * Class AbstractClient
 * @package App\Classes\Torrent\Client
 */
abstract class AbstractClient implements TorrentInterface {

    /**
     * Download single torrent file
     * @param string $url
     * @param string $category
     *
     * @return TorrentInterface|static|self|$this
     */
    abstract public function download(string $url, string $category) : TorrentInterface;

    /**
     * Get torrent files
     * @param string $hash
     * @return array
     */
    abstract public function torrentFiles(string $hash) : array;

    /**
     * Initialize Client
     * @return AbstractClient|static|self|$this
     */
    abstract protected function initializeClient() : self;

}

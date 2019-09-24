<?php namespace App\Classes\Torrent\Contract;

/**
 * Interface TorrentInterface
 * @package App\Classes\Torrent\Contract
 */
interface TorrentInterface {

    /**
     * Download single torrent file
     * @param string $url
     * @param string $category
     *
     * @return static|self|$this
     */
    public function download(string $url, string $category) : self;

    /**
     * Get all active torrents on the server
     * @return array
     */
    public function listTorrents() : array;

}

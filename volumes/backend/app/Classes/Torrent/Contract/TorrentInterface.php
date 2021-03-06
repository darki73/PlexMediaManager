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

    /**
     * List torrents in the format acceptable by the dashboard
     * @return array
     */
    public function listTorrentsForDashboard() : array;

    /**
     * Resume torrent
     * @param string $hash
     * @return void
     */
    public function resumeTorrent(string $hash) : void;

    /**
     * Pause Torrent
     * @param string $hash
     * @return void
     */
    public function pauseTorrent(string $hash) : void;

    /**
     * Delete torrent
     * @param string $hash
     * @param bool $force
     * @return void
     */
    public function deleteTorrent(string $hash, bool $force = false) : void;

    /**
     * Create new category
     * @param string $categoryName
     * @return void
     */
    public function createCategory(string $categoryName) : void;

    /**
     * Upload torrent files and start the downloading procedure
     * @param array $files
     * @return void
     */
    public function createTorrent(array $files) : void;

    /**
     * Remove selected file from the download queue
     * @param string $hash
     * @param int $fileID
     * @return void
     */
    public function doNotDownload(string $hash, int $fileID) : void;

}

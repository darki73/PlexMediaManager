<?php namespace App\Classes\Torrent;

use RuntimeException;
use App\Classes\Torrent\Client\AbstractClient;
use App\Classes\Torrent\Contract\TorrentInterface;

/**
 * Class Torrent
 * @package App\Classes\Torrent
 */
class Torrent implements TorrentInterface {

    /**
     * Torrent client class
     * @var \Illuminate\Config\Repository|mixed|null
     */
    protected $clientClass = null;

    /**
     * Torrent client instance
     * @var AbstractClient|null
     */
    protected $client = null;

    /**
     * Torrent constructor.
     */
    public function __construct() {
        $this->clientClass = config('torrent.client');
        $this->initializeClient();
    }

    /**
     * Get torrent client implementation class
     * @return string|null
     */
    public function getClientClass() : ?string {
        return $this->clientClass;
    }

    /**
     * Download single torrent file
     * @param string $url
     * @param string $category
     *
     * @return TorrentInterface
     */
    public function download(string $url, string $category) : TorrentInterface {
        return $this->client->download($url, $category);
    }

    /**
     * Get torrent client instance
     * @return AbstractClient
     */
    public function client() : AbstractClient {
        return $this->client;
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function listTorrents(): array {
        return $this->client->listTorrents();
    }

    /**
     * List torrents in the format acceptable by the dashboard
     * @return array
     */
    public function listTorrentsForDashboard() : array {
        return $this->client->listTorrentsForDashboard();
    }

    /**
     * @inheritDoc
     * @param string $hash
     * @return void
     */
    public function resumeTorrent(string $hash): void {
        $this->client->resumeTorrent($hash);
        return;
    }

    /**
     * @inheritDoc
     * @param string $hash
     * @return void
     */
    public function pauseTorrent(string $hash): void {
        $this->client->pauseTorrent($hash);
        return;
    }

    /**
     * @inheritDoc
     * @param string $hash
     * @param bool $force
     * @return void
     */
    public function deleteTorrent(string $hash, bool $force = false): void {
        $this->client->deleteTorrent($hash, $force);
        return;
    }

    /**
     * Initialize torrent client
     * @return Torrent|static|self|$this
     */
    protected function initializeClient() : self {
        $this->client = new $this->clientClass;
        return $this;
    }

}

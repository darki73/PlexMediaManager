<?php namespace App\Classes\Storage;

/**
 * Class TorrentStorage
 * @package App\Classes\Storage
 */
class TorrentStorage extends AbstractStorage {

    /**
     * @inheritDoc
     * @var string
     */
    protected $storageType = 'torrent';

    /**
     * Plex Storage instance
     * @var PlexStorage|null
     */
    protected $plexStorage = null;

    /**
     * Information about remote series path
     * @var array
     */
    protected $remoteSeriesPath = [];

    /**
     * Information about remote movies path
     * @var array
     */
    protected $remoteMoviesPath = [];

    /**
     * TorrentStorage constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->plexStorage = new PlexStorage;
        $this->initializeRemotePaths();
    }

    /**
     * Get Plex storage instance
     * @return PlexStorage|null
     */
    public function getPlexStorage() : ?PlexStorage {
        return $this->plexStorage;
    }

    /**
     * Get storage path for specific type of content
     * @param string $type
     * @return array|null
     */
    public function getStorageFor(string $type) : ?array {
        switch ($type) {
            case 'series':
                return $this->remoteSeriesPath;
                break;
            case 'movies':
                return $this->remoteMoviesPath;
                break;
            default:
                return null;
        }
    }

    /**
     * Initialize storage paths outside of the container
     * @return TorrentStorage|static|self|$this
     */
    protected function initializeRemotePaths() : self {
        $drive = $this->plexStorage->getNextUsableDrive();
        $this->remoteSeriesPath = [
            'torrent'       =>  [
                'local'     =>  $this->seriesPath,
                'remote'    =>  config('storage.mounts.torrent') . DIRECTORY_SEPARATOR . 'series'
            ],
            'plex'          =>  [
                'local'     =>  $this->plexStorage->getSeriesPath(),
                'remote'    =>  $drive['remote_mount'] . DIRECTORY_SEPARATOR . 'series'
            ]
        ];
        $this->remoteMoviesPath = [
            'torrent'       =>  [
                'local'     =>  $this->moviesPath,
                'remote'    =>  config('storage.mounts.torrent') . DIRECTORY_SEPARATOR . 'movies'
            ],
            'plex'          =>  [
                'local'     =>  $this->plexStorage->getMoviesPath(),
                'remote'    =>  $drive['remote_mount'] . DIRECTORY_SEPARATOR . 'movies'
            ]
        ];
        return $this;
    }

}

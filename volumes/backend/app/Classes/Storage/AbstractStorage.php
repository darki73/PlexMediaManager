<?php namespace App\Classes\Storage;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Class AbstractStorage
 * @package App\Classes\Storage
 */
abstract class AbstractStorage {

    /**
     * Laravel disk name
     * @var string
     */
    protected $disk = 'local';

    /**
     * Storage type
     * @var null|string
     */
    protected $storageType = null;

    /**
     * Private folder absolute and relative paths array
     * @var array
     */
    protected $privateFolder = [];

    /**
     * Movies path array
     * @var array
     */
    protected $moviesPath = [];

    /**
     * Series path array
     * @var array
     */
    protected $seriesPath = [];

    /**
     * AbstractStorage constructor.
     */
    public function __construct() {
        $this
            ->resolvePrivateFolderPath()
            ->initializeMediaPaths();
    }

    /**
     * List all directories at specified path
     * @param string $path
     * @param bool $recursive
     * @return array
     */
    public function listDirectories(string $path, bool $recursive = false) : array {
        if ($this->isAbsolutePath($path)) {
            $path = $this->storageRelativePath($path);
        }
        if ($recursive) {
            return $this->storage()->allDirectories($path);
        }
        return $this->storage()->directories($path);
    }

    /**
     * List all files at specified path
     * @param string $path
     * @param bool $relative
     * @return array
     */
    public function listFiles(string $path, bool $relative = false) : array {
        if ($relative) {
            return $this->storage()->allFiles($path);
        }
        return $this->storage()->files($path);
    }

    /**
     * Convert bytes to human readable
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public function formatBytes(int $bytes, int $precision = 2) : string {
        $base = log($bytes, 1024);
        $suffixes = ['', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[(int) floor($base)];
    }

    /**
     * Convert human readable to bytes
     * @param int $value
     * @param string $unit
     * @return int
     */
    public function formatNice(int $value, string $unit = '') : int {
        $units = [
            ''      =>  1,
            'KB'    =>  1024,
            'MB'    =>  1024 * 1024,
            'GB'    =>  1024 * 1024 * 1024,
            'TB'    =>  1024 * 1024 * 1024 * 1024
        ];

        if (! array_key_exists(strtoupper($unit), $units)) {
            throw new RuntimeException('Unable to find a scale for `' . strtoupper($unit) . '`, we are only supporting disks up to 999 TB');
        }
        return (int) $value * $units[strtoupper($unit)];
    }

    /**
     * Convert relative path to absolute
     * @param string $relativePath
     * @return string
     */
    public function relativeToAbsolute(string $relativePath) : string {
        return $this->absolutePathPrefix() . DIRECTORY_SEPARATOR . $relativePath;
    }

    /**
     * Convert absolute path to relative
     * @param string $absolutePath
     * @return string
     */
    public function absoluteToRelative(string $absolutePath) : string {
        return str_replace($this->absolutePathPrefix() . DIRECTORY_SEPARATOR, '', $absolutePath);
    }

    /**
     * Get series path information
     * @return array
     */
    public function getSeriesPath() : array {
        return $this->seriesPath;
    }

    /**
     * Get movies path information
     * @return array
     */
    public function getMoviesPath() : array {
        return $this->moviesPath;
    }

    /**
     * Initialize variables for media paths
     * @return PlexStorage|static|self|$this
     */
    protected function initializeMediaPaths() : self {
        $basePath = [
            'relative'      =>  $this->resolveForPrivateFolder($this->storageType),
            'absolute'      =>  $this->resolveForPrivateFolder($this->storageType, true)
        ];
        $this->moviesPath = [
            'relative'      =>  $basePath['relative'] . DIRECTORY_SEPARATOR . 'movies',
            'absolute'      =>  $basePath['absolute'] . DIRECTORY_SEPARATOR . 'movies'
        ];
        $this->seriesPath = [
            'relative'      =>  $basePath['relative'] . DIRECTORY_SEPARATOR . 'series',
            'absolute'      =>  $basePath['absolute'] . DIRECTORY_SEPARATOR . 'series'
        ];
        return $this;
    }

    /**
     * Get relative path for the private folder
     * @return string
     */
    protected function privateRelativePath() : string {
        return $this->privateFolder['relative'];
    }

    /**
     * Get absolute path for the private folder
     * @return string
     */
    protected function privateAbsolutePath() : string {
        return $this->privateFolder['absolute'];
    }

    /**
     * Get Storage instance
     * @return Filesystem
     */
    protected function storage() : Filesystem {
        return Storage::disk($this->disk);
    }

    /**
     * Resolve path with relation to private storage folder
     * @param string $path
     * @param bool $asAbsolute
     * @return string
     */
    protected function resolveForPrivateFolder(string $path, bool $asAbsolute = false) : string {
        if ($asAbsolute) {
            return $this->storageAbsolutePath('private' . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR));
        }
        return $this->storageRelativePath('private' . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR));
    }

    /**
     * Get absolute path
     * @param string $path
     * @return string
     */
    protected function getAbsolutePath(string $path) : string {
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        if (Str::startsWith($path, 'private' . DIRECTORY_SEPARATOR)) {
            return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, ['app', 'storage', 'app']) . DIRECTORY_SEPARATOR . $path;
        }
        return $this->resolveForPrivateFolder($path);
    }

    /**
     * Check whether or not path is absolute
     * @param string $path
     * @return bool
     */
    protected function isAbsolutePath(string $path) : bool {
        return Str::startsWith($path, DIRECTORY_SEPARATOR . $this->absolutePathPrefix());
    }

    /**
     * Check whether or not path is relative
     * @param string $path
     * @return bool
     */
    protected function isRelativePath(string $path) : bool {
        return !$this->isAbsolutePath($path);
    }


    /**
     * Get absolute storage path prefix string
     * @return string
     */
    private function absolutePathPrefix() : string {
        return DIRECTORY_SEPARATOR . implode(
            DIRECTORY_SEPARATOR,
            [
                'app',
                'storage',
                'app'
            ]
        );
    }

    /**
     * Get absolute storage path for the provided path part
     * @param string $path
     * @return string
     */
    private function storageAbsolutePath(string $path) : string {
        return $this->absolutePathPrefix() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Get relative storage path for the provided path part
     * @param string $path
     * @return string
     */
    private function storageRelativePath(string $path) : string {
        return str_replace(
            $this->absolutePathPrefix(),
            '',
            $path
        );
    }

    /**
     * Resolve path to private folder
     * @return AbstractStorage|static|self|$this
     */
    private function resolvePrivateFolderPath() : self {
        $this->privateFolder = [
            'relative'  =>  $this->storageRelativePath('private'),
            'absolute'  =>  $this->storageAbsolutePath('private')
        ];
        return $this;
    }


}

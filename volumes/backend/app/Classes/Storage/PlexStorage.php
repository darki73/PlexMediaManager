<?php namespace App\Classes\Storage;

use RuntimeException;

/**
 * Class PlexStorage
 * @package App\Classes\Storage
 */
class PlexStorage extends AbstractStorage {

    /**
     * @inheritDoc
     * @var string
     */
    protected $storageType = 'plex';

    /**
     * List of mounted drives
     * @var array
     */
    protected $mountedDrives = [];

    /**
     * Drive which was deemed best for writing data to
     * @var null|string
     */
    protected $bestDrive = null;

    /**
     * PlexStorage constructor.
     */
    public function __construct() {
        parent::__construct();
        $this
            ->listAvailableDrives()
            ->selectBestDrive();
    }

    /**
     * List media available on all mounted drives
     * @param string $mediaType
     * @return array
     */
    public function listMediaForAllDrives(string $mediaType) : array {
        $relativePathMethod = strtolower($mediaType) . 'RelativePath';
        $absolutePathMethod = strtolower($mediaType) . 'AbsolutePath';

        if (!method_exists($this, $relativePathMethod) || !method_exists($this, $absolutePathMethod)) {
            throw new RuntimeException('Media type of `' . $mediaType . '` is not supported!');
        }

        $mediaPaths = [];

        foreach ($this->mountedDrives as $drive => $stats) {
            $relativeContentPath = $this->$relativePathMethod($drive);
            $absoluteContentPath = $this->$absolutePathMethod($drive);

            switch ($mediaType) {
                case 'series':
                    foreach ($this->listDirectories($relativeContentPath) as $path) {
                        $mediaName = str_replace($relativeContentPath . DIRECTORY_SEPARATOR, '', $path);
                        $mediaPaths[] = [
                            'content_path'  =>  [
                                'relative'  =>  $relativeContentPath,
                                'absolute'  =>  $absoluteContentPath
                            ],
                            'media_path'    =>  [
                                'relative'  =>  $relativeContentPath . DIRECTORY_SEPARATOR . $mediaName,
                                'absolute'  =>  $absoluteContentPath . DIRECTORY_SEPARATOR . $mediaName,
                            ],
                            'drive'         =>  $drive
                        ];
                    }
                    break;
                case 'movies':
                    foreach ($this->listFiles($relativeContentPath) as $path) {
                        $mediaName = str_replace($relativeContentPath . DIRECTORY_SEPARATOR, '', $path);
                        $mediaPaths[] = [
                            'content_path'  =>  [
                                'relative'  =>  $relativeContentPath,
                                'absolute'  =>  $absoluteContentPath
                            ],
                            'media_path'    =>  [
                                'relative'  =>  $relativeContentPath . DIRECTORY_SEPARATOR . $mediaName,
                                'absolute'  =>  $absoluteContentPath . DIRECTORY_SEPARATOR . $mediaName,
                            ],
                            'drive'         =>  $drive
                        ];
                    }
                    break;
            }

        }

        return $mediaPaths;
    }

    /**
     * Get drive information
     * @param string $drive
     * @return array|null
     */
    public function driveInformation(string $drive) : ?array {
        return $this->mountedDrives[$drive] ?? null;
    }

    /**
     * Get best drive name
     * @return string
     */
    public function getBestDrive() : string {
        return $this->bestDrive;
    }

    /**
     * Get list of mounted drives
     * @return array
     */
    public function getMountedDrives() : array {
        return $this->mountedDrives;
    }

    /**
     * Get next usable for data writing drive
     * @return array
     */
    public function getNextUsableDrive() : array {
        return $this->mountedDrives[$this->bestDrive];
    }

    /**
     * Calculate number of series and movies on the drive
     * @return PlexStorage|static|self|$this
     */
    public function countSeriesMovies() : self {
        $seriesData = [];
        $moviesData = [];

        foreach ($this->mountedDrives as $drive => $information) {
            $seriesPath = $this->seriesRelativePath($drive);
            $moviesPath = $this->moviesRelativePath($drive);
            $this->calculateSeriesMoviesPerDrive($drive, $seriesPath, $seriesData, 'episodes');
            $this->calculateSeriesMoviesPerDrive($drive, $moviesPath, $moviesData);
            if (! array_key_exists('series', $seriesData)) {
                $seriesData[$drive]['series'] = \count($this->listDirectories($seriesPath));
            } else {
                $seriesData[$drive]['series'] += \count($this->listDirectories($seriesPath));
            }
        }

        foreach ($seriesData as $drive => $data) {
            $seriesData[$drive]['size']['nice'] = $this->formatBytes($seriesData[$drive]['size']['exact']);
        }
        foreach ($moviesData as $drive => $data) {
            $moviesData[$drive]['size']['nice'] = $this->formatBytes($moviesData[$drive]['size']['exact']);
        }

        foreach ($this->mountedDrives as $drive => $data) {
            $this->mountedDrives[$drive]['media']['series'] = $seriesData[$drive];
            $this->mountedDrives[$drive]['media']['movies'] = $moviesData[$drive];
            $mounts = [];
            foreach ($this->listDirectories('private/plex') as $mediaType) {
                foreach ($this->listDirectories($mediaType) as $path) {
                    $disk = str_replace($mediaType . DIRECTORY_SEPARATOR, '', $path);
                    if (trim($drive) === trim($disk)) {
                        $mounts[] = $this->relativeToAbsolute($path);
                    }
                }
            }
            $this->mountedDrives[$drive]['media']['mounts'] = $mounts;
        }
        return $this;
    }

    /**
     * Calculate series and movies per drive
     * @param string $drive
     * @param string $path
     * @param array $array
     * @param string $fieldName
     * @return void
     */
    private function calculateSeriesMoviesPerDrive(string $drive, string $path, array & $array, string $fieldName = 'count') : void {
        foreach ($this->listFiles($path, true) as $item) {
            if (! array_key_exists($drive, $array)) {
                $array[$drive] = [
                    $fieldName      =>  0,
                    'size'          =>  [
                        'exact'     =>  0,
                        'nice'      =>  null
                    ]
                ];
            }
            $absolutePath = $this->relativeToAbsolute($item);
            $array[$drive][$fieldName] += 1;
            $array[$drive]['size']['exact'] += filesize($absolutePath);
        }
        return;
    }

    /**
     * Get mounted drives
     * @return array
     */
    public function drives() : array {
        return $this->mountedDrives;
    }

    /**
     * Get list of mounted drives
     * @return PlexStorage|static|self|$this
     */
    protected function listAvailableDrives() : self {
        $relativePath = $this->seriesRelativePath();
        foreach ($this->listDirectories($relativePath) as $path) {
            $driveName = trim(str_replace(
                $relativePath . DIRECTORY_SEPARATOR,
                '',
                $path
            ));

            $this->mountedDrives[$driveName] = $this->getDriveInformation($this->getAbsolutePath($path), $driveName);
        }
        return $this;
    }

    /**
     * Determine which drive is the best for writing data to
     * @return PlexStorage|static|self|$this
     */
    protected function selectBestDrive() : self {
        // We have to do it twice, since we dont know if the preferred drive will be first in the list
        foreach ($this->mountedDrives as $drive => $stats) {
            $preferred = $stats['preferred'];
            $usable = $stats['usable'];

            if ($preferred && $usable) {
                $this->bestDrive = $drive;
                break;
            }
        }

        if ($this->bestDrive === null) {
            foreach ($this->mountedDrives as $drive => $stats) {
                if ($stats['usable']) {
                    $this->bestDrive = $drive;
                    break;
                }
            }
        }
        return $this;
    }

    /**
     * Get relative|absolute path for specified media with respect to provided drive
     * Trying to DRY'ing code a bit
     * @param array $media
     * @param string $type
     * @param string|null $drive
     * @return string
     */
    protected function mediaPath(array $media, string $type, ?string $drive = null) : string {
        $path = $media[$type];
        if ($drive !== null) {
            $path .= DIRECTORY_SEPARATOR . $drive;
        }
        return $path;
    }

    /**
     * Get absolute path for series
     * IF drive is specified, path to specific drive will be returned instead
     * @param string|null $drive
     * @return string
     */
    protected function seriesAbsolutePath(?string $drive = null) : string {
        return $this->mediaPath($this->seriesPath, 'absolute', $drive);
    }

    /**
     * Get absolute path for series with best drive in mind
     * @return string
     */
    protected function seriesAbsolutePathBestDrive() : string {
        return $this->seriesAbsolutePath($this->bestDrive);
    }

    /**
     * Get relative path for series
     * IF drive is specified, path to specific drive will be returned instead
     * @param string|null $drive
     * @return string
     */
    protected function seriesRelativePath(?string $drive = null) : string {
        return $this->mediaPath($this->seriesPath, 'relative', $drive);
    }

    /**
     * Get relative path for series with best drive in mind
     * @return string
     */
    protected function seriesRelativePathBestDrive() : string {
        return $this->seriesRelativePath($this->bestDrive);
    }

    /**
     * Get absolute path for movies
     * IF drive is specified, path to specific drive will be returned instead
     * @param string|null $drive
     * @return string
     */
    protected function moviesAbsolutePath(?string $drive = null) : string {
        return $this->mediaPath($this->moviesPath, 'absolute', $drive);
    }

    /**
     * Get absolute path for movies with best drive in mind
     * @return string
     */
    protected function moviesAbsolutePathBestDrive() : string {
        return $this->moviesAbsolutePath($this->bestDrive);
    }

    /**
     * Get relative path for movies
     * IF drive is specified, path to specific drive will be returned instead
     * @param string|null $drive
     * @return string
     */
    protected function moviesRelativePath(?string $drive = null) : string {
        return $this->mediaPath($this->moviesPath, 'relative', $drive);
    }

    /**
     * Get relative path for movies with best drive in mind
     * @return string
     */
    protected function moviesRelativePathBestDrive() : string {
        return $this->moviesRelativePath($this->bestDrive);
    }

    /**
     * Get information about single drive
     * @param string $path
     * @param string $drive
     * @return array
     */
    private function getDriveInformation(string $path, string $drive) : array {
        $totalSpace = disk_total_space($path);
        $freeSpace = disk_free_space($path);
        $usedSpace = $totalSpace - $freeSpace;
        $usedPercentage = (int) (100 - round($freeSpace / $totalSpace * 100, 0));
        $remainingPercentage = (int) (100 - $usedPercentage);

        return [
            'preferred'         =>  config('storage.preferred') === $drive,
            'usable'            =>  $this->isDriveUsable($freeSpace, $remainingPercentage),
            'remote_mount'      =>  config('storage.mounts.plex.' . $drive),
            'path'              =>  [
                'relative'      =>  $this->absoluteToRelative($path),
                'absolute'      =>  $path
            ],
            'total_space'       =>  [
                'exact'         =>  $totalSpace,
                'nice'          =>  $this->formatBytes($totalSpace)
            ],
            'free_space'        =>  [
                'exact'         =>  $freeSpace,
                'nice'          =>  $this->formatBytes($freeSpace)
            ],
            'used_space'        =>  [
                'exact'         =>  $usedSpace,
                'nice'          =>  $this->formatBytes($usedSpace)
            ],
            'percentage'        =>  [
                'used'          =>  $usedPercentage,
                'free'          =>  $remainingPercentage
            ],
        ];
    }

    /**
     * Check if drive could be used to save data
     * We are checking against the env variables to see whether or not we can use the drive to save data to
     * @param int $freeSpace
     * @param int $percentage
     * @return bool
     */
    private function isDriveUsable(int $freeSpace, int $percentage) : bool {
        $threshold = config('storage.threshold.value');
        if (config('storage.threshold.percentage')) {
            if ($threshold > 100) {
                throw new RuntimeException('When you are using percentage based threshold, please ensure it is not higher than `100`. Right now it is set to `' . $threshold . '`.');
            }
            return $percentage > $threshold;
        } else {
            $renderNotUsableAt = $this->formatNice($threshold, config('storage.threshold.units'));
            return $freeSpace > $renderNotUsableAt;
        }
    }

}

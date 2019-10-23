<?php namespace App\Classes\TheMovieDB\Downloader;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

/**
 * Class DumpDownloader
 * @package App\Classes\TheMovieDB\Downloader
 */
class DumpDownloader {

    /**
     * TheMovieDatabase dumps location
     * @var string
     */
    protected string $source = 'http://files.tmdb.org/p/exports';

    /**
     * Path to the storage
     * @var string|null
     */
    protected ?string $storage = null;

    /**
     * Default format for Movies files
     * @var string
     */
    protected string $moviesFormat = 'movie_ids_%s_%s_%s.json.gz';

    /**
     * Default format for Series files
     * @var string
     */
    protected string $seriesFormat = 'tv_series_ids_%s_%s_%s.json.gz';

    /**
     * Selected format string
     * @var string|null
     */
    protected ?string $selectedFormat = null;

    /**
     * Format we are working with
     * @var string|null
     */
    protected ?string $format = null;

    /**
     * Current Date Time
     * @var Carbon
     */
    protected ?Carbon $now = null;

    /**
     * "Yesterday" Date Time
     * @var Carbon|null
     */
    protected ?Carbon $yesterday = null;

    /**
     * DumpDownloader constructor.
     */
    public function __construct() {
        $this->storage = storage_path('temp');
        $this->now = Carbon::now();
        $this->yesterday = Carbon::yesterday();
    }

    /**
     * Get instance of class with the 'series' in mind
     * @return DumpDownloader|static|self|$this
     */
    public function series() : self {
        $this->selectedFormat = $this->seriesFormat;
        $this->format = $this->formatBuilder($this->seriesFormat);
        return $this;
    }

    /**
     * Get instance of class with the 'movies' in mind
     * @return DumpDownloader|static|self|$this
     */
    public function movies() : self {
        $this->selectedFormat = $this->moviesFormat;
        $this->format = $this->formatBuilder($this->moviesFormat);
        return $this;
    }


    /**
     * Get full path to the file
     * @return string
     */
    public function getLocalPath() : string {
        $this->download();
        return sprintf('%s/%s', $this->storage, str_replace('.gz', '', $this->format));
    }

    /**
     * Download and extract file
     * @return void
     */
    public function download() : void {
        $remoteFilePath = sprintf('%s/%s', $this->source, $this->format);
        $localPath = sprintf('%s/%s', $this->storage, $this->format);
        $localJson = str_replace('.gz', '', $localPath);
        if (! File::exists($localJson)) {
            file_put_contents($localPath, file_get_contents($remoteFilePath));
            shell_exec(sprintf('gunzip %s', $localPath));
        }
        $this->cleanUp($localPath);
    }

    /**
     * Whether or now we can download new dump
     * @return bool
     */
    public function canDownload() : bool {
        return $this->now->hour >= 8;
    }

    /**
     * Format builder
     * @param string $format
     * @param bool $forceYesterday
     * @return string
     */
    protected function formatBuilder(string $format, bool $forceYesterday = false) : string {
        if (!$this->canDownload() || $forceYesterday) {
            return sprintf($format, pad($this->yesterday->month), pad($this->yesterday->day), $this->yesterday->year);
        }
        return sprintf($format, pad($this->now->month), pad($this->now->day), $this->now->year);
    }

    /**
     * Cleanup temp directory
     * @param string $localPath
     * @return void
     */
    protected function cleanUp(string $localPath) : void {
        if (File::exists($localPath)) {
            unlink($localPath);
        }
        $previousJsonDataFile = str_replace('.gz', '', $this->formatBuilder($this->selectedFormat, $this->canDownload()));
        if (File::exists($previousJsonDataFile)) {
            unlink($previousJsonDataFile);
        }
    }

}

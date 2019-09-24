<?php namespace App\Classes\Media\Source\Type;

use Exception;
use App\Classes\Media\Source\Parser\SeriesNameParser;

/**
 * Class Series
 * @package App\Classes\Media\Source\Type
 */
class Series extends AbstractType {

    /**
     * @inheritDoc
     * @var string
     */
    protected $source = 'series';

    /**
     * List of processed series
     * @var array
     */
    protected $series = [];

    /**
     * @inheritDoc
     * @return array
     */
    public function list() : array {
        return $this->series;
    }

    /**
     * Whether or not we should skip series from indexing
     * @param string $path
     * @return bool
     */
    protected function shouldSkip(string $path) : bool {
        return file_exists($path . DIRECTORY_SEPARATOR . '.skip');
    }

    /**
     * @inheritDoc
     * @return AbstractType|static|self|$this
     * @throws Exception
     */
    protected function processStorageItems(): AbstractType {
        foreach ($this->rawElements as $element) {
            $absolutePath = $this->absoluteMediaPath($element);
            $relativePath = $this->relativeMediaPath($element);
            $name = str_replace($this->relativeContentPath($element) . DIRECTORY_SEPARATOR, '', $relativePath);

            $originalName = $name;
            $seriesYear = null;

            if (false !== strpos($name, '(') && false !== strpos($name, ')')) {
                $seriesYear = $this->extractYear($name);
                $name = trim(str_replace('(' . $seriesYear . ')', '', $name));
            }

            if (! $this->shouldSkip($absolutePath)) {
                if (!array_key_exists($name, $this->series)) {
                    $this->series[$name] = [
                        'name'              =>  $name,
                        'original_name'     =>  $originalName,
                        'year'              =>  $seriesYear,
                        'seasons'           =>  []
                    ];
                    $this->series[$name]['seasons'] = $this->processSeriesSeasons($relativePath, $name);
                } else {
                    foreach ($this->processSeriesSeasons($relativePath, $name) as $season => $seasonDetails) {
                        if (!array_key_exists($season, $this->series[$name]['seasons'])) {
                            $this->series[$name]['seasons'][$season] = $seasonDetails;
                        } else {
                            $this->series[$name]['seasons'][$season]['episodes'] = $this->series[$name]['seasons'][$season]['episodes'] + $seasonDetails['episodes'];
                            ksort($this->series[$name]['seasons'][$season]['episodes']);
                        }
                    }
                }
                ksort($this->series[$name]['seasons']);
            }
        }
        return $this;
    }

    /**
     * Process series seasons
     * @param string $seriesRelativePath
     * @param string $seriesName
     * @return array
     * @throws Exception
     */
    protected function processSeriesSeasons(string $seriesRelativePath, string $seriesName) : array {
        $seriesSeasons = $this->storage->listDirectories($seriesRelativePath);
        $seasons = [];

        foreach ($seriesSeasons as $season) {
            $seasonName = str_replace($seriesRelativePath . DIRECTORY_SEPARATOR, '', $season);
            $seasonNumber = (int) trim(str_replace('Season', '', $seasonName));
            $seasonAbsolutePath = $this->storage->relativeToAbsolute($season);
            $seasons[$seasonNumber] = [
                'season'            =>  $seasonNumber,
                'name'              =>  $seasonName,
                'year'              =>  $this->extractYear($seriesRelativePath),
                'episodes'          =>  $this->processSeasonEpisodes($season, $seasonAbsolutePath)
            ];
        }

        return $seasons;
    }

    /**
     * Process episodes for specified series and season
     * @param string $seasonRelativePath
     * @param string $seasonAbsolutePath
     * @return array
     * @throws Exception
     */
    protected function processSeasonEpisodes(string $seasonRelativePath, string $seasonAbsolutePath) : array {
        $seasonEpisodes = $this->storage->listFiles($seasonRelativePath);
        $episodes = [];

        foreach ($seasonEpisodes as $episode) {
            $episodeFile = str_replace($seasonRelativePath . DIRECTORY_SEPARATOR, '', $episode);
            $absolutePath = $seasonAbsolutePath . DIRECTORY_SEPARATOR . $episodeFile;
            $extension = pathinfo($episodeFile, PATHINFO_EXTENSION);

            if (in_array(strtolower($extension), $this->allowedExtensions)) {
                $data = SeriesNameParser::extractInformation($episodeFile);
                $fileSize = filesize($absolutePath);

                if (isset($data['series'])) {
                    $episodes[$data['episode']] = array_merge(
                        $data,
                        [
                            'path'              =>  [
                                'relative'      =>  $episode,
                                'absolute'      =>  $absolutePath
                            ],
                            'size'              =>  [
                                'exact'         =>  $fileSize,
                                'nice'          =>  $this->storage->formatBytes($fileSize)
                            ]
                        ]
                    );
                } else {
                    if (\count($data) > 1) {
                        foreach ($data as $episodeNumber => $details) {
                            $episodes[$episodeNumber] = array_merge(
                                $details,
                                [
                                    'path'              =>  [
                                        'relative'      =>  $episode,
                                        'absolute'      =>  $absolutePath
                                    ],
                                    'size'              =>  [
                                        'exact'         =>  $fileSize,
                                        'nice'          =>  $this->storage->formatBytes($fileSize)
                                    ]
                                ]
                            );
                        }
                    }
                }
            }

        }

        return $episodes;
    }

}

<?php namespace App\Classes\Media\Source\Parser;

use Exception;
use Illuminate\Support\Arr;

/**
 * Class SeriesNameParser
 * @package App\Classes\Media\Source\Parser
 */
class SeriesNameParser {

    /**
     * Series file name
     * @var string|null
     */
    protected $fileName = null;

    /**
     * What should be removed from file name
     * at different stages of processing
     * @var array
     */
    private $removeFromName = [];

    /**
     * File extension
     * @var string|null
     */
    protected $extension = null;

    /**
     * Original series name before any processing
     * @var string|null
     */
    protected $originalName = null;

    /**
     * Series name
     * @var string|null
     */
    protected $name = null;

    /**
     * Year when series was first aired
     * @var int|null
     */
    protected $year = null;

    /**
     * Series season number
     * @var integer|null
     */
    protected $season = null;

    /**
     * Series episode number
     * @var integer|null
     */
    protected $episode = null;

    /**
     * List of episodes in a single file
     * @var array
     */
    protected $episodes = [];

    /**
     * Whether or not one media file contains multiple episodes for series
     * @var bool
     */
    protected $multipleEpisodesInFile = false;

    /**
     * Extract information from series file name
     * @param string $fileName
     * @return array
     * @throws Exception
     */
    public static function extractInformation(string $fileName) : array {
        $instance = new self($fileName);
        return $instance->toArray();
    }

    /**
     * SeriesNameParser constructor.
     * @param string $fileName
     * @throws Exception
     */
    protected function __construct(string $fileName) {
        $this->fileName = $fileName;
        $this->extractDataFromFileName();
    }

    /**
     * Extract series data from the file name
     * @return SeriesNameParser|static|self|$this
     * @throws Exception
     */
    protected function extractDataFromFileName() : self {
        $this
            ->extractFileExtension()
            ->extractSeriesName()
            ->extractSeasonAndEpisode();
        return $this;
    }

    /**
     * Extract file extension
     * @return SeriesNameParser|static|self|$this
     */
    protected function extractFileExtension() : self {
        $this->extension = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $this->removeFromName[] = '.' . $this->extension;
        return $this;
    }

    /**
     * Extract series name
     * @return SeriesNameParser|static|self|$this
     * @throws Exception
     */
    protected function extractSeriesName() : self {
        $tempName = trim(Arr::first(explode('-', $this->fileName)));
        if (
            false !== strpos($tempName, '(')
            && false !== strpos($tempName, ')')
        ) {
            $extracted = $this->extractSeriesYear($tempName);
            if (isset($extracted['year'], $extracted['replace'])) {
                $this->year = $extracted['year'];
                $this->name = trim(str_replace($extracted['replace'], '', $tempName));
            } else {
                throw new Exception($this->fileName);
            }
        } else {
            $this->name = $tempName;
        }
        $this->originalName = $tempName;
        $this->removeFromName[] = $tempName;
        return $this;
    }

    /**
     * Extract year when series was first aired
     * @param string $tempName
     * @return array|null
     */
    protected function extractSeriesYear(string $tempName) : ?array {
        preg_match( '!\(([^\)]+)\)!', $tempName, $match );
        if (count($match) === 2) {
            return [
                'year'      =>  (int) $match[1],
                'replace'   =>  (string) $match[0]
            ];
        }
        return null;
    }

    /**
     * Extract season and episode numbers from series file name
     * @return SeriesNameParser|static|self|$this
     * @throws Exception
     */
    protected function extractSeasonAndEpisode() : self {
        $stillRawString = trim(ltrim(trim(str_replace($this->removeFromName, '', $this->fileName)), '-'));

        $regexForSingleEpisode = '/s\d{1,2}e\d{0,3}/';
        $regexForMultipleEpisodesVersionOne = '/s\d{1,2}e\d{1,3}-\d{1,3}/';
        $regexForMultipleEpisodesVersionTwo = '/s\d{1,2}e\d{1,3}-e\d{1,3}/';

        preg_match($regexForSingleEpisode, $stillRawString, $singleEpisodeMatches);
        preg_match($regexForMultipleEpisodesVersionOne, $stillRawString, $multipleEpisodesMatchesOne);
        preg_match($regexForMultipleEpisodesVersionTwo, $stillRawString, $multipleEpisodesMatchesTwo);

        $isSingleEpisode = \count($singleEpisodeMatches) === 1;
        $isMultipleEpisodesOne = \count($multipleEpisodesMatchesOne) === 1;
        $isMultipleEpisodesTwo = \count($multipleEpisodesMatchesTwo) === 1;

        if ($isMultipleEpisodesOne || $isMultipleEpisodesTwo) {
            $this->multipleEpisodesInFile = true;
        }

        if ($isSingleEpisode && !$isMultipleEpisodesOne && !$isMultipleEpisodesTwo) {
            $this->extractSeasonAndEpisodeSingle($stillRawString);
        } else if ($isMultipleEpisodesOne) {
            $this->extractSeasonAndEpisodeMultiple($stillRawString);
        } else if ($isMultipleEpisodesTwo) {
            $this->extractSeasonAndEpisodeMultiple($stillRawString, true);
        } else {
            throw new Exception('Unable to extract series Season and Episode information from the file name');
        }

        return $this;
    }

    /**
     * Extract season and episode from file name which contains single episode
     * @param string $string
     * @return SeriesNameParser|static|self|$this
     */
    protected function extractSeasonAndEpisodeSingle(string $string) : self {
        preg_match('/s(\d{2,3})e\d{2,3}/i', $string, $seasonMatches);
        preg_match('/s\d{2,3}e(\d{2,3})/i', $string, $episodeMatches);
        $this->season = (int) $seasonMatches[1];
        $this->episode = (int) $episodeMatches[1];
        return $this;
    }

    /**
     * Extract season and episode from file name which contains multiple episode
     * @param string $string
     * @param bool $secondPrefixed
     * @return SeriesNameParser|static|self|$this
     */
    protected function extractSeasonAndEpisodeMultiple(string $string, bool $secondPrefixed = false) : self {
        $regex = $secondPrefixed ? '/s\d{2,3}e(\d{2,3})-e(\d{2,3})/i' : '/s\d{2,3}e(\d{2,3})-(\d{2,3})/i';

        preg_match('/s(\d{2,3})e\d{2,3}/i', $string, $seasonMatches);
        preg_match($regex, $string, $episodesMatches);
        $this->season = (int) $seasonMatches[1];
        for ($i = 1; $i < count($episodesMatches); $i++) {
            $this->episodes[] = (int) $episodesMatches[$i];
        }
        return $this;
    }

    /**
     * Convert class to array
     * @return array
     */
    protected function toArray() : array {
        if ($this->multipleEpisodesInFile) {
            $episodes = [];
            foreach ($this->episodes as $episode) {
                $episodes[$episode] = [
                    'series'            =>  $this->originalName,
                    'season'            =>  $this->season,
                    'episode'           =>  $episode,
                    'year'              =>  $this->year,
                    'extension'         =>  $this->extension,
                ];
            }
            return $episodes;
        } else {
            return [
                'series'            =>  $this->originalName,
                'season'            =>  $this->season,
                'episode'           =>  $this->episode,
                'year'              =>  $this->year,
                'extension'         =>  $this->extension,
            ];
        }
    }

}

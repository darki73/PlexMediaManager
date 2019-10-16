<?php namespace App\Classes\Integrations;

use App\Models\Episode;
use App\Models\Series;
use Illuminate\Support\Arr;

/**
 * Class Message
 * @package App\Classes\Integrations
 */
class Message {

    /**
     * Message Title
     * @var string|null
     */
    protected $title = null;

    /**
     * Currently selected action
     * @var string|null
     */
    protected $message = null;

    /**
     * Message creation timestamp
     * @var string|null
     */
    protected $timestamp = null;

    /**
     * Informer Type String
     * @var string|null
     */
    protected $informer = null;

    /**
     * Color used for the card (only used in some integrations)
     * @var integer|null
     */
    protected $color = null;

    /**
     * Description for Series
     * @var string|null
     */
    protected $description = null;

    /**
     * Homepage for Series
     * @var string|null
     */
    protected $url = null;

    /**
     * Series Poster URL
     * @var string|null
     */
    protected $thumbnail = null;

    /**
     * Message constructor.
     */
    public function __construct() {
        $this->timestamp = sprintf('%sZ', \Carbon\Carbon::now()->toDateTimeLocalString());
    }

    /**
     * Get message title
     * @return string|null
     */
    public function getTitle() : ?string {
        return $this->title;
    }

    /**
     * Get message body
     * @return string|null
     */
    public function getMessage() : ?string {
        return $this->message;
    }

    /**
     * Get message creation timestamp
     * @return string|null
     */
    public function getTimestamp() : ?string {
        return $this->timestamp;
    }

    /**
     * Get informer type
     * @return string|null
     */
    public function getInformer() : ?string {
        return $this->informer;
    }

    /**
     * Get color for message widget
     * @return int|null
     */
    public function getColor() : ?int {
        return $this->color;
    }

    /**
     * Get series description
     * @return string|null
     */
    public function getDescription() : ?string {
        return $this->description;
    }

    /**
     * Get series homepage url
     * @return string|null
     */
    public function getUrl() : ?string {
        return $this->url;
    }

    /**
     * Get series thumbnail path
     * @return string|null
     */
    public function getThumbnail() : ?string {
        return $this->thumbnail;
    }

    /**
     * Get array representation of the message
     * @return array
     */
    public function toArray() : array {
        return [
            'title'         =>  $this->title,
            'description'   =>  $this->description,
            'message'       =>  $this->message,
            'informer'      =>  $this->informer,
            'color'         =>  $this->color,
            'url'           =>  $this->url,
            'thumbnail'     =>  $this->thumbnail,
            'timestamp'     =>  $this->timestamp
        ];
    }

    /**
     * This message will notify user that the download procedure for series has started
     * @param Series $series
     * @param string|null $message
     * @return static
     */
    public static function seriesDownloadStart(Series $series, ?string $message = null) : self {
        $self = new static;
        $self->title = sprintf('%s (%d)', $series->title, Message::getSeriesReleaseDate($series->release_date));
        if ($message !== null) {
            $self->message = $message;
        } else {
            $self->message = sprintf(
                '%s `%s`',
                Message::getActionMessage(__FUNCTION__),
                $self->title
            );
        }
        $self->informer = NotificationsManager::SERIES_INFORMER;
        $self->color = 57391;
        $self->description = $series->overview;
        $self->url = $series->homepage;
        $self->thumbnail = sprintf(
            'https://%s/storage/images/series/%d/global/w185/%s',
            env('APP_URL'),
            $series->id,
            $series->poster
        );
        return $self;
    }

    /**
     * This message will be sent when the download of series is finished
     * @param Series $series
     * @return static
     */
    public static function seriesDownloadFinished(Series $series) : self {
        $message = sprintf(
            '%s `%s`',
            Message::getActionMessage(__FUNCTION__),
            sprintf('%s (%d)', $series->title, Message::getSeriesReleaseDate($series->release_date))
        );
        return Message::seriesDownloadStart($series, $message);
    }


    /**
     * This message will notify user that we are downloading new episode for series
     * @param Series $series
     * @param Episode $episode
     * @return static
     */
    public static function seriesEpisodeDownloadStart(Series $series, Episode $episode) : self {
        $self = new static;
        $self->message = sprintf(Message::getActionMessage(__FUNCTION__), $episode->episode_number, $episode->season_number, $series->title, Message::getSeriesReleaseDate($series->release_date));
        $self->informer = NotificationsManager::SERIES_INFORMER;
        return $self;
    }

    /**
     * This message will notify user that we are downloading multiple series from the torrent tracker
     * @param Series $series
     * @param string $tracker
     * @param int $count
     * @return static
     */
    public static function seriesEpisodesDownloadStart(Series $series, string $tracker, int $count) : self {
        $self = new static;
        $self->message = sprintf(Message::getActionMessage(__FUNCTION__), $count, $series->title, Message::getSeriesReleaseDate($series->release_date), ucfirst($tracker));
        $self->informer = NotificationsManager::SERIES_INFORMER;
        return $self;
    }

    /**
     * Get message action message
     * @param string $method
     * @return string
     */
    protected static function getActionMessage(string $method) : string {
        $methods = [
            'seriesDownloadStart'           =>  'Started download procedure for',
            'seriesDownloadFinished'        =>  'Finished downloading',
            'seriesEpisodeDownloadStart'    =>  'Downloading **Episode %d** for **Season %d** of `%s (%d)`',
            'seriesEpisodesDownloadStart'   =>  'Downloading **%d** episodes for `%s (%d)` from %s'
        ];
        return $methods[$method];
    }

    /**
     * Get series release date
     * @param string $date
     * @return int
     */
    protected static function getSeriesReleaseDate(string $date) : int {
        $parts = explode('-', $date);
        return (integer) Arr::first($parts);
    }

}

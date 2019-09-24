<?php namespace App\Classes\LogReader\Entities;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Classes\LogReader\Contracts\LogParser;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Class LogEntry
 * @package App\Classes\LogReader\Entities
 */
class LogEntry {

    /**
     * Unique ID of the log entry
     * @var null|string
     */
    public $id = null;

    /**
     * Date of the log entry
     * @var null|Carbon
     */
    public $date = null;

    /**
     * Environment of the log entry
     * @var null|string
     */
    public $environment = null;

    /**
     * Log level of the log entry
     * @var null|string
     */
    public $level = null;

    /**
     * File path of the log entry
     * @var null|string
     */
    public $file_path = null;

    /**
     * Context of the log entry
     * @var null|LogContext
     */
    public $context = null;

    /**
     * Stack trace entries of the log entry
     * @var null|Collection
     */
    public $stack_traces = null;

    /**
     * LogParser instance
     * @var null|LogParser
     */
    protected $parser = null;

    /**
     * Cache Repository instance
     * @var null|Cache
     */
    protected $cache = null;

    /**
     * Original attributes of the log entry
     * @var array
     */
    protected $attributes = [];

    /**
     * LogEntry constructor.
     * @param LogParser $parser
     * @param Cache $cache
     * @param array $attributes
     */
    public function __construct(LogParser $parser, Cache $cache, array $attributes = []) {
        $this->parser = $parser;
        $this->cache = $cache;

        $this->setAttributes($attributes);
        $this->assignAttributes();
    }

    /**
     * Magic accessor
     * @param string $property
     * @return mixed
     */
    public function __get(string $property) {
        return $this->getAttribute($property);
    }

    /**
     * Get attribute from log entry
     * @param string $key
     * @return mixed|null
     */
    public function getAttribute(string $key) {
        $key = $this->reformatForCompatibility($key);
        return (property_exists($this, $key)) ? $this->{$key} : null;
    }

    /**
     * Get original value of property from log entry
     * @param string $key
     * @return mixed|null
     */
    public function getOriginal(string $key) {
        $key = $this->reformatForCompatibility($key);
        return (
            array_key_exists(
                $key,
                $this->attributes
            )
        ) ? $this->attributes[$key] : null;
    }

    /**
     * Mark log entry as read so it wont be displayed in results anymore
     * @return mixed
     */
    public function markAsRead() {
        return $this->cache->rememberForever($this->makeCacheKey(), function() {
            return $this->getRawContent();
        });
    }

    /**
     * Alias for the markAsRead() method
     * @return mixed
     */
    public function markRead() {
        return $this->markAsRead();
    }

    /**
     * Check if specific log entry has been marked as read
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function isRead() : bool {
        return $this->cache->has($this->makeCacheKey());
    }

    /**
     * Remove current entry from the log
     * @return bool
     */
    public function delete() : bool {
        $rawContent = $this->getRawContent();
        $filePath = $this->attributes['file_path'];
        $logContent = str_replace($rawContent, '', file_get_contents($filePath));
        file_put_contents($filePath, $logContent);
        return true;
    }

    /**
     * Get raw content of the log entry
     * @return string
     */
    public function getRawContent() : string {
        return $this->attributes['header'] . ' ' . $this->attributes['body'];
    }

    /**
     * Get all stack traces
     * @return Collection|null
     */
    public function getStackTraces() : ?Collection {
        return $this->stack_traces;
    }

    /**
     * Get stack traces as array
     * @return array
     */
    public function getStackTracesAsArray() : array {
        $result = [];
        if ($this->stack_traces !== null) {
            /**
             * @var TraceEntry $stackTrace
             */
            foreach ($this->stack_traces->toArray() as $stackTrace) {
                $result[] = [
                    'caught_at'     =>  $stackTrace->caught_at,
                    'line'          =>  $stackTrace->line,
                    'in'            =>  $stackTrace->in
                ];
            }
        }
        return $result;
    }

    /**
     * Generate unique ID for the current log entry
     * @return string
     */
    protected function generateID() : string {
        return md5($this->getRawContent());
    }

    /**
     * Create cache key for the log based on the entry ID
     * @return string
     */
    protected function makeCacheKey() : string {
        return 'log_' . $this->generateID();
    }

    /**
     * Set the ID property of the log entry
     * @param string $id
     * @return LogEntry|static|self|$this
     */
    protected function setID(string $id) : self {
        $this->id = $id;
        return $this;
    }

    /**
     * Set the DATE property of the log entry
     * @param string|null $date
     * @return LogEntry|static|self|$this
     */
    protected function setDate(?string $date = null) : self {
        if ($date) {
            $this->date = Carbon::createFromFormat('Y-m-d H:i:s', $date);
        }
        return $this;
    }

    /**
     * Set the ENVIRONMENT property of the log entry
     * @param string|null $environment
     * @return LogEntry|static|self|$this
     */
    protected function setEnvironment(?string $environment = null) : self {
        if ($environment) {
            $this->environment = strtolower($environment);
        }
        return $this;
    }

    /**
     * Set the LEVEL property of the log entry
     * @param string|null $level
     * @return LogEntry|static|self|$this
     */
    protected function setLevel(?string $level = null) : self {
        if ($level) {
            $this->level = strtolower($level);
        }
        return $this;
    }

    /**
     * Set the FILE_PATH property of the log entry
     * @param string|null $filePath
     * @return LogEntry|static|self|$this
     */
    protected function setFilePath(?string $filePath = null) : self {
        if ($filePath) {
            $this->file_path = $filePath;
        }
        return $this;
    }

    /**
     * Set the CONTEXT property of the log entry
     * @param string|null $context
     * @return LogEntry|static|self|$this
     */
    protected function setContext(?string $context = null) : self {
        if ($context) {
            $this->context = new LogContext($this->parser, $context);
        }
        return $this;
    }

    /**
     * Set the STACK_TRACES property of the log entry
     * @param string|null $stackTraces
     * @return LogEntry|static|self|$this
     */
    protected function setStackTraces(?string $stackTraces = null) : self {
        $traces = $this->parser->parseLogStackTrace($stackTraces);
        $output = [];

        foreach ($traces as $trace) {
            $output[] = new TraceEntry($this->parser, $trace);
        }
        $this->stack_traces = new Collection($output);
        return $this;
    }

    /**
     * Set the ATTRIBUTES property of the log entry
     * @param array $attributes
     * @return LogEntry|static|self|$this
     */
    protected function setAttributes(array $attributes = []) : self {
        if (is_array($attributes)) {
            $this->attributes = $attributes;
        }
        return $this;
    }

    /**
     * Assign attributes to class variables
     * @return LogEntry|static|self|$this
     */
    protected function assignAttributes() : self {
        $body = $this->parser->parseLogBody($this->attributes['body']);
        $this->attributes['context'] = $body['context'];
        $this->attributes['stack_traces'] = $body['stack_traces'];

        $this
            ->setID($this->generateID())
            ->setDate($this->attributes['date'])
            ->setEnvironment($this->attributes['environment'])
            ->setLevel($this->attributes['level'])
            ->setFilePath($this->attributes['file_path'])
            ->setContext($this->attributes['context'])
            ->setStackTraces($this->attributes['stack_traces']);

        return $this;
    }

    /**
     * Convert the property strings
     * @param string $property
     * @return string
     */
    protected function reformatForCompatibility(string $property) : string {
        switch (true) {
            case ($property === 'header'):
                $property = 'context';
                break;
            case ($property === 'stack'):
                $property = 'stack_traces';
                break;
            case ($property === 'filePath'):
                $property = 'file_path';
                break;
            default:
                break;
        }
        return $property;
    }

}

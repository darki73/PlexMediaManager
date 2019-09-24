<?php namespace App\Clasess\LogReader;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Clasess\LogReader\Levelable;
use App\Classes\LogReader\Entities\LogEntry;
use \Psr\SimpleCache\InvalidArgumentException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Config\Repository as Config;
use App\Classes\LogReader\Contracts\LogParser as ParserInterface;
use App\Classes\LogReader\Exceptions\UnableToRetrieveLogFilesException;

/**
 * Class LogReader
 * @package App\Clasess\LogReader
 */
class LogReader {

    /**
     * Cache instance
     * @var null|Cache
     */
    protected $cache = null;

    /**
     * Config instance
     * @var null|Config
     */
    protected $config = null;

    /**
     * Request instance
     * @var null|Request
     */
    protected $request = null;

    /**
     * LogParser instance
     * @var null|LogParser
     */
    protected $parser = null;

    /**
     * Levelable instance
     * @var null|Levelable
     */
    protected $levelable = null;

    /**
     * Current environment
     * @var null|string
     */
    protected $environment = null;

    /**
     * Current log level
     * @var null|array
     */
    protected $level = null;

    /**
     * Path to directory with logs
     * @var string
     */
    protected $path = '';

    /**
     * Filename to be used to search log files for
     * @var string
     */
    protected $filename = '';

    /**
     * Current log file path
     * @var string
     */
    protected $currentLogPath = '';

    /**
     * Field by which we want to order the log entries
     * @var string
     */
    protected $orderByField = '';

    /**
     * Direction in which we want to display logs (ASC, DESC)
     * @var string
     */
    protected $orderByDirection = '';

    /**
     * Include logs which were already read
     * @var bool
     */
    protected $includeRead = false;

    /**
     * LogReader constructor.
     * @param Cache $cache
     * @param Config $config
     * @param Request $request
     */
    public function __construct(Cache $cache, Config $config, Request $request) {
        $this->cache = $cache;
        $this->config = $config;
        $this->request = $request;
        $this->levelable = new Levelable;
        $this->parser = new LogParser;

        $this
            ->setLogPath(
                $this->config->get(
                    'log-reader.path',
                    storage_path('logs')
                )
            )->setLogFileName(
                $this->config->get(
                    'log-reader.filename',
                    'laravel.log'
                )
            )->setEnvironment(
                $this->config->get(
                    'log-reader.environment',
                    env('APP_ENV')
                )
            )->setLevel(
                $this->config->get(
                    'log-reader.level',
                    null
                )
            )->setOrderByField(
                $this->config->get(
                    'log-reader.order_by_field',
                    ''
                )
            )->setOrderByDirection(
                $this->config->get(
                    'log-reader.order_by_direction',
                    ''
                )
            );
    }

    /**
     * Set log path
     * @param string $path
     * @return LogParser|static|self|$this
     */
    public function setLogPath(string $path) : self {
        $this->path = $path;
        return $this;
    }

    /**
     * Set log parser
     * @param LogParser $parser
     * @return LogParser|static|self|$this
     */
    public function setLogParser(LogParser $parser) : self {
        $this->parser = $parser;
        return $this;
    }

    /**
     * Get log parser instance
     * @return LogParser|null
     */
    public function getLogParser() : ?LogParser {
        return $this->parser;
    }

    /**
     * Get Levelable instance
     * @return \App\Clasess\LogReader\Levelable|null
     */
    public function getLevelable() : ?Levelable {
        return $this->levelable;
    }

    /**
     * Retrieves the orderByField property.
     * @return string
     */
    public function getOrderByField() : string {
        return $this->orderByField;
    }

    /**
     * Retrieves the orderByDirection property.
     * @return string
     */
    public function getOrderByDirection() : string {
        return $this->orderByDirection;
    }

    /**
     * Retrieves the environment property.
     * @return string|null
     */
    public function getEnvironment() : ?string {
        return $this->environment;
    }

    /**
     * Retrieves the level property.
     * @return array|null
     */
    public function getLevel() : ?array {
        return $this->level;
    }

    /**
     * Get the current log path
     * @return string|null
     */
    public function getCurrentLogPath() : ?string {
        return $this->currentLogPath;
    }

    /**
     * Retrieves the path to directory storing the log files.
     * @return string|null
     */
    public function getLogPath() : ?string {
        return $this->path;
    }

    /**
     * Retrieves the log filename property.
     * @return string
     */
    public function getLogFilename() : string {
        return $this->filename;
    }

    /**
     * Sets the environment to sort the log entries by.
     * @param  string  $environment
     * @return LogParser|static|self|$this
     */
    public function environment(string $environment) : self {
        $this->setEnvironment($environment);
        return $this;
    }

    /**
     * Sets the level to sort the log entries by.
     * @param  mixed  $level
     * @return LogParser|static|self|$this
     */
    public function level($level) : self {
        if (empty($level)) {
            $level = [];
        } elseif (is_string($level)) {
            $level = explode(',', str_replace(' ', '', $level));
        } else {
            $level = is_array($level) ? $level : func_get_args();
        }
        $this->setLevel($level);
        return $this;
    }

    /**
     * Sets the filename to get log entries.
     * @param  string  $filename
     * @return LogParser|static|self|$this
     */
    public function filename(string $filename) : self {
        $this->setLogFilename($filename);
        return $this;
    }

    /**
     * Includes read entries in the log results.
     * @return LogParser|static|self|$this
     */
    public function withRead() : self {
        $this->setIncludeRead(true);
        return $this;
    }

    /**
     * Alias of the withRead() method.
     * @return LogParser|static|self|$this
     */
    public function includeRead() : self {
        return $this->withRead();
    }

    /**
     * Sets the direction to return the log entries in.
     * @param  string  $field
     * @param  string  $direction
     * @return LogParser|static|self|$this
     */
    public function orderBy(string $field, string $direction = 'asc') : self {
        $this->setOrderByField($field);
        $this->setOrderByDirection($direction);
        return $this;
    }

    /**
     * Returns a Laravel collection of log entries.
     * @return Collection
     * @throws InvalidArgumentException
     * @throws UnableToRetrieveLogFilesException
     */
    public function get() : Collection {
        $entries = [];
        $files = $this->getLogFiles();
        if (! is_array($files)) {
            throw new UnableToRetrieveLogFilesException('Unable to retrieve files from path: ' . $this->getLogPath());
        }
        foreach ($files as $log) {
            $this->setCurrentLogPath($log['path']);
            $parsedLog = $this->parseLog($log['contents'], $this->getEnvironment(), $this->getLevel());
            foreach ($parsedLog as $entry) {
                $newEntry = new LogEntry($this->parser, $this->cache, $entry);
                if (!$this->includeRead && $newEntry->isRead()) {
                    continue;
                }
                if ($newEntry->context !== null) {
                    $entries[$newEntry->id] = $newEntry;
                }
            }
        }
        return $this->postCollectionModifiers(new Collection($entries));
    }

    /**
     * Returns total of log entries.
     * @return int
     * @throws InvalidArgumentException
     * @throws UnableToRetrieveLogFilesException
     */
    public function count() : int {
        return $this->get()->count();
    }

    /**
     * Finds a logged error by it's ID.
     * @param string $id
     * @return mixed|null
     * @throws InvalidArgumentException
     * @throws UnableToRetrieveLogFilesException
     */
    public function find(string $id = '') {
        return $this->get()->get($id);
    }

    /**
     * Marks all retrieved log entries as read and
     * returns the number of entries that have been marked.
     * @return int
     * @throws InvalidArgumentException
     * @throws UnableToRetrieveLogFilesException
     */
    public function markAsRead() : int {
        $entries = $this->get();
        $count = 0;
        foreach ($entries as $entry) {
            if ($entry->markAsRead()) {
                ++$count;
            }
        }
        return $count;
    }

    /**
     * Alias of the markAsRead() method.
     * @return int
     * @throws InvalidArgumentException
     * @throws UnableToRetrieveLogFilesException
     */
    public function markRead() : int {
        return $this->markAsRead();
    }

    /**
     * Deletes all retrieved log entries and returns
     * the number of entries that have been deleted.
     * @return int
     * @throws InvalidArgumentException
     * @throws UnableToRetrieveLogFilesException
     */
    public function delete() : int {
        $entries = $this->get();
        $count = 0;
        foreach ($entries as $entry) {
            if ($entry->delete()) {
                ++$count;
            }
        }
        return $count;
    }

    /**
     * Deletes all retrieved log entries and returns
     * the number of entries that have been deleted.
     * @return int
     */
    public function removeLogFile() : int {
        $files = $this->getLogFileList();
        $count = 0;
        foreach ($files as $file) {
            if (@unlink($file)) {
                ++$count;
            }
        }
        return $count;
    }

    /**
     * Paginates the returned log entries.
     * @param int $perPage
     * @param int|null $currentPage
     * @param array $options
     *
     * @return LengthAwarePaginator
     * @throws InvalidArgumentException
     * @throws UnableToRetrieveLogFilesException
     */
    public function paginate(int $perPage = 25, int $currentPage = null, array $options = []) {
        $currentPage = $this->getPageFromInput($currentPage, $options);
        $offset      = ($currentPage - 1) * $perPage;
        $total       = $this->count();
        $entries     = $this->get()->slice($offset, $perPage)->all();
        return new LengthAwarePaginator($entries, $total, $perPage, $currentPage, $options);
    }

    /**
     * Returns an array of log filenames.
     * @param  null|string  $filename
     * @return array
     */
    public function getLogFilenameList(?string $filename = null) : array {
        $data = [];
        if (empty($filename)) {
            $filename = '*.*';
        }
        $files = $this->getLogFileList($filename);
        if (is_array($files)) {
            foreach ($files as $file) {
                $basename = pathinfo($file, PATHINFO_BASENAME);
                $data[$basename] = $file;
            }
        }
        return $data;
    }

    /**
     * Set the current log path
     * @param string $path
     * @return LogParser|static|self|$this
     */
    protected function setCurrentLogPath(string $path) : self {
        $this->currentLogPath = $path;
        return $this;
    }

    /**
     * Set file name from which log entries will be retrieved
     * @param string|null $fileName
     * @return LogParser|static|self|$this
     */
    protected function setLogFileName(?string $fileName) : self {
        if (empty($fileName) || $fileName === null) {
            $this->filename = '*.*';
        } else {
            $this->filename = $fileName;
        }
        return $this;
    }

    /**
     * Set orderByField property
     * @param string $field
     * @return LogParser|static|self|$this
     */
    protected function setOrderByField(string $field) : self {
        $field = strtolower($field);

        $acceptedFields = [
            'id',
            'date',
            'level',
            'file_path',
            'environment'
        ];

        if (in_array($field, $acceptedFields)) {
            $this->orderByField = $field;
        }

        return $this;
    }

    /**
     * Set orderByDirection property
     * @param string $direction
     * @return LogParser|static|self|$this
     */
    protected function setOrderByDirection(string $direction) : self {
        $direction = strtolower($direction);
        if ($direction == 'desc' || $direction == 'asc') {
            $this->orderByDirection = $direction;
        }
        return $this;
    }

    /**
     * Set environment property
     * @param string|null $environment
     * @return LogParser|static|self|$this
     */
    protected function setEnvironment(?string $environment) : self {
        $this->environment = $environment;
        return $this;
    }

    /**
     * Set level property
     * @param array|null $level
     * @return LogParser|static|self|$this
     */
    protected function setLevel(?array $level) : self {
        if (is_array($level)) {
            $this->level = $level;
        }
        return $this;
    }

    /**
     * Set includeRead property
     * @param bool $include
     * @return LogParser|static|self|$this
     */
    protected function setIncludeRead(bool $include = false) : self {
        $this->includeRead = $include;
        return $this;
    }

    /**
     * Modifies collection with relation to provided modifiers
     * @param Collection $collection
     * @return Collection
     */
    protected function postCollectionModifiers(Collection $collection) : Collection {
        if ($this->getOrderByField() && $this->getOrderByDirection()) {
            $field = $this->getOrderByField();
            $desc  = false;
            if ($this->getOrderByDirection() === 'desc') {
                $desc = true;
            }
            $sorted = $collection->sortBy(function ($entry) use ($field) {
                if (property_exists($entry, $field)) {
                    return $entry->{$field};
                }
            }, SORT_NATURAL, $desc);
            return $sorted;
        }
        return $collection;
    }

    /**
     * Get specified page from the input. Used for pagination
     * @param int|null $currentPage
     * @param array $options
     * @return int
     */
    protected function getPageFromInput(?int $currentPage = null, array $options = []) : int {
        if (is_numeric($currentPage)) {
            return intval($currentPage);
        }
        $pageName = (array_key_exists('pageName', $options)) ? $options['pageName'] : 'page';
        $page = $this->request->input($pageName);
        if (is_numeric($page)) {
            return intval($page);
        }
        return 1;
    }

    /**
     * Parse log file
     * @param string $content
     * @param string|null $allowedEnvironment
     * @param array|null $allowedLevel
     *
     * @return array
     */
    protected function parseLog(string $content, ?string $allowedEnvironment = null, ?array $allowedLevel = []) : array {
        $log = [];
        $parsed = $this->parser->parseLogContent($content);

        $parsed_headerSet = $parsed['headerSet'];
        $parsed_dateSet = $parsed['dateSet'];
        $parsed_envSet = $parsed['envSet'];
        $parsed_levelSet = $parsed['levelSet'];
        $parsed_bodySet = $parsed['bodySet'];

        if (empty($parsed_headerSet)) {
            return $log;
        }
        $needReFormat = in_array('Next', $parsed_headerSet);
        $newContent   = null;
        foreach ($parsed_headerSet as $key => $header) {
            if (empty($parsed_dateSet[$key])) {
                $parsed_dateSet[$key]  = $parsed_dateSet[$key-1];
                $parsed_envSet[$key]   = $parsed_envSet[$key-1];
                $parsed_levelSet[$key] = $parsed_levelSet[$key-1];
                $header                = str_replace("Next", $parsed_headerSet[$key-1], $header);
            }
            $newContent .= $header.' '.$parsed_bodySet[$key];
            if ((empty($allowedEnvironment) || $allowedEnvironment == $parsed_envSet[$key]) && $this->levelable->filter($parsed_levelSet[$key], $allowedLevel)) {
                $parsed_bodySet = array_values(array_filter($parsed_bodySet));
                $log[] = [
                    'environment' => $parsed_envSet[$key],
                    'level'       => $parsed_levelSet[$key],
                    'date'        => $parsed_dateSet[$key],
                    'file_path'   => $this->getCurrentLogPath(),
                    'header'      => $header,
                    'body'        => $parsed_bodySet[$key]
                ];
            }
        }
        if ($needReFormat) {
            file_put_contents($this->getCurrentLogPath(), $newContent);
        }
        return $log;
    }

    /**
     * Retrieves all the data inside each log file from the log file list.
     * @return array|bool
     */
    protected function getLogFiles() {
        $data = [];
        $files = $this->getLogFileList();
        if (is_array($files)) {
            $count = 0;
            foreach ($files as $file) {
                $data[$count]['contents'] = file_get_contents($file);
                $data[$count]['path'] = $file;
                $count++;
            }
            return $data;
        }
        return false;
    }

    /**
     * Returns an array of log file paths.
     * @param  null|string  $forceName
     * @return bool|array
     */
    protected function getLogFileList(?string $forceName = null) {
        $path = $this->getLogPath();
        if (is_dir($path)) {
            /*
             * Matches files in the log directory with the special name'
             */
            $logPath = sprintf('%s%s%s', $path, DIRECTORY_SEPARATOR, $this->getLogFilename());
            /*
             * Force matches all files in the log directory'
             */
            if (!is_null($forceName)) {
                $logPath = sprintf('%s%s%s', $path, DIRECTORY_SEPARATOR, $forceName);
            }
            return glob($logPath, GLOB_BRACE);
        }
        return false;
    }

}

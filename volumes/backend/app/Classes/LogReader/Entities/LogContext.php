<?php namespace App\Classes\LogReader\Entities;

use App\Classes\LogReader\Contracts\LogParser;

/**
 * Class LogContext
 * @package App\Classes\LogReader\Entities
 */
class LogContext {

    /**
     * Message of the log context
     * @var null|string
     */
    public $message = null;

    /**
     * Exception of the log context
     * @var null|string
     */
    public $exception = null;

    /**
     * Location of the exception in log context
     * @var null|string
     */
    public $in = null;

    /**
     * Line of the exception in log context
     * @var null|integer
     */
    public $line = null;

    /**
     * LogParser instance
     * @var null|LogParser
     */
    protected $parser = null;

    /**
     * Original log context
     * @var null|string
     */
    protected $content = null;

    /**
     * LogContext constructor.
     * @param LogParser $parser
     * @param string $content
     */
    public function __construct(LogParser $parser, string $content) {
        $this->parser = $parser;
        $this->content = $content;
        $this->assignAttributes();
    }

    /**
     * Return string representation of log content
     * @return string
     */
    public function __toString() : string {
        return $this->content;
    }

    /**
     * Convert class to array
     * @return array
     */
    public function toArray() : array {
        return [
            'message'   =>  $this->message,
            'exception' =>  $this->exception,
            'file'      =>  $this->in,
            'line'      =>  $this->line
        ];
    }

    /**
     * Parse log context and assign resulting variables and
     * values to local
     * @return void
     */
    protected function assignAttributes() : void {
        $parsed = $this->parser->parseLogContext($this->content);
        foreach ($parsed as $key => $value) {
            $this->{$key} = $value;
        }
    }

}

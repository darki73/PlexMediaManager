<?php namespace App\Classes\LogReader\Entities;

use App\Classes\LogReader\Contracts\LogParser;

/**
 * Class TraceEntry
 * @package App\Classes\LogReader\Entities
 */
class TraceEntry {

    /**
     * When trace was recorded
     * @var null|string
     */
    public $caught_at = null;

    /**
     * Location of the exception in trace content
     * @var null|string
     */
    public $in = null;

    /**
     * Line of the exception in trace content
     * @var null|integer
     */
    public $line = null;

    /**
     * LogParser instance
     * @var null|LogParser
     */
    protected $parser = null;

    /**
     * Original trace content
     * @var null|string
     */
    protected $content = null;

    /**
     * TraceEntry constructor.
     * @param LogParser $parser
     * @param string $content
     */
    public function __construct(LogParser $parser, string $content) {
        $this->parser = $parser;
        $this->content = $content;

        $this->assignAttributes();
    }

    /**
     * Return string representation of trace content
     * @return string
     */
    public function __toString() : string {
        return $this->content;
    }

    /**
     * Parse trace content and assign resulting variables and
     * values to local
     * @return void
     */
    protected function assignAttributes() : void {
        $parsed = $this->parser->parseStackTraceEntry($this->content);
        foreach ($parsed as $key => $value) {
            $this->{$key} = $value;
        }
    }

}

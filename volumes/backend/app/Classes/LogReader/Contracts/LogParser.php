<?php namespace App\Classes\LogReader\Contracts;

/**
 * Interface LogParser
 * @package App\Classes\LogReader\Contracts
 */
interface LogParser {

    /**
     * Parses the content of the log file into an array containing all the necessary information
     * @param string $content
     * @return array
     */
    public function parseLogContent(string $content) : array;

    /**
     * Parses the body of the log file into an array containing all the necessary information
     * @param string $body
     * @return array
     */
    public function parseLogBody(string $body) : array;

    /**
     * Parses the context of the log file into an array containing all the necessary information
     * @param string $context
     * @return array
     */
    public function parseLogContext(string $context) : array;

    /**
     * Parses the stack trace part of the log file into an array containing all the necessary information
     * @param string|null $stackTrace
     * @return array
     */
    public function parseLogStackTrace(?string $stackTrace) : array;

    /**
     * Parses the content of the stack trace entry into an array containing all the necessary information
     * @param string $stackTraceEntry
     * @return array
     */
    public function parseStackTraceEntry(string $stackTraceEntry) : array;

}

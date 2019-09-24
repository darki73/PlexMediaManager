<?php namespace App\Clasess\LogReader;

use App\Classes\LogReader\Contracts\LogParser as LogParserInterface;
use Illuminate\Support\Str;

/**
 * Class LogParser
 * @package App\Clasess\LogReader
 */
class LogParser implements LogParserInterface {

    /**
     *
     * @var string
     */
    const LOG_DATE_PATTERN            = "\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]";

    /**
     *
     * @var string
     */
    const LOG_ENVIRONMENT_PATTERN     = "(\w+)";

    /**
     *
     * @var string
     */
    const LOG_LEVEL_PATTERN           = "([A-Z]+)";

    /**
     *
     * @var string
     */
    const CONTEXT_EXCEPTION_PATTERN   = "exception\s\'{1}([^\']+)\'{1}";

    /**
     *
     * @var string
     */
    const CONTEXT_MESSAGE_PATTERN     = "(\swith\smessage\s\'{1}(.*)\'{1})?";

    /**
     *
     * @var string
     */
    const CONTEXT_IN_PATTERN          = "\sin\s(.*)\:(\d+)";

    /**
     *
     * @var string
     */
    const STACK_TRACE_DIVIDER_PATTERN = "(\[stacktrace\]|Stack trace\:)";

    /**
     *
     * @var string
     */
    const STACK_TRACE_INDEX_PATTERN   = "\#\d+\s";

    /**
     *
     * @var string
     */
    const TRACE_IN_DIVIDER_PATTERN    = "\:\s";

    /**
     *
     * @var string
     */
    const TRACE_FILE_PATTERN          = "(.*)\((\d+)\)";

    /**
     * @inheritDoc
     * @param string $content
     * @return array
     */
    public function parseLogContent(string $content): array {
        $headerSet = $dateSet = $envSet = $levelSet = $bodySet = [];
        $pattern = "/^" . self::LOG_DATE_PATTERN . "\s" . self::LOG_ENVIRONMENT_PATTERN . "\." . self::LOG_LEVEL_PATTERN . "\:|Next/m";
        preg_match_all($pattern, $content, $matches);

        if (is_array($matches)) {
            $bodySet = array_map('ltrim', preg_split($pattern, $content));

            if (empty($bodySet['0']) && \count($bodySet) > \count($matches)) {
                array_shift($bodySet);
            }

            $headerSet = $matches[0];
            $dateSet   = $matches[1];
            $envSet    = $matches[2];
            $levelSet  = $matches[3];
            $bodySet   = $bodySet;
        }

        return compact('headerSet', 'dateSet', 'envSet', 'levelSet', 'bodySet');
    }

    /**
     * @inheritDoc
     * @param string $body
     * @return array
     */
    public function parseLogBody(string $body): array {
        $pattern      = "/^" . self::STACK_TRACE_DIVIDER_PATTERN . "/m";
        $parts        = array_map('ltrim', preg_split($pattern, $body));
        $context      = $parts[0];
        $stack_traces = isset($parts[1]) ? $parts[1] : null;
        return compact('context', 'stack_traces');
    }

    /**
     * @inheritDoc
     * @param string $context
     * @return array
     */
    public function parseLogContext(string $context): array {
        $content = trim($context);
//        $pattern = "/^" . self::CONTEXT_EXCEPTION_PATTERN . self::CONTEXT_MESSAGE_PATTERN . self::CONTEXT_IN_PATTERN . "$/ms";
//        preg_match($pattern, $content, $matches);
//        $exception = isset($matches[1]) ? $matches[1] : null;
//        $message   = isset($matches[2]) ? $matches[3] : $content;
//        $in        = isset($matches[4]) ? $matches[4] : null;
//        $line      = isset($matches[5]) ? $matches[5] : null;


        $message = trim($this->extractMessage(Str::before($content, '{"exception"')));
        [$in, $line] = $this->extractPosition(Str::after($content, ' at '));
        $exception = $this->extractException($content);

        return compact('message', 'exception', 'in', 'line');
    }

    /**
     * Extract message
     * @param string $message
     * @return string|null
     */
    protected function extractMessage(string $message) : ?string {
        if (false !== stripos($message, 'called in')) {
            $message = substr($message, 0, strpos($message, ", called in"));
        }
        $message = strlen($message) > 0 ? $message : null;
        return $message;
    }

    /**
     * Extract position
     * @param string $position
     * @return array
     */
    protected function extractPosition(string $position) : array {
        $position = trim(str_replace(['(', ')'], '', $position));
        if (false !== strpos($position, ':')) {
            $values = explode(':', $position);
            if (isset($values[1]) && strlen($values[1]) > 0) {
                return $values;
            }
        }
        return [null, null];
    }

    /**
     * Extract exception
     * @param string $content
     * @return string|null
     */
    protected function extractException(string $content) : ?string {
        if (false !== stripos($content, '{"exception":"[object] (')) {
            return Str::before(Str::after($content, '{"exception":"[object] ('), '(code:');
        }
        return null;
    }

    /**
     * @inheritDoc
     * @param string|null $stackTrace
     * @return array
     */
    public function parseLogStackTrace(?string $stackTrace): array {
        $content = trim($stackTrace);
        $pattern = "/^" . self::STACK_TRACE_INDEX_PATTERN . "/m";
        if (empty($content)) {
            return [];
        }
        $traces = preg_split($pattern, $content);
        if (empty($trace[0])) {
            array_shift($traces);
        }
        return $traces;
    }

    /**
     * @inheritDoc
     * @param string $stackTraceEntry
     * @return array
     */
    public function parseStackTraceEntry(string $stackTraceEntry): array {
        $content = trim($stackTraceEntry);
        $caught_at = $content;
        $in = $line = null;
        if (!empty($content) && preg_match("/.*" . self::TRACE_IN_DIVIDER_PATTERN . ".*/", $content)) {
            $split = array_map('trim', preg_split("/" . self::TRACE_IN_DIVIDER_PATTERN . "/", $content));
            $in   = trim($split[0]);
            $caught_at = (isset($split[1])) ? $split[1] : null;
            if (preg_match("/^" . self::TRACE_FILE_PATTERN . "$/", $in, $matches)) {
                $in   = trim($matches[1]);
                $line = $matches[2];
            }
        }
        return compact('caught_at', 'in', 'line');
    }

}

<?php

namespace ride\library\log\listener;

use ride\library\decorator\DebugLogDecorator;

use ride\library\log\listener\FileLogListener;
use ride\library\log\exception\LogException;
use ride\library\log\LogMessage;

/**
 * Log listener to write log messages to file
 */
class FilteredFileLogListener extends FileLogListener {

    protected $filteredSources = array();
    protected $filteredLevels = array();

    /**
     * Array with the level translated in human readable form
     * @var array
     */
    protected $levels = array(
        LogMessage::LEVEL_ERROR => 'E',
        LogMessage::LEVEL_WARNING => 'W',
        LogMessage::LEVEL_INFORMATION => 'I',
        LogMessage::LEVEL_DEBUG => 'D',
    );

    public function __construct($fileName) {
        parent::__construct($fileName);
    }

    public function addSources($sources) {
        if (!is_array($sources)) {
            $sources = array($sources);
        }

        foreach ($sources as $source) {
            $this->filteredSources[$source] = $source;
        }
    }

    public function removeSource($source) {
        unset($this->filteredSources[$source]);
    }

    public function addLevels($levels) {
        if (!is_array($levels)) {
            $levels = array($levels);
        }

        foreach ($levels as $level) {
            $this->filteredLevels[$level] = $level;
        }
    }

    public function removeLevel($level) {
        unset($this->filteredLevels[$level]);
    }

    /**
     * {@inheritdoc}
     */
    protected function log(LogMessage $message) {
        if (!in_array($message->getSource(), $this->filteredSources) || !in_array($this->levels[$message->getLevel()], $this->filteredLevels)) {
            return;
        }

        $output = $this->getLogMessageAsString($message);

        if ($this->writeFile($output)) {
            $this->truncateFile($output);
        }
    }

    /**
     * Append the output to the log file
     * @param string $output String to append to the file
     * @return boolean
     */
    private function writeFile($output) {
        if (!($f = @fopen($this->fileName, 'a'))) {
            return false;
        }

        fwrite($f, $output);
        fclose($f);

        return true;
    }

    /**
     * Truncate the log tile if the truncate size is set and the log file is
     * bigger then the truncate size
     * @param string $output String to write in the truncated file, empty by
     * default
     * @return null
     */
    private function truncateFile($output = '') {
        $truncateSize = $this->getFileTruncateSize();
        if (!$truncateSize) {
            return;
        }

        clearstatcache();

        $fileSize = filesize($this->fileName) / 1024; // we work with kb
        if ($fileSize < $truncateSize) {
            return;
        }

        if ($this->useBackupFile) {
            copy($this->fileName, $this->fileName . '.1');
        }

        if ($f = @fopen($this->fileName, 'w')) {
            fwrite($f, $output);
            fclose($f);
        }
    }

}

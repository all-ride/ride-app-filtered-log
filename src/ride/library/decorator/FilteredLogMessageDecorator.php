<?php

namespace ride\library\decorator;

use ride\library\log\LogMessage;

/**
 * Decorator for debug log messages into output format
 */
class FilteredLogMessageDecorator implements Decorator {

    /**
     * Decorator for the date value
     * @var \ride\library\decorator\Decorator
     */
    protected $dateDecorator;

    /**
     * Sets the decorator for the date value
     * @param \ride\library\decorator\Decorator $dateDecorator
     * @return null
     */
    public function setDateDecorator(Decorator $dateDecorator) {
        $this->dateDecorator = $dateDecorator;
    }

    /**
     * Gets the decorator for the date value
     * @return \ride\library\decorator\Decorator
     */
    public function getDateDecorator() {
        return $this->dateDecorator;
    }

    /**
     * Decorator for the memory value
     * @var \ride\library\decorator\Decorator
     */
    protected $memoryDecorator;

    /**
     * Sets the decorator for the memory value
     * @param \ride\library\decorator\Decorator $memoryDecorator
     * @return null
     */
    public function setMemoryDecorator(Decorator $memoryDecorator) {
        $this->memoryDecorator = $memoryDecorator;
    }

    /**
     * Gets the decorator for the memory value
     * @return \ride\library\decorator\Decorator
     */
    public function getMemoryDecorator() {
        return $this->memoryDecorator;
    }

    /**
     * Separator between the fields
     * @var string
     */
    protected $separator = ' ~ ';

    /**
     * Use colors
     * @var boolean
     */
    protected $useColors = true;

    /**
     * Colors
     */
    const COLOR_BLACK ='0;30';
    const COLOR_DARK_GRAY ='1;30';
    const COLOR_BLUE ='0;34';
    const COLOR_LIGHT_BLUE ='1;34';
    const COLOR_GREEN ='0;32';
    const COLOR_LIGHT_GREEN ='1;32';
    const COLOR_CYAN ='0;36';
    const COLOR_LIGHT_CYAN ='1;36';
    const COLOR_RED ='0;31';
    const COLOR_LIGHT_RED ='1;31';
    const COLOR_PURPLE ='0;35';
    const COLOR_LIGHT_PURPLE ='1;35';
    const COLOR_BROWN ='0;33';
    const COLOR_YELLOW ='1;33';
    const COLOR_LIGHT_GRAY ='0;37';
    const COLOR_WHITE ='1;37';
    const BACKGROUND_BLACK = '40';
    const BACKGROUND_RED = '41';
    const BACKGROUND_GREEN = '42';
    const BACKGROUND_YELLOW = '43';
    const BACKGROUND_BLUE = '44';
    const BACKGROUND_MAGENTA = '45';
    const BACKGROUND_CYAN = '46';
    const BACKGROUND_LIGHT_GRAY = '47';

    /**
     * Column names
     */
    const COLUMN_ID = 'id';
    const COLUMN_DATE = 'date';
    const COLUMN_DURATION = 'duration';
    const COLUMN_CLIENT = 'client';
    const COLUMN_SOURCE = 'source';
    const COLUMN_MEMORY = 'memory';
    const COLUMN_LEVEL = 'level';
    const COLUMN_TITLE = 'title';
    const COLUMN_DESCRIPTION = 'description';

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

    /**
     * Array with the level translated in color form
     * @var array
     */
    protected $levelColors = array(
        LogMessage::LEVEL_ERROR => self::COLOR_RED,
        LogMessage::LEVEL_WARNING => self::COLOR_PURPLE,
        LogMessage::LEVEL_INFORMATION => self::COLOR_GREEN,
        LogMessage::LEVEL_DEBUG => null,
    );

    /**
     * Add fields to show
     * @param mixed $fields
     */
    public function addFields($fields) {
        if (!is_array($fields)) {
            $fields = array($fields);
        }

        foreach($fields as $field) {
            $this->fields[$field] = $field;
        }
    }

    /**
     * Remove a field from the filters
     * @param  string $field
     */
    public function removeField($field) {
        unset($this->fields[$field]);
    }

    /**
     * Get a field
     * @param  string $field
     * @return string
     */
    public function hasField($field) {
        return $this->fields[$field];
    }

    /**
     * Set the separator
     * @param string $separator
     */
    public function setSeparator($separator) {
        $this->separator = ' ' . trim($separator) . ' ';
    }

    /**
     * Set the use of colors
     * @param  boolean $useColors
     */
    public function useColors($useColors) {
        $this->useColors = $useColors;
    }

    /**
     * Decorate a value for another context
     * @param mixed $value Value to decorate
     * @return mixed Decorated value if applicable, provided value otherwise
     */
    public function decorate($value) {
        if (!$value instanceof LogMessage) {
            return $value;
        }

        $output = array();
        foreach ($this->fields as $key=>$field) {
            if ($field = $this->getParsedValue($field, $value)) {
                if ($this->useColors) {
                    $field = $this->color($field, $this->levelColors[$value->getLevel()]);
                }

                $output[$key] = $field;
            }
        }

        $separator = $this->separator;
        if ($this->useColors) {
            $separator = $this->color($separator, self::COLOR_CYAN);
        }
        $output = implode($separator, $output);

        return $output . "\n";
    }

    /**
     * Get a parsed value
     * @param  string $key
     * @param  value $value
     * @return string
     */
    protected function getParsedValue($key, $value) {
        switch ($key) {
            case self::COLUMN_ID:
                return $value->getId();

            case self::COLUMN_DATE:
                $date = $value->getDate();
                if ($this->dateDecorator) {
                    $date = $this->dateDecorator->decorate($date);
                }
                return $date;

            case self::COLUMN_MEMORY:
                $memory = memory_get_usage();
                if ($this->memoryDecorator) {
                    $memory = $this->memoryDecorator->decorate($memory);
                }
                return $memory;

            case self::COLUMN_DURATION:
                return substr($value->getMicroTime(), 0, 5);

            case self::COLUMN_CLIENT:
                return $value->getClient();

            case self::COLUMN_SOURCE:
                return $value->getSource();

            case self::COLUMN_LEVEL:
                return $this->levels[$value->getLevel()];

            case self::COLUMN_TITLE:
                return $value->getTitle();

            case self::COLUMN_DESCRIPTION:
                $description = $value->getDescription();
                if (!empty($description)) {
                    return $description;
                }
            default:
                return null;
        }
    }

    /**
     * Color a string
     * @param  string $string
     * @param  string $foreground_color
     * @param  string $background_color
     * @return string
     */
    protected function color($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        if ($foreground_color) {
            $colored_string .= "\033[" . $foreground_color . "m";
        }
        if ($background_color) {
            $colored_string .= "\033[" . $background_color . "m";
        }
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

}

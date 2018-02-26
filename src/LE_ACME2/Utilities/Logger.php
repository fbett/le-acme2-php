<?php

namespace LE_ACME2\Utilities;

class Logger {

    const LEVEL_DISABLED = 0;
    const LEVEL_INFO = 1;
    const LEVEL_DEBUG = 2;

    private static $_instance = NULL;

    public static function getInstance() {

        if(self::$_instance === NULL) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected $_desiredLevel = self::LEVEL_DISABLED;

    public function setDesiredLevel($desiredLevel) {

        $this->_desiredLevel = $desiredLevel;
    }

    /**
     * @param int $level
     * @param string $message
     * @param string|array|object $data
     */
    public function add($level = self::LEVEL_DEBUG, $message, $data = array()) {

        if($level > $this->_desiredLevel)
            return;

        $e = new \Exception();
        $trace = $e->getTrace();
        unset($trace[0]);

        echo '<b>' . date('d-m-Y H:i:s') . ': ' . $message . '</b><br>';

        if($this->_desiredLevel == self::LEVEL_DEBUG) {

            $step = 0;
            foreach ($trace as $traceItem) {
                echo 'Trace #' . $step . ': ' . $traceItem['class'] . '::' . $traceItem['function'] . ':<br/>';
                $step++;
            }

            if ((is_array($data) && count($data) > 0) || !is_array($data))
                echo '<br/>Data:<br/><pre>' . var_export($data, true) . '</pre>';

            echo '<br><br>';
        }
    }

}
<?php

namespace LE_ACME2\Utilities;

use LE_ACME2\SingletonTrait;

class Logger {

    use SingletonTrait;

    const LEVEL_DISABLED = 0;
    const LEVEL_INFO = 1;
    const LEVEL_DEBUG = 2;

    private function __construct() {}

    protected $_desiredLevel = self::LEVEL_DISABLED;

    public function setDesiredLevel(int $desiredLevel) {
        $this->_desiredLevel = $desiredLevel;
    }

    /** @var \Psr\Log\LoggerInterface|null $_psrLogger */
    private $_psrLogger = null;

    /**
     * @param \Psr\Log\LoggerInterface|null $psrLogger
     */
    public function setPSRLogger($psrLogger) {
        $this->_psrLogger = $psrLogger;
    }

    /**
     * @param int $level
     * @param string $message
     * @param array $data
     */
    public function add(int $level, string $message, array $data = array()) {

        if($level > $this->_desiredLevel)
            return;

        if($this->_psrLogger) {

            if($level == self::LEVEL_INFO) {
                $this->_psrLogger->info($message, $data);
                return;
            }
            if($level == self::LEVEL_DEBUG) {
                $this->_psrLogger->debug($message, $data);
                return;
            }
            throw new \RuntimeException('Missing PSR Logger support for level: ' . $level);
        }

        $e = new \Exception();
        $trace = $e->getTrace();
        unset($trace[0]);

        $output = '<b>' . date('d-m-Y H:i:s') . ': ' . $message . '</b><br>' . "\n";

        if($this->_desiredLevel == self::LEVEL_DEBUG) {

            $step = 0;
            foreach ($trace as $traceItem) {

                if(!isset($traceItem['class']) || !isset($traceItem['function'])) {
                    continue;
                }

                $output .= 'Trace #' . $step . ': ' . $traceItem['class'] . '::' . $traceItem['function'] . '<br/>' . "\n";
                $step++;
            }

            if ((is_array($data) && count($data) > 0) || !is_array($data))
                $output .= "\n" .'<br/>Data:<br/>' . "\n" . '<pre>' . var_export($data, true) . '</pre>';

            $output .= '<br><br>' . "\n\n";
        }

        if(PHP_SAPI == 'cli') {

            $output = strip_tags($output);
        }
        echo $output;
    }
}
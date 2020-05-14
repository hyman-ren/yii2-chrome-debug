<?php

namespace hyman\debug;

use yii\log\logger;

/**
 * Class ChromeLogger
 * @package hyman\debug
 */
class ChromeLogger extends Logger
{
    //时间计算使用
    protected static $timer = 0;

    //sql计数器
    protected static $sqlCoounter = 0;

    protected $groupMethod = 'groupCollapsed';

    protected $debugLevel = 3;

    protected $sqlWarningTime = 80; //执行稍长sql执行时间

    protected $sqlNeedOptimizeTime = 500; //需要优化sql执行时间

    /**
     * @param $groupMethod
     * @author    hyman    hyman@an2.net
     */
    public function setGroupMethod($groupMethod){
        $this->groupMethod = $groupMethod;
    }

    /**
     * @author    hyman    hyman@an2.net
     */
    public static function getSqlCount(){
        return self::$sqlCoounter;
    }

    /**
     * 
     * @author    hyman    hyman@an2.net
     */
    public function setDebugLevel($level){
        $this->debugLevel = $level;
    }

    /**
     * 
     * @author    hyman    hyman@an2.net
     */
    public function setSqlWarningTime($warnIngTime, $needOptimizeTime){
        $this->sqlWarningTime = $warnIngTime;
        $this->sqlNeedOptimizeTime = $needOptimizeTime;
    }

    /**
     *
     * @param $message
     * @param $level
     * @param string $category
     * @return mixed
     * @author    hyman    hyman@an2.net
     */
    public function log($message, $level, $category = 'application')
    {
        if ($level == self::LEVEL_PROFILE_BEGIN) {
            self::$timer = microtime(true) * 1000;
        }

        $rel = [
            self::LEVEL_ERROR       => 'error',
            self::LEVEL_WARNING     => 'warn',
            self::LEVEL_INFO        => 'info',
            self::LEVEL_TRACE       => 'log',
            self::LEVEL_PROFILE_END => 'log',
        ];
        $func = $rel[$level] ?? '';
        if (empty($func)) {
            return parent::log($message, $level, $category);
        }
        $ext = '';
        $per = '';
        if ($level == self::LEVEL_PROFILE_END) {
            if (in_array($category, ['yii\db\Command::query', 'yii\db\Command::execute'])) {
                self::$sqlCoounter++;
                $per = '第' . self::$sqlCoounter . '条SQL:';
            }
            $costTime = (microtime(true) * 1000 - self::$timer);
            $ext = sprintf(" 耗时%.4fms", $costTime);

            if($costTime > $this->sqlWarningTime){
                $func = 'warn';
            }

            if($costTime > $this->sqlNeedOptimizeTime){
                $func = 'error';
            }
        }
        $message = is_string($message) ? $message : json_encode($message);
        $message = $per . $message . $ext;
        $traces  = [];
        $count   = 0;
        $ts      = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_pop($ts); // remove the last trace since it would be the entry script, not very useful
        foreach ($ts as $trace) {
            if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII2_PATH) !== 0) {
                unset($trace['object'], $trace['args']);
                $traces[] = $trace['file'] . ' line:' . $trace['line'];
                if (++$count >= $this->debugLevel) {
                    break;
                }
            }
        }

        $message .= "\n" . implode("\n", $traces);
        if(substr($message,0,14) == 'Running action'){
            ChromePhp::groupEnd();
            $method = $this->groupMethod;
            ChromePhp::$method('page');
        }
        ChromePhp::$func($message);
        return parent::log($message, $level, $category);
    }

}

<?php


namespace hyman\debug;

use Yii;
use yii\log\logger;

/**
 * Class ChromeLogger
 * @package hyman\debug
 */
class ChromeLogger extends Logger
{
    //时间计算使用
    public static $timer = 0;

    //sql计数器
    public static $sqlCoounter = 0;

    /**
     *
     * @param $message
     * @param $level
     * @param string $category
     * @return mixed
     * @author    hyman    hyman@an2.net
     */
    public function log($message, $level, $category = 'application'){
        if($level == self::LEVEL_PROFILE_BEGIN){
            self::$timer = microtime(true) * 1000;
        }

        $rel = [
            self::LEVEL_ERROR => 'error',
            self::LEVEL_WARNING => 'warn',
            self::LEVEL_INFO => 'info',
            self::LEVEL_TRACE => 'log',
            self::LEVEL_PROFILE_END => 'log',
        ];
        $func = $rel[$level] ?? '';
        if(empty($func)){
            return parent::log($message, $level, $category);
        }
        $ext = '';
        $per = '';
        if($level == self::LEVEL_PROFILE_END){
            if(in_array($category, ['yii\db\Command::query', 'yii\db\Command::execute'])){
                self::$sqlCoounter ++;
                $per = '第' . self::$sqlCoounter . '条SQL:';
            }
            $ext = ' 耗时' . (microtime(true) * 1000 - self::$timer) . 'ms';
        }
        $message = is_string($message) ? $message : json_encode($message);
        $message = $per . $message .$ext;
        $traces = [];
        $count = 0;
        $ts = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_pop($ts); // remove the last trace since it would be the entry script, not very useful
        foreach ($ts as $trace) {
            if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII2_PATH) !== 0) {
                unset($trace['object'], $trace['args']);
                $traces[] = $trace['file'] . ' line:' .$trace['line'];
                if (++$count >= 3) {
                    break;
                }
            }
        }

        $message .= "\n" .implode("\n",$traces);
        ChromePhp::$func($message);
        return parent::log($message, $level, $category);
    }

}

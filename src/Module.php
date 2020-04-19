<?php

namespace hyman\debug;

use Yii;
use yii\base\BootstrapInterface;
use yii\web\Application;
use yii\web\Controller;
use yii\web\View;

/**
 * Class Module
 * @package hyman\debug
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    protected static $time;

    public $encryptType = 'base64';

    public $aesKey = '';

    public $aesIv = '';

    public $autoFolding = false;

    public $debugLevel = 3;

    /**
     *
     * @param $app
     * @author    hyman    hyman@an2.net
     */
    public function bootstrap($app)
    {
        $groupMethod = $this->autoFolding ? 'groupCollapsed' : 'group';
        if('aes' == $this->encryptType){
            ChromePhp::setEncryptConfig($this->aesKey,$this->aesIv);
        }
        $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app, $groupMethod) {
            self::$time = microtime(true) * 1000;
            $logger     = new ChromeLogger();
            $logger->setGroupMethod($groupMethod);
            $logger->setDebugLevel($this->debugLevel);
            $targets    = Yii::$app->getLog()->setLogger($logger);
            Yii::setLogger($logger);
            ChromePhp::groupCollapsed('being');
        });

        $app->on(Application::EVENT_BEFORE_ACTION, function () use ($app, $groupMethod) {
            $controller = $app->controller;
            $view       = $app->getView();
            $route      = $app->requestedRoute;
            ChromePhp::groupEnd();
            ChromePhp::groupCollapsed($route);
            ChromePhp::$groupMethod('beforRunAction');
            $controller->on(Controller::EVENT_BEFORE_ACTION, function() use($groupMethod){
                ChromePhp::groupEnd();
                ChromePhp::$groupMethod('beforAction');
            });
            $view->on(View::EVENT_BEFORE_RENDER, function($event) use($groupMethod){
                $viewFile = $event->viewFile ?? '';
                ChromePhp::$groupMethod('render file:' . $viewFile);
            });
            $view->on(View::EVENT_AFTER_RENDER, function($event) use($groupMethod){
                ChromePhp::groupEnd();
            });
            $view->on(View::EVENT_BEGIN_PAGE, function(){ChromePhp::info('HTML Is Begin!');});
            $view->on(View::EVENT_BEGIN_BODY, function(){ChromePhp::info('Body Is Begin!');});
            $view->on(View::EVENT_END_BODY, function(){ChromePhp::info('Body is End!');});
            $view->on(View::EVENT_END_PAGE, function(){ChromePhp::info('HTML is End!');});

        });
        $app->on(Application::EVENT_AFTER_REQUEST, function () use ($app, $groupMethod) {
            ChromePhp::groupEnd();
            ChromePhp::$groupMethod('pageInfo');
            $request  = $app->getRequest();
            $response = $app->getResponse();
            $info     = [
                'phpVersion' => PHP_VERSION,
                'yiiVersion' => Yii::getVersion(),
                'env'        => YII_ENV,
                'version'    => PHP_VERSION,
                'url'        => $request->getAbsoluteUrl(),
                'ajax'       => (int) $request->getIsAjax(),
                'method'     => $request->getMethod(),
                'ip'         => $request->getUserIP(),
                'time'       => $_SERVER['REQUEST_TIME_FLOAT'],
                'statusCode' => $response->statusCode,
                'sqlCount'   => Yii::getLogger()::getSqlCount(),
                'memory'     => sprintf('%.3f MB', memory_get_peak_usage() / 1048576),
                'time'       => sprintf("%.2fms", (microtime(true) * 1000 - self::$time)),
            ];

            foreach ($info as $key => $value) {
                $value = is_string($value) ? $value : print_r($value, true);
                ChromePhp::info(sprintf("%s : %s", $key, $value));
            }

            ChromePhp::groupEnd();
            ChromePhp::groupEnd();
        });
    }

}

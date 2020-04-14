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
    /**
     *
     * @param $app
     * @author    hyman    hyman@an2.net
     */
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
            self::$time = microtime(true) * 1000;
            $logger     = new ChromeLogger();
            $targets    = Yii::$app->getLog()->setLogger($logger);
            Yii::setLogger($logger);
            ChromePhp::groupCollapsed('being');
        });

        $app->on(Application::EVENT_BEFORE_ACTION, function () use ($app) {
            $controller = $app->controller;
            $view       = $app->getView();
            $route      = $app->requestedRoute;
            ChromePhp::groupEnd();
            ChromePhp::groupCollapsed($route);
            ChromePhp::groupCollapsed('beforRunAction');
            $controller->on(Controller::EVENT_BEFORE_ACTION, [$this, 'runBeforeAction']);
            $app->getView()->on(View::EVENT_BEFORE_RENDER, [$this, 'runBeforeRender']);
            $app->getView()->on(View::EVENT_BEGIN_PAGE, [$this, 'runBeginPage']);

        });
        $app->on(Application::EVENT_AFTER_REQUEST, function () use ($app) {
            ChromePhp::groupEnd();
            ChromePhp::groupCollapsed('pageInfo');
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
                'sqlCount'   => Yii::getLogger()::$sqlCoounter,
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

    /**
     *
     * @author    hyman    hyman@an2.net
     */
    public function runBeforeAction()
    {
        ChromePhp::groupEnd();
        ChromePhp::groupCollapsed('action');
    }

    /**
     *
     * @author    hyman    hyman@an2.net
     */
    protected function runBeforeRender()
    {
        ChromePhp::groupEnd();
        ChromePhp::groupCollapsed('render');
    }

    /**
     *
     * @author    hyman    hyman@an2.net
     */
    protected function runBeginPage()
    {
        ChromePhp::groupEnd();
        ChromePhp::groupCollapsed('page');
    }

}

<?php

namespace phuongdev89\phpcheckstyle;

use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

    /**
     * Bootstrap method to be called during application bootstrap stage.
     *
     * @param Application $app the application currently running
     *
     */
    public function bootstrap($app)
    {
        Yii::setAlias('phuongdev89/phpcheckstyle', __DIR__);
    }
}

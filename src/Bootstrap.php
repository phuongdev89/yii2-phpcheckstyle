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
        $constantFile = Yii::getAlias('@vendor/phpcheckstyle/phpcheckstyle/src/PHPCheckstyle/_Constants.php');
        $constant = file_get_contents($constantFile);
        preg_match('/\.\s\"(\/\.\.)\"/', $constant, $output);
        if (isset($output[1])) {
            $constant = str_replace('"/.."', '"/../.."', $constant);
            file_put_contents($constantFile, $constant);
        }
    }
}

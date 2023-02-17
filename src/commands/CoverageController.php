<?php
/**
 * Created by FesVPN.
 * @project  tribeiptv-shop
 * @author   Phuong Dev <phuongdev89@gmail.com>
 * @datetime 2/17/2023 10:33 AM
 */

namespace phuongdev89\phpcheckstyle\commands;

use PHPCheckstyle\PHPCheckstyle;
use phuongdev89\base\Module;
use Yii;
use yii\console\Controller;

class CoverageController extends Controller
{
    /**
     * @var bool adiguhadighai uiasdghuiadhg
     */
    public $progress = true;

    public $format = ['html'];
    public $quite = false;
    public $level = INFO;

    public $maxErrors = 100;

    public $language = 'en-us';
    public $outdir = null;
    public $config = null;
    public $debug = false;

    public function init()
    {
        parent::init();
        if ($this->outdir === null) {
            $this->outdir = Yii::getAlias('@runtime/phpcheckstyle');
        }
        if ($this->config === null) {
            $this->config = Yii::getAlias('@phuongdev89/phpcheckstyle/phpcheckstyle.xml');
        }
    }

    public function options($actionID)
    {
        $a[] = 'progress';
        return $a;
    }

    public function actionRun($src, $exclude = '')
    {
        defined('PHPCHECKSTYLE_HOME_DIR') or define('PHPCHECKSTYLE_HOME_DIR', Yii::getAlias('@vendor/phpcheckstyle/phpcheckstyle'));
        if (!is_array($exclude)) {
            if ($exclude == '') {
                $exclude = [];
            } else {
                $exclude = [$exclude];
            }
        }
        if (substr($src, 0, 1) !== '@') {
            $src = '@' . $src;
        }
        if (strpos($src, '@') !== false) {
            $src = Yii::getAlias($src);
        }
        if (!file_exists($src)) {
            echo 'Folder/File does not exist';
            die;
        }
        if (!is_array($src)) {
            $src = [$src];
        }
        $lineCountFile = null;
        $time_start = microtime(true);
        $style = new PHPCheckstyle($this->format, $this->outdir, $this->config, $lineCountFile, $this->debug, $this->progress);
        if (file_exists(PHPCHECKSTYLE_HOME_DIR . '/../src/PHPCheckstyle/Lang/' . $this->language . '.ini')) {
            $style->setLang($this->language);
        }
        $style->processFiles($src, $exclude);

        $errorCounts = $style->getErrorCounts();
        if (!$this->quite) {
            echo PHP_EOL . "Summary" . PHP_EOL;
            echo "=======" . PHP_EOL . PHP_EOL;
            echo "Errors:   " . $errorCounts[ERROR] . PHP_EOL;
            echo "Ignores:  " . $errorCounts[IGNORE] . PHP_EOL;
            echo "Infos:    " . $errorCounts[INFO] . PHP_EOL;
            echo "Warnings: " . $errorCounts[WARNING] . PHP_EOL;
            echo "=======" . PHP_EOL . PHP_EOL;
            echo "Reporting Completed." . PHP_EOL;
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            echo "Processing ended in " . $time . " ms" . PHP_EOL;
        }
    }

    /**
     * using to test coverage
     * @return void
     *
     * @datetime 17/2/2023 1:20 AM
     * @author   Phuong Dev <phuongdev89@gmail.com>
     */
    public function actionIndex($full = false)
    {
        $src = [];
        $need_to_run = true;
        $outdir = Yii::getAlias('@runtime') . '/phpcheckstyle';
        if (!file_exists($outdir)) {
            mkdir($outdir, 0777, true);
        }
        if (PHP_OS === 'WINNT') {
            $command = 'php .\vendor\phpcheckstyle\phpcheckstyle\run.php --progress --config ' . Yii::getAlias('@phuongdev89/phpcheckstyle') . '/phpcheckstyle.xml --outdir ' . $outdir;
            $start = 'start ' . $outdir . '\index.html';
        } else {
            $command = './vendor/bin/phpcheckstyle --progress --config ' . Yii::getAlias('@phuongdev89/phpcheckstyle') . '/phpcheckstyle.xml --outdir ' . $outdir;
            $start = 'echo "Open ' . $outdir . '/index.html to see report';
        }
        if ($full) {
            if (Module::isBasic()) {
                $command .= ' --src ' . Yii::getAlias('@app');
            } else {
                $command .= ' --src ' . Yii::getAlias('@common') . '/../';
            }
        } else {
            $need_to_run = false;
            $git = exec('git status -s', $files);
            if ($git !== false) {
                foreach ($files as $key => $file) {
                    preg_match('/^[A\s][M|\s]\s([^environments][^\/config\/].*\.php)/', $file, $output_array);
                    if (isset($output_array[1])) {
                        $src[] = Yii::getAlias('@' . $output_array[1]);
                        $need_to_run = true;
                    }
                    if ($key >= 50) {
                        break;
                    }
                }
            } else {
                echo "Git error";
            }
            $command .= ' --src ' . implode(' --src ', $src);
        }
        if ($need_to_run) {
            if (($output = shell_exec($command)) !== false) {
                echo $output;
                $outfile = $outdir . '/index.html';
                if (file_exists($outfile)) {
                    $content = file_get_contents($outfile);
                    $content = str_replace('</head>', '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script></head>', $content);
                    $content = str_replace('</body>', '<script>$.each($(".tableCellBold"), function(k,v){let line = $(v).text();let h2 = $(v).closest(".dataTable").prev();$(v).html("<a href=\"phpstorm://open?url=file://"+h2.text()+"&line="+line+"\">"+line+"</a>")})</script></body>', $content);
                    file_put_contents($outfile, $content);
                    shell_exec($start);
                }
            }
        } else {
            echo "Nothing to change";
        }
    }
}

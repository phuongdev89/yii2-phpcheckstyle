<?php
/**
 * Created by FesVPN.
 * @project  tribeiptv-shop
 * @author   Phuong Dev <phuongdev89@gmail.com>
 * @datetime 2/17/2023 10:33 AM
 */

namespace phuongdev89\phpcheckstyle\commands;
require_once __DIR__ . '/../../../../phpcheckstyle/phpcheckstyle/vendor/autoload.php';

use Error;
use ErrorException;
use Exception;
use PHPCheckstyle\PHPCheckstyle;
use phuongdev89\base\Module;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class CoverageController extends Controller
{

    /**
     * @var bool Display progress file when checking <br/>
     * If not set, default is 1
     */
    public $progress = true;

    /**
     * @var string Output format (html/text/xml/xml_console/console/html_console). <br/>
     * Defaults to 'html'. <br/>
     * Can be multiple formats separator by comma. <br/>
     * Example: "html,text"
     */
    public $format = 'html';

    /**
     * @var string Level to report. Default is INFO <br/>
     * Value is: INFO ERROR WARNING IGNORE
    */
    public $level = INFO;

    public $maxErrors = 100;

    public $language = 'en-us';

    /**
     * @var string|null Output directory of report. <br/>
     * default is `runtime/phpcheckstyle`
    */
    public $outdir = null;

    /**
     * @var string|null Config path. <br/>
     * default is `phuongdev89/phpcheckstyle/phpcheckstyle.xml`
     */
    public $config = null;

    /**
     * @var bool Debug output. <br/>
     * default is false
     */
    public $debug = false;

    protected $defaultExcludes = ['vendor', 'environments', 'requirements.php', 'console/migrations'];


    /**
     * @inheritDoc
     */
    public function options($actionID)
    {
        return ['progress', 'debug', 'format', 'quite', 'level', 'maxErrors', 'language', 'outdir', 'config'];
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        if (is_string($this->format)) {
            if (strpos($this->format, ',')) {
                $this->format = explode(',', $this->format);
            } else {
                $this->format = [$this->format];
            }
        }
        return parent::beforeAction($action);
    }

    /**
     * Run a scan with folders/files with exclude folders/files
     *
     * Example:
     * php yii coverage/run frontend/controllers frontend/models,frontend/forms
     *
     * @param string|array $src
     * @param string|array $exclude exclude folders/files, separator by comma
     * @return void
     * @throws Exception
     *
     * @datetime 18/2/2023 1:20 AM
     * @author   Phuong Dev <phuongdev89@gmail.com>
     * @version  1.0.0
     */
    public function actionRun($src, $exclude = '')
    {
        if (!is_array($exclude)) {
            if ($exclude == '') {
                $exclude = $this->defaultExcludes;
            } else {
                $exclude = ArrayHelper::merge($this->defaultExcludes, [$exclude]);
            }
        }
        if (!is_array($src)) {
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
            $src = [$src];
        }
        $lineCountFile = null;
        $time_start = microtime(true);
        $style = new PHPCheckstyle($this->format, $this->outdir, $this->config, $lineCountFile, $this->debug, $this->progress);
        if (file_exists(PHPCHECKSTYLE_HOME_DIR . '/../src/PHPCheckstyle/Lang/' . $this->language . '.ini')) {
            $style->setLang($this->language);
        }
        try {
            $style->processFiles($src, $exclude);
        } catch (Exception|Error|ErrorException $e) {
            echo 'Above file need to be formatted before continue' . PHP_EOL;
            echo $e->getMessage();
        }
        $errorCounts = $style->getErrorCounts();
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
        $this->_phpstormDebug();
        $this->_runWebBrowser();
    }

    /**
     * using to test coverage
     *
     * Example:
     * php yii coverage
     *
     * @param bool $full <br/>
     * true - if you want to scan coverage all project <br/>
     * false - only scan by git status -s
     *
     * @return void
     * @throws Exception
     *
     * @datetime 17/2/2023 1:20 AM
     * @author   Phuong Dev <phuongdev89@gmail.com>
     * @version  1.0.0
     */
    public function actionIndex($full = false)
    {
        $src = [];
        $need_to_run = true;
        if (!file_exists($this->outdir)) {
            mkdir($this->outdir, 0777, true);
        }
        if ($full) {
            if (Module::isBasic()) {
                $src[] = Yii::getAlias('@app');
            } else {
                $src[] = dirname(Yii::getAlias('@common'));
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
        }
        if ($need_to_run) {
            $this->actionRun($src);
            $this->_phpstormDebug();
            $this->_runWebBrowser();
        } else {
            echo "Nothing to change";
        }
    }

    /**
     * Supported phpstorm debug by using https://github.com/phuongdev89/phpstorm-protocol
     *
     * @return void
     *
     * @datetime 18/2/2023 1:44 AM
     * @author   Phuong Dev <phuongdev89@gmail.com>
     * @version  1.0.0
     */
    private function _phpstormDebug()
    {
        $outfile = $this->outdir . '/index.html';
        if (file_exists($outfile)) {
            $content = file_get_contents($outfile);
            $content = str_replace('</head>', '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script></head>', $content);
            $content = str_replace('</body>', '<script>$.each($(".tableCellBold"), function(k,v){let line = $(v).text();let h2 = $(v).closest(".dataTable").prev();$(v).html("<a href=\"phpstorm://open?url=file://"+h2.text()+"&line="+line+"\">"+line+"</a>")})</script></body>', $content);
            file_put_contents($outfile, $content);
        }

    }

    /**
     * Open report by using default browser
     *
     * @return void
     *
     * @datetime 21/2/2023 12:51 AM
     * @author   Phuong Dev <phuongdev89@gmail.com>
     * @version  1.0.0
     */
    private function _runWebBrowser()
    {
        if (PHP_OS === 'WINNT') {
            $start = 'start ' . $this->outdir . '\index.html';
        } else {
            $start = 'echo "Open ' . $this->outdir . '/index.html to see report';
        }
        shell_exec($start);
    }
}

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
    const GIT_REGEX = '/[AM]\s+(.*\.php)/';

    /**
     * @var bool Display progress file when checking
     * If not set, default is 1
     */
    public $progress = true;

    /**
     * @var string|array Output format (html/text/xml/xml_console/console/html_console/junit).
     * Defaults to 'html'.
     * Can be multiple formats separator by comma.
     * Example: "html,text"
     */
    public $format = 'html';

    /**
     * @var string Level to report. Default is INFO
     * Value is: INFO ERROR WARNING IGNORE
     */
    public $level = INFO;

    public $maxErrors = 100;

    public $language = 'en-us';

    /**
     * @var string|null Output directory of report.
     * default is `runtime/phpcheckstyle`
     */
    public $outdir = null;

    /**
     * @var string|null Config path.
     * default is `phuongdev89/phpcheckstyle/phpcheckstyle.xml`
     */
    public $config = null;

    /**
     * @var bool Debug output.
     * default is false
     */
    public $debug = false;

    protected $defaultExcludes = ['vendor', 'environments', 'requirements.php', 'console/migrations'];

    protected $defaultRootDir = null;

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

        if ($this->defaultRootDir === null) {
            if (Module::isBasic()) {
                $this->defaultRootDir = Yii::getAlias('@app');
            } else {
                $this->defaultRootDir = dirname(Yii::getAlias('@common'));
            }
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
        $format = $this->format;
        if (!is_array($exclude)) {
            if ($exclude == '') {
                $exclude = $this->defaultExcludes;
            } else {
                $exclude = ArrayHelper::merge($this->defaultExcludes, [$exclude]);
            }
        }
        if (!is_array($src)) {
            if (substr($src, 0, 1) !== '@') {
                $src = $this->defaultRootDir . DIRECTORY_SEPARATOR . $src;
            } else {
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
        $style = new PHPCheckstyle($format, $this->outdir, $this->config, $lineCountFile, $this->debug, $this->progress);
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
        if (in_array('html', $format)) {
            $this->_phpstormDebug();
            $this->_runWebBrowser();
        }
    }

    /**
     * using to test coverage by given commit hash or recent commit
     *
     * @param string|null $hash git commit hash
     *
     * @return void
     * @throws Exception
     *
     * @datetime 17/2/2023 1:20 AM
     * @author   Phuong Dev <phuongdev89@gmail.com>
     * @version  1.0.0
     */
    public function actionGit($hash = null)
    {
        $src = [];
        if ($hash !== null) {
            $git = exec('git show --pretty="" --name-status ' . $hash, $files);
        } else {
            $git = exec('git status -s', $files);
        }
        if ($git !== false) {
            foreach ($files as $key => $file) {
                preg_match(self::GIT_REGEX, $file, $output_array);
                if (isset($output_array[1])) {
                    $src[] = $this->defaultRootDir . DIRECTORY_SEPARATOR . trim($output_array[1]);
                }
            }
        } else {
            echo "Git error";
        }
        echo '<pre>';
        print_r($src);
        die;
        if ($src != null) {
            $this->actionRun($src);
        } else {
            echo "Nothing to run";
        }
    }

    /**
     * using to test coverage
     *
     * Example:
     * php yii coverage
     *
     * @param bool $full
     * true - if you want to scan coverage all project
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
        if (!file_exists($this->outdir)) {
            mkdir($this->outdir, 0777, true);
        }
        if ($full) {
            if (Module::isBasic()) {
                $src[] = Yii::getAlias('@app');
            } else {
                $src[] = dirname(Yii::getAlias('@common'));
            }
            $this->actionRun($src);
        } else {
            $this->actionGit();
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
            $start = 'start ' . $this->outdir . DIRECTORY_SEPARATOR . 'index.html';
        } else {
            $start = 'echo "Open ' . $this->outdir . DIRECTORY_SEPARATOR . 'index.html to see report';
        }
        shell_exec($start);
    }
}

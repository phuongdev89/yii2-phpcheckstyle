<?php
/**
 * Created by FesVPN.
 * @project  tribeiptv-shop
 * @author   Phuong Dev <phuongdev89@gmail.com>
 * @datetime 2/17/2023 10:33 AM
 */

namespace phuongdev89\phpcheckstyle\commands;

use Yii;
use yii\console\Controller;

class CoverageController extends Controller
{

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
        if (!file_exists(dirname($outdir))) {
            mkdir($outdir, 0777, true);
        }
        if (PHP_OS === 'WINNT') {
            $command = 'php .\vendor\phpcheckstyle\phpcheckstyle\run.php --config ' . Yii::getAlias('@phuongdev89/phpcheckstyle') . '/phpcheckstyle.xml --outdir ' . $outdir;
            $start = 'start ' . $outdir . '\index.html';
        } else {
            $command = './vendor/bin/phpcheckstyle --config ' . Yii::getAlias('@phuongdev89/phpcheckstyle') . '/phpcheckstyle.xml --outdir ' . $outdir;
            $start = 'echo "Open ' . $outdir . '/index.html to see report';
        }
        if (!$full) {
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
            if (shell_exec($command)) {
                $outfile = $outdir . '/index.html';
                $content = file_get_contents($outfile);
                $content = str_replace('</head>', '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script></head>', $content);
                $content = str_replace('</body>', '<script>$.each($(".tableCellBold"), function(k,v){let line = $(v).text();let h2 = $(v).closest(".dataTable").prev();$(v).html("<a href=\"phpstorm://open?url=file://"+h2.text()+"&line="+line+"\">"+line+"</a>")})</script></body>', $content);
                file_put_contents($outfile, $content);
                shell_exec($start);
            }
        } else {
            echo "Nothing to change";
        }
    }
}

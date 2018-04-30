<?php
/**
 * Created by PhpStorm.
 * User: Sarfraz
 * Date: 4/29/2018
 * Time: 2:12 PM
 */

namespace Sarfraznawaz2005\GitUp\Uploader;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sarfraznawaz2005\GitUp\Uploader\Connectors\FTP;
use Sarfraznawaz2005\GitUp\Uploader\Connectors\SFTP;
use ZipArchive;

class Uploader
{
    public $zipFile = 'deployment_gitup.zip';
    public $exportFolder = 'gitup_deployment_files';
    public $extractScriptFile = 'extract_gitup.php';

    protected $options = [];

    public function __construct()
    {
        $this->exportFolder = $this->tmpDir() . $this->exportFolder;
        $this->zipFile = $this->tmpDir() . $this->zipFile;
        $this->extractScriptFile = $this->tmpDir() . $this->extractScriptFile;

        $this->zipFile = str_replace('\\', '/', $this->zipFile);
        $this->exportFolder = str_replace('\\', '/', $this->exportFolder);
        $this->extractScriptFile = str_replace('\\', '/', $this->extractScriptFile);
    }

    public function setOptions(array $options = [])
    {
        $this->options = $options;
    }

    public function getConnector()
    {
        if ($this->options['connector'] === 'FTP') {
            $connector = new FTP();
        } elseif ($this->options['connector'] === 'SFTP') {
            $connector = new SFTP();
        }

        return $connector;
    }

    public function createZipOfChangedFiles($files)
    {
        @unlink($this->zipFile);
        @unlink($this->extractScriptFile);
        @$this->recursiveRmDir($this->exportFolder);

        file_put_contents($this->extractScriptFile, $this->extractScript($this->options['root']));

        @mkdir($this->exportFolder, 0777, true);

        foreach ($files as $file) {
            $folder = $this->exportFolder . DIRECTORY_SEPARATOR . dirname($file);

            if (!file_exists($folder)) {
                @mkdir($folder, 0777, true);
            }

            $file = str_replace('\\', '/', $file);

            $source = getcwd() . '/' . $file;
            $source = str_replace('public/', '', $source);

            $desination = $this->exportFolder . DIRECTORY_SEPARATOR . $file;
            $desination = str_replace('\\', '/', $desination);

            if (!copy($source, $desination)) {
                $this->error('Count not copy: ' . $desination);
            }
        }

        // remove those files from export folder which are excluded
        $iterator = new RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->exportFolder,
                \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $filename => $fileInfo) {
            $pathArray = explode($this->exportFolder, $filename);
            $currentFile = trim($pathArray[1], '\\');
            $currentFile = str_replace('\\', '/', $currentFile);

            if (in_array(trim($currentFile), $files)) {
                continue;
            }

            if (!$fileInfo->isDir()) {
                @unlink($filename);
            }
        }

        // now create zip file of these files
        $this->zipData($this->exportFolder, $this->zipFile);

        $this->recursiveRmDir($this->exportFolder);

        if (!file_exists($this->zipFile)) {
            return false;
        }
    }

    protected function extractScript($root)
    {
        $userRoot = $root;
        $zipFile = basename($this->zipFile);

        return <<< SCRIPT
<?php 
   set_time_limit(0);
   
    \$root = \$_SERVER['DOCUMENT_ROOT'] . '/';
    
    if (false === strpos(\$root, '$userRoot')) {
        \$root = \$_SERVER['DOCUMENT_ROOT'] . "/$userRoot";
    }   
    
  \$zip = new ZipArchive();
  \$res = \$zip->open("\$root/$zipFile");

  if (\$res === true){
    \$zip->extractTo("\$root");
    \$zip->close();
    echo 'ok';
  } else {
    echo 'failed';
  }

SCRIPT;

    }

    protected function recursiveRmDir($dir)
    {
        if (!file_exists($dir) || !is_dir($dir)) {
            return false;
        }

        $iterator = new RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $filename => $fileInfo) {
            if ($fileInfo->isDir()) {
                rmdir($filename);
            } else {
                unlink($filename);
            }
        }

        @rmdir($dir);
    }

    protected function zipData($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();

        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source),
                RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file));
                } else {
                    if (is_file($file) === true) {

                        $str1 = str_replace($source . '/', '', '/' . $file);
                        $zip->addFromString($str1, file_get_contents($file));

                    }
                }
            }
        } else {
            if (is_file($source) === true) {
                $zip->addFromString(basename($source), file_get_contents($source));
            }
        }

        return $zip->close();
    }

    /**
     * Filter ignore files.
     *
     * @param array $files Array of files which needed to be filtered
     *
     * @param $ignoredPatterns
     * @return array with `files` (filtered) and `filesToSkip`
     */
    public function filterIgnoredFiles($files, $ignoredPatterns)
    {
        $filesToSkip = [];

        $files = array_map(function ($file) {
            return str_replace('\\', '/', $file);
        }, $files);

        foreach ($files as $i => $file) {
            foreach ($ignoredPatterns as $pattern) {
                if ($this->patternMatch($pattern, $file)) {
                    unset($files[$i]);
                    $filesToSkip[] = $file;
                    break;
                }
            }
        }

        $files = array_values($files);

        return ['upload' => $files, 'ignored' => $filesToSkip];
    }

    /**
     * Glob the file path.
     *
     * @param string $pattern
     * @param string $string
     *
     * @return string
     */
    protected function patternMatch($pattern, $string)
    {
        return preg_match('#^' . strtr(preg_quote($pattern, '#'), ['\*' => '.*', '\?' => '.']) . '$#i', $string);
    }

    protected function tmpDir()
    {
        $dir = sys_get_temp_dir();
        $dir = trim($dir);

        if ($dir && substr($dir, -1, 1) !== '/') {
            $dir = $dir . '/';
            return $dir;
        }

        return '';
    }
}
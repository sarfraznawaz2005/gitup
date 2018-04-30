<?php

namespace Sarfraznawaz2005\GitUp\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Sarfraznawaz2005\GitUp\Facades\GitUp;
use Sarfraznawaz2005\GitUp\Uploader\Uploader;

// TODO: Rollback feature

class GitUpController extends BaseController
{
    protected $uploader = null;
    protected $options = [];

    public function __construct()
    {
        $this->options = config('gitup');

        if (config('gitup.http_authentication')) {
            $this->middleware('auth.basic');
        }
    }

    public function index()
    {
        $isDirty = false;
        $commits = GitUp::commitsAll();

        $files = GitUp::getFiles();

        if ($files) {
            $isDirty = true;
        }

        $uploadedCommits = DB::table('commits')->pluck('commit_id')->toArray();

        return view('gitup::index', compact('commits', 'isDirty', 'uploadedCommits'));
    }

    public function getFiles($commitId = '')
    {
        $diffLog = '';

        if ($commitId) {
            $files = GitUp::getFiles($commitId);
            $diffLog = GitUp::getDiffLog($commitId);
        } else {
            $files = GitUp::getFiles();
        }

        return view('gitup::files', compact('files', 'diffLog'));
    }

    public function previewUploadFiles()
    {
        $files = [];
        $uploadFiles = [];
        $deleteFiles = [];

        $servers = config('gitup.servers');
        $commits = request()->commits;

        foreach ($commits as $key => $commit) {
            $files[] = GitUp::getFiles($commit);
        }

        foreach ($files as $fileArray) {
            foreach ($fileArray as $file) {
                $type = current($file);
                $path = next($file);

                if (!trim($path) || $path == '.' || $path == '..') {
                    continue;
                }

                if ($type && $path) {
                    if ($type === 'A' || $type === 'C' || $type === 'M' || $type === 'T') {
                        $uploadFiles[] = $path;
                    } elseif ($type === 'D') {
                        $deleteFiles[] = $path;
                    }
                }
            }
        }

        // do not upload excluded files
        $this->uploader = new Uploader();

        $files = $this->uploader->filterIgnoredFiles($uploadFiles, $this->options['ignored']);
        $uploadFiles = $files['upload'];
        $ignoredFiles = $files['ignored'];

        return view('gitup::preview', compact('uploadFiles', 'deleteFiles', 'ignoredFiles', 'servers', 'commits'));
    }

    public function uploadFiles()
    {
        error_reporting(1);
        set_time_limit(0);

        ini_set('output_buffering', false);
        ini_set('implicit_flush', 'true');

        $files = [];
        $uploadFiles = [];
        $deleteFiles = [];

        if (!request()->server_name) {
            $this->error('No server specified!');
            exit;
        }

        $options = config('gitup.servers.' . request()->server_name);

        // check to make sure we are good to go
        $this->checkUp($options);

        /*
         * Git Status Codes
         *
         * A: addition of a file
         * C: copy of a file into a new one
         * D: deletion of a file
         * M: modification of the contents or mode of a file
         * R: renaming of a file
         * T: change in the type of the file
         * U: file is unmerged (you must complete the merge before it can be committed)
         * X: "unknown" change type (most probably a bug, please report it)
         */

        foreach (request()->commits as $key => $commit) {
            $files[] = GitUp::getFiles($commit);
        }

        foreach ($files as $fileArray) {
            foreach ($fileArray as $file) {
                $type = current($file);
                $path = next($file);

                if (!trim($path) || $path == '.' || $path == '..') {
                    continue;
                }

                if ($type && $path) {
                    if ($type === 'A' || $type === 'C' || $type === 'M' || $type === 'T') {
                        $uploadFiles[] = $path;
                    } elseif ($type === 'D') {
                        $deleteFiles[] = $path;
                    }
                }
            }
        }

        // do not upload excluded files
        $this->uploader = new Uploader();
        $this->uploader->setOptions($options);

        $uploadFiles = $this->uploader->filterIgnoredFiles($uploadFiles, $this->options['ignored'])['upload'];

        // output
        echo '<body style="background: #eee;"></body>';
        echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" />';
        echo '<div style="padding: 15px; background: #333; color:#fff; width: 600px; margin: 20px auto; border-radius: 10px;">';

        if (!$uploadFiles) {
            $this->out('Nothing to Upload');
            exit;
        }

        try {

            $this->out('Deployment started...');
            $this->out('Zipping files...');

            $result = $this->uploader->createZipOfChangedFiles($uploadFiles);

            if ($result === false) {
                $this->error('Could not create zip file to upload :(');
                exit;
            }

            $this->out('Zip file created...');

            $connector = $this->uploader->getConnector();

            $this->out('Connecting to server...');

            $connector->connect($options);

            $this->out('Uploading extract files script...');

            $uploadStatus = $connector->upload($this->uploader->extractScriptFile, $options['public_path']);

            if (!$uploadStatus) {
                $this->error('Could not upload script file.');
                exit;
            }

            $this->out('Uploading zip file...');

            $uploadStatus = $connector->upload($this->uploader->zipFile, '/');

            if (!$uploadStatus) {
                $this->error('Could not upload zip file.');
                exit;
            }

            $this->out('Extracting files on server...');

            $hitUrl = $options['domain'] . $options['public_path'] . '/' . basename($this->uploader->extractScriptFile);
            $response = file_get_contents($hitUrl);

            if ($response === 'ok') {

                $this->out('Files uploaded successfully...');

                // delete files deleted in commits
                if ($deleteFiles) {
                    foreach ($deleteFiles as $file) {
                        $deleteStatus = $connector->deleteAt($file);

                        if ($deleteStatus === true) {
                            $this->out('Deleted: ' . $file);
                        } else {
                            $this->error("Could not delete '$file'");
                        }
                    }
                }

                $this->out('Finishing, please wait...');

                // delete script file
                $connector->deleteAt($options['public_path'] . $this->uploader->extractScriptFile);

                // delete deployment file
                $connector->delete(basename($this->uploader->zipFile));

                // save data in db too
                $commits = GitUp::commitsData(request()->commits);

                foreach ($commits as $commit) {

                    $uploadFiles = [];
                    $deleteFiles = [];

                    $files = GitUp::getFiles($commit['commit_id']);

                    foreach ($files as $fileArray) {
                        $type = current($fileArray);
                        $path = next($fileArray);

                        if (!trim($path) || $path == '.' || $path == '..') {
                            continue;
                        }

                        if ($type && $path) {
                            if ($type === 'A' || $type === 'C' || $type === 'M' || $type === 'T') {
                                $uploadFiles[] = $path;
                            } elseif ($type === 'D') {
                                $deleteFiles[] = $path;
                            }
                        }
                    }

                    $files = [
                        'upload' => $uploadFiles,
                        'delete' => $deleteFiles,
                    ];

                    DB::table('commits')->insert([
                        'user' => $commit['user'],
                        'commit_id' => $commit['commit_id'],
                        'message' => $commit['message'],
                        'files' => json_encode($files),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }

                $this->out('Deployment finished :)');

            } else {
                $this->error('Error: Unable to extract files.');
            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        echo '<hr>';
        echo '<a href="' . route('__gitup__') . '" class="btn btn-warning btn-sm">&larr; Back</a>';
    }

    protected function out($message)
    {
        $arrow = '&#10004;';

        echo "$arrow $message<br>";

        ob_flush();
        flush();
        sleep(1);
    }

    protected function error($message)
    {
        echo "<span style='color:red;'>$message</span><br>";

        ob_flush();
        flush();
        sleep(1);
    }

    /**
     * Checks if everything is okay before we proceed
     *
     * @param $options
     */
    protected function checkUp($options)
    {
        $dir = dirname(getcwd());

        if (!isset($options['connector'])) {
            $this->error('Connector is not specified in config file!');
            exit;
        }

        if (!file_exists("$dir/.git")) {
            $this->error("'$dir' is not a Git repository");
            exit;
        }
    }

    public function statistics()
    {
        $commits = collect(GitUp::commitsAll())->groupBy('user');

        return view('gitup::statistics', compact('commits'));
    }
}

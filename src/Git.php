<?php
/**
 * Created by PhpStorm.
 * User: Sarfaraz
 * Date: 4/25/2018
 * Time: 1:56 PM
 */

namespace Sarfraznawaz2005\GitUp;

use Carbon\Carbon;

class Git
{
    /**
     * Executes given command
     *
     * @param $command
     * @return string
     */
    protected function execute($command)
    {
        $command = str_replace("\n", '', $command);

        $result = shell_exec(escapeshellcmd($command) . ' 2>&1');

        return $result ?: '';
    }

    public function commitsToday()
    {
        $yesterday = Carbon::parse(date('Y-m-d'))->subDay(1)->toDateTimeString();

        $commits = $this->execute("git log --after=\"$yesterday\" --pretty=format:\"%cn|%h|%cd|%B\"");

        return $this->getCommitDetails($commits);
    }

    public function commitsAll()
    {
        $commits = $this->execute("git log --pretty=format:\"%cn|%h|%cd|%B\"");

        return $this->getCommitDetails($commits);
    }

    public function commitsData(array $commitIds)
    {
        $commits = '';
        foreach ($commitIds as $key => $commit) {
            $commits .= $this->execute("git log -1 $commit --pretty=format:\"%cn|%h|%cd|%B\"");
        }

        return $this->getCommitDetails($commits);
    }

    public function getCommitDetails($commits)
    {
        $data = [];

        if ($commits) {
            $lines = explode("\n", $commits);
            $lines = array_filter($lines);

            foreach ($lines as $line) {
                if (!trim($line)) {
                    continue;
                }

                $pieces = explode('|', trim($line));
                $pieces = array_map('trim', $pieces);

                if (! isset($pieces[2])) {
                    continue;
                }

                $pieces[2] = Carbon::parse(date('Y/m/d H:i:s', strtotime($pieces[2])))->format('m-d-Y H:i:s');

                $data[] = [
                    'user' => $pieces[0],
                    'commit_id' => $pieces[1],
                    'date' => $pieces[2],
                    'message' => $pieces[3],
                ];
            }
        }

        return $data;
    }

    public function getFiles($commitId = '')
    {
        $data = [];

        if ($commitId) {
            $files = $this->execute("git diff-tree --no-commit-id --name-status -r $commitId");
        } else {
            // for un-committed files
            $files = $this->execute('git status --porcelain');
        }

        $files = explode("\n", $files);

        foreach ($files as $file) {
            if (!trim($file)) {
                continue;
            }

            $file = str_replace(["\t", ' '], '|', trim($file));
            $pieces = explode('|', $file);
            $pieces = array_map('trim', $pieces);

            $data[] = [
                'status' => $pieces[0],
                'file' => $pieces[1]
            ];
        }

        return $data;
    }

    public function getDiffLog($commitId)
    {
        $data = '';

        $lines = $this->execute("git whatchanged -m -n 1 -p $commitId");
        $lines = explode("\n", $lines);

        foreach ($lines as $line) {

            $line = str_replace("\t", str_repeat('&nbsp;', 4), $line);
            $line = str_replace(' ', '&nbsp;', $line);

            if (trim($line) && $line[0] === '+') {
                $data .= "<span style='color:green;'>$line</span><br>";
            } elseif (trim($line) && $line[0] === '-') {
                $data .= "<span style='color:red;'>$line</span><br>";
            } else {
                $data .= $line . '<br>';
            }
        }

        return $data;
    }
}

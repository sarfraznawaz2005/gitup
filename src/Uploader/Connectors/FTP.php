<?php
/**
 * Created by PhpStorm.
 * User: Sarfraz
 * Date: 8/17/2017
 * Time: 4:47 PM
 */

namespace Sarfraznawaz2005\GitUp\Uploader\Connectors;

use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Filesystem;
use Sarfraznawaz2005\GitUp\Uploader\Contracts\ConnectorInterface;
use Sarfraznawaz2005\GitUp\Uploader\Traits\Options;

class FTP implements ConnectorInterface
{
    use Options;

    protected $connector = null;

    function connect($options)
    {
        try {
            $this->connector = new Filesystem(new FtpAdapter($this->getOptions($options)));
        } catch (\Exception $e) {
            echo "\r\nError: {$e->getMessage()}\r\n";
            exit;
        }
    }

    function upload($path, $destination, $overwrite = true)
    {
        $destination = $destination . '/' . basename($path);
        $destination = str_replace('//', '/', $destination);

        if ($overwrite && $this->existsAt($destination)) {
            $this->deleteAt($destination);
        }

        $stream = fopen($path, 'r+');
        $result = $this->connector->writeStream($destination, $stream);
        fclose($stream);

        return $result;
    }

    function exists($path)
    {
        return $this->connector->has(basename($path));
    }

    function existsAt($path)
    {
        return $this->connector->has($path);
    }

    function delete($path)
    {
        $this->deleteAt(basename($path));
    }

    function deleteAt($path)
    {
        try {
            if (!is_dir($path)) {
                return $this->connector->delete($path);
            } else {
                return $this->connector->deleteDir($path);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    function write($path, $contents, $overwrite = true)
    {
        if ($overwrite && $this->exists($path)) {
            $this->delete($path);
        }

        return $this->connector->write(basename($path), $contents);
    }

    function read($path)
    {
        return $this->connector->read(basename($path));
    }
    
    function disconnect()
    {
        if (is_resource($this->connector)) {
            dd($this->connector);
            ftp_close($this->connector);
        }

        $this->connector = null;        
    }    
}

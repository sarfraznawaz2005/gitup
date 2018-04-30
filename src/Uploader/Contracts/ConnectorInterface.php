<?php
/**
 * Created by PhpStorm.
 * User: Sarfraz
 * Date: 8/16/2017
 * Time: 4:22 PM
 */

namespace Sarfraznawaz2005\GitUp\Uploader\Contracts;

interface ConnectorInterface
{
    function connect($server);

    function upload($path, $destination, $overwrite = true);

    function exists($path);

    function delete($path);

    function write($path, $contents, $overwrite = true);

    function read($path);
}
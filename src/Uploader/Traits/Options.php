<?php
/**
 * Created by PhpStorm.
 * User: Sarfraz
 * Date: 8/17/2017
 * Time: 11:56 PM
 */

namespace Sarfraznawaz2005\GitUp\Uploader\Traits;

trait Options
{
    public function getOptions($options)
    {
        // read ini file options
        $defaults = [
            'host' => '',
            'username' => '',
            'password' => '',
            'root' => '/',
            'port' => null,
            'passive' => null,
            'timeout' => null,
            'ssl' => false,
        ];

        if (is_array($options)) {
            $options = array_merge($defaults, $options);
        }

        // defaults
        $options['passive'] = ($options['passive'] ?: true);
        $options['ssl'] = ($options['ssl'] ?: false);
        $options['port'] = ($options['port'] ?: 21);

        $this->validateOptions($options);

        $options['root'] = $this->addSlashIfMissing($options['root']);
        $options['domain'] = $this->addSlashIfMissing($options['domain']);
        $options['public_path'] = $this->addSlashIfMissing($options['public_path']);

        return $options;
    }

    private function addSlashIfMissing($path)
    {
        if (substr($path, -1, 1) !== '/') {
            $path = $path . '/';
        }

        return $path;
    }

    /**
     * Validates important options
     *
     * @param array $options
     * @throws \Exception
     */
    protected function validateOptions(array $options)
    {
        if (!trim($options['root'])) {
            throw new \Exception('"root" option not specified!');
        }

        if (!trim($options['public_path'])) {
            throw new \Exception('"public_path" option not specified!');
        }

        if (!trim($options['domain'])) {
            throw new \Exception('"domain" option not specified!');
        }

        if (!filter_var($options['domain'], FILTER_VALIDATE_URL)) {
            throw new \Exception('Invalid value for "domain" option!');
        }

    }
}
<?php
namespace Sarfraznawaz2005\GitUp\Facades;

use Illuminate\Support\Facades\Facade;

class GitUp extends Facade
{
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'GitUp';
    }

} 
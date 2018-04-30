# gitup

[![Laravel 5.1](https://img.shields.io/badge/Laravel-5.1-brightgreen.svg?style=flat-square)](http://laravel.com)
[![Laravel 5.2](https://img.shields.io/badge/Laravel-5.2-brightgreen.svg?style=flat-square)](http://laravel.com)
[![Laravel 5.3](https://img.shields.io/badge/Laravel-5.3-brightgreen.svg?style=flat-square)](http://laravel.com)
[![Laravel 5.4](https://img.shields.io/badge/Laravel-5.4-brightgreen.svg?style=flat-square)](http://laravel.com)
[![Laravel 5.5](https://img.shields.io/badge/Laravel-5.5-brightgreen.svg?style=flat-square)](http://laravel.com)
[![Total Downloads](https://poser.pugx.org/sarfraznawaz2005/gitup/downloads)](https://packagist.org/packages/sarfraznawaz2005/gitup)

Laravel package to upload git commits to server(s) via (s)ftp.

## DISCLAIMER ##

This package is not fully tested, **use it at your own risk!**

## Screenshot ##

![Main Window](https://raw.githubusercontent.com/sarfraznawaz2005/gitup/master/screen.png)

## Requirements ##

 - PHP >= 5.6
 - `git` added to PATH env
 - `FTP` and `Zip` PHP extensions (both ship with PHP and usually turned on)
 
 ## Installation ##
 
 Install via composer
 ```
 composer require sarfraznawaz2005/gitup
 ```
 
 For Laravel < 5.5:
 
 Add Service Provider to `config/app.php` in `providers` section
 ```php
 Sarfraznawaz2005\GitUp\GitUpServiceProvider::class,
 ```
 
 ---
 
 Run `php artisan vendor:publish` to publish package's config and migration file. You should now have `gitup.php` file published in `app/config` folder. It will also publish migration file in `database/migrations` folder.
 
 Run `php artisan migrate` to create `commits` table in your database.
 
 Check and update `config/gitup.php` file to setup config options including S(FTP) server information where you would like to upload.
 
 By default, gitup UI is available at `/gitup` url.
 
 ## Similar Project ##
  - [floyer](https://github.com/sarfraznawaz2005/floyer)
 
 ## License ##
 
 This code is published under the [MIT License](http://opensource.org/licenses/MIT).
 This means you can do almost anything with it, as long as the copyright notice and the accompanying license file is left intact.
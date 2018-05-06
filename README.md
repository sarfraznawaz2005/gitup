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

## Why ##

We have multiple servers eg live, staging, testing, etc and client wanted us to upload task/story # X to staging only or story Y to live only that's when it was hard to track down files worked upon earlier and then upload them selectively; a time consuming process and nuisance so we created this package so that we can easily upload with one click selected stories to asked servers.

## Requirements ##

 - PHP >= 5.6
 - `git` added to PATH env
 - `FTP` and `Zip` PHP extensions (both ship with PHP and usually turned on)
 - `league/flysystem` FTP wrapper used by gitUp. (comes with laravel by default)
 - `league/flysystem-sftp` Library used by gitUp to upload files via SFTP.
 
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
 
 Run `php artisan vendor:publish` to publish package's config and migration file. You should now have `config/gitup.php` file published. It will also publish migration file in `database/migrations` folder.
 
 Run `php artisan migrate` to create `commits` table in your database.
 
 Check and update `config/gitup.php` file to setup config options including S(FTP) server information where you would like to upload.
 
 By default, gitup UI is available at `/gitup` route.
 
 ## How it Works ##

 For selected commits, we extract files out of them and create zip archive along with an script to extract this zip archive. The zip archive and extract script are then uploaded to selected server where extract script extracts the uploaded zip archive. Once the upload process is done, both zip archive and extract script are deleted from the server.

 Uploading zip archive along with extract script has huge speed benefits as all committed files get uploaded in one shot as opposed to uploading each committed file individually.

 ## Similar Project ##
  - [floyer](https://github.com/sarfraznawaz2005/floyer)
 
 ## License ##
 
 This code is published under the [MIT License](http://opensource.org/licenses/MIT).
 This means you can do almost anything with it, as long as the copyright notice and the accompanying license file is left intact.

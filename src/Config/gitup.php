<?php

return [

    # Route where gitUp will be available in your app.
    'route' => 'gitup',

    #-------------------------------------------------------------------------

    # If "true", the gitUp page can be viewed by any user who provides
    # correct login information (eg all app users).
    'http_authentication' => false,

    #-------------------------------------------------------------------------

    # ignored files/patterns. These files will not be uploaded
    'ignored' => [
        '*.scss',
        '*.ini',
        '.git/*',
        '.idea/*',
        '.env',
    ],

    #-------------------------------------------------------------------------

    # setup one or more servers here with (s)ftp information where you want to upload files
    'servers' => [
        'default' => [
            # Connectoin type: FTP or SFTP. Value is case-sensitive
            'connector' => 'FTP',
            # Should also include protocol
            'domain' => 'http://mysite.com',
            'host' => 'ftp.toyoursite.com',
            'username' => 'username',
            'password' => 'password',
            'root' => '/',
            # Publically accessible folder on server for your app
            'public_path' => '/',
            'port' => 21,
            'passive' => true,
            'ssl' => false,
            # private key file path when connecting via SFTP connector
            'key_file' => 'path/to/privatekey',
        ]
    ],
];

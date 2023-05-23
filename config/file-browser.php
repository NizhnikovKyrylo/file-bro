<?php

return [
    /*
    |--------------------------------------------------------------------------
    | An entry folder
    |--------------------------------------------------------------------------
    */
    'entry' => public_path('/files'),
    /*
    |--------------------------------------------------------------------------
    | File or folder permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'folder' => 0755,
        'file' => 0644
    ],
    /*
    |--------------------------------------------------------------------------
    | List of restricted files
    |--------------------------------------------------------------------------
    |
    | This list contains the restricted to upload file mime-types or
    | file extensions
    |
    */
    'restricted' => [
        //'.php', 'image/gif', 'iso'
    ]
    /*'mime-types' => [
        'archive' => [
            'application/gzip',
            'application/java-archive',
            'application/rar',
            'application/zip',
            'application/x-bzip2'
        ],
        'audio' => [
            'audio/aac',
            'audio/ogg',
            'audio/flac',
            'audio/midi',
            'audio/mpeg',
            'audio/x-wav',
            'audio/aifc',
            'audio/x-aiff'
        ],
        'database' => [
            'text/csv',
            'application/csv',
            'application/vnd.sun.xml.base',
            'application/vnd.oasis.opendocument.base',
            'application/vnd.oasis.opendocument.database',
            'application/sql'
        ],
        'font' => ['font/ttf', 'font/woff', 'font/woff2', 'font/opentype', 'application/vnd.ms-fontobject'],
        'images' => [
            'image/gif',
            'image/jp2',
            'image/jpeg',
            'image/png',
            'image/svg+xml',
            'image/tiff',
            'image/bmp'
        ],
        'pdf' => ['application/pdf'],
        'script' => [
            'application/ecmascript',
            'application/hta',
            'application/xhtml+xml',
            'application/xml',
            'application/xslt+xml',
            'text/css',
            'text/x-csrc',
            'text/x-c++src',
            'application/x-asp',
            'text/x-python'
        ],
        'spreadsheet' => [
            'application/vnd.ms-excel',
            'application/vnd.ms-excel.sheet.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.spreadsheet'
        ],
        'text' => ['text/plain', 'text/html', 'text/markdown', 'application/json', 'application/x-x509-ca-cert'],
        'text-processor' => [
            'application/msword',
            'application/rtf',
            'text/rtf',
            'text/richtext',
            'application/vnd.ms-word.document.macroEnabled.12',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.text-master',
            'application/abiword'
        ],
        'video' => [
            'video/avi',
            'video/mpeg',
            'video/mp4',
            'video/quicktime',
            'video/ogg',
            'video/webm',
            'video/x-flv',
            'video/x-msvideo',
            'video/x-matroska',
            'video/x-ms-wmv'
        ]
    ],*/
];

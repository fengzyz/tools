<?php
/**
 * Created by PhpStorm.
 * User: shuyu
 * Date: 2019/6/4
 * Time: 15:57
 */

return [
    'elasticsearch' => [
        'prefix' => env('ELASTICSEARCH_PREFIX', 'laravel_'),
        'hosts' => [
            env('ELASTICSEARCH_HOST', 'http://localhost'),
        ],
        'analyzer' => env('ELASTICSEARCH_ANALYZER', 'ik_max_word'),
        'settings' => [],
        'filter' => [
            '+',
            '-',
            '&',
            '|',
            '!',
            '(',
            ')',
            '{',
            '}',
            '[',
            ']',
            '^',
            '\\',
            '"',
            '~',
            '*',
            '?',
            ':'
        ]
    ],
];
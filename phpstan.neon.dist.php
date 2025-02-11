<?php

declare(strict_types=1);

$config = [
    'includes' => [
        __DIR__ . '/phpstan.neon.dist',
    ],
    'parameters' => [
        'phpVersion' => PHP_VERSION_ID,
    ],
];

if (PHP_VERSION_ID < 80300) {
    $config['includes'][] = __DIR__ . '/phpstan.php-lt-83.neon.dist';
}

if (PHP_VERSION_ID >= 80400) {
    $config['includes'][] = __DIR__ . '/phpstan.php-gte-84.neon.dist';
}

return $config;

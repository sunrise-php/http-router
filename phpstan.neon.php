<?php

declare(strict_types=1);

$config = [
    'includes' => [
        __DIR__ . '/phpstan.neon',
    ],
    'parameters' => [
        'phpVersion' => PHP_VERSION_ID,
    ],
];

if (PHP_VERSION_ID < 80300) {
    $config['includes'][] = __DIR__ . '/phpstan.php-lt-83.neon';
}

if (PHP_VERSION_ID >= 80400) {
    $config['includes'][] = __DIR__ . '/phpstan.php-gte-84.neon';
}

return $config;

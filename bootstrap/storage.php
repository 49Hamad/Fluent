<?php

declare(strict_types=1);

$defaultStoragePath = dirname(__DIR__).DIRECTORY_SEPARATOR.'storage';
$storagePath = $defaultStoragePath;

if (is_file($envFile = dirname(__DIR__).DIRECTORY_SEPARATOR.'.env')) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (! str_starts_with($line, 'STORAGE_PATH=')) {
            continue;
        }

        $value = trim(substr($line, strlen('STORAGE_PATH=')), " \t\"'");

        if ($value !== '') {
            $storagePath = $value;
        }

        break;
    }
}

$viewCompiledPath = rtrim($storagePath, '/\\').'/framework/views';

$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;
$_ENV['VIEW_COMPILED_PATH'] = $viewCompiledPath;
$_SERVER['VIEW_COMPILED_PATH'] = $viewCompiledPath;
putenv('LARAVEL_STORAGE_PATH='.$storagePath);
putenv('VIEW_COMPILED_PATH='.$viewCompiledPath);

$requiredDirectories = [
    'app/public',
    'app/private',
    'framework/cache/data',
    'framework/sessions',
    'framework/views',
    'framework/testing',
    'logs',
];

foreach ($requiredDirectories as $directory) {
    $path = $storagePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $directory);

    if (! is_dir($path)) {
        @mkdir($path, 0755, true);
    }
}

$configCache = dirname(__DIR__).DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'config.php';

if (is_file($configCache)) {
    $cachedConfig = @include $configCache;
    $compiled = is_array($cachedConfig) ? ($cachedConfig['view']['compiled'] ?? null) : null;

    if (! is_string($compiled) || $compiled === '') {
        @unlink($configCache);
    }
}

return $storagePath;

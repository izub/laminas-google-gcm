<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/test'
    ]);

    $rectorConfig->cacheClass(MemoryCacheStorage::class);
    $rectorConfig->importNames();

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74
    ]);
};

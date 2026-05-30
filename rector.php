<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php82: true)
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withPreparedSets(symfonyCodeQuality: true)
    ->withComposerBased(symfony: true);

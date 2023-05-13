<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/phar',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);

    // skip individual rules
    $rectorConfig->skip([
        //
        // I'm skipping this because I want to make sure it's correct before
        // proceeding.
        //

        \Rector\Php80\Rector\FunctionLike\UnionTypesRector::class,

        //
        // I'm skipping this because it's stupid.
        //

        \Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::class,

        //
        // I'm skipping this because it makes the code less readable.  We're
        // instead relying on Symfony to do what it says it will do.
        //

        \Rector\Php71\Rector\FuncCall\CountOnNullRector::class,

        //
        // I'm skipping this for now while I review why Rector thinks casts are
        // necessary here.
        //

        \Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector::class,

    ]);
};

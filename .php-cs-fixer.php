<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/app')
    ->in(__DIR__ . '/config')
    ->in(__DIR__ . '/routes')
    ->in(__DIR__ . '/database')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return ChiefTools\PhpCsFixer\Config::make($finder);

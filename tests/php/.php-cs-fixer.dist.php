<?php
$rootDir = __DIR__ . '/../../';

$finder = PhpCsFixer\Finder::create()
    ->in($rootDir)
    ->exclude(['vendor', 'node_modules', 'version-control'])
;

$config = new PrestaShop\CodingStandards\CsFixer\Config();
return $config
    ->setUsingCache(true)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
    ->setFinder($finder)
    ->setRules([
        'echo_tag_syntax' => ['format' => 'short']
    ])
;

<?php

/**
 * @see https://cs.symfony.com/doc/config.html
 */

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src', __DIR__ . '/base', __DIR__ . '/req')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
    ])
    ->setFinder($finder)
;

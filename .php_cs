<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('vendor')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR2' => true,
        //'strict_param' => true,
        'array_syntax' => array('syntax' => 'short'),
    ))
    ->setFinder($finder)
;

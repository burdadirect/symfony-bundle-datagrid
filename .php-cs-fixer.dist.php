<?php

use BurdaDirect\Codestyle\PHP82;

require 'vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
  ->in(__DIR__ . '/src/');

$fixer = PHP82::create($finder)
  ->setRiskyAllowed(false);

return $fixer;
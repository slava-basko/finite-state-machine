<?php

require_once __DIR__ . '/../vendor/autoload.php';

$iota = 0;
define('DRAFT', $iota++);
define('REVIEW', $iota++);
define('PUBLISH', $iota++);
define('TRASH', $iota++);
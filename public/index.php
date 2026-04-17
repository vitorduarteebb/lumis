<?php

declare(strict_types=1);

use App\Core\Application;

$basePath = dirname(__DIR__);

require $basePath . '/vendor/autoload.php';

/** @var Application $app */
$app = require $basePath . '/bootstrap/app.php';

$app->run();

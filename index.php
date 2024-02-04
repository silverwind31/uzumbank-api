<?php

use Core\App;
use Core\Request;

require __DIR__. '/loader.php';

$app = new App();

$app->run(Request::capture())->send();

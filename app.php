<?php
include_once "vendor/autoload.php";

try {
    new \Frame\Core\Init([
        'localhost' => 'Myapp',
        'johnginsberg.com' => 'Myapp'
    ]);
} catch (\Frame\Core\Exception\RouteNotFoundException $e) {
    echo "404!" . $e->getMessage();
}

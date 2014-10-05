<?php
include_once "vendor/autoload.php";

try {
    new \Frame\Core\Init([
        'localhost' => 'Myapp'
    ]);
} catch (\Frame\Core\Exception\RouteNotFoundException $e) {
    echo "404!";
}

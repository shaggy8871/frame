<?php
include_once "../vendor/autoload.php";

use Frame\Core\Init;
use Frame\Core\Project;

// 3 options for configuring a project
$projects = [
    'localhost' => new Project('Myapp', '../src/Myapp', true),
    'johnginsberg.com' => ['Myapp', '../src/Myapp'],
    'frame.johnginsberg.com' => ['Myapp', '../src/Myapp'],
];

$app = new Init($projects);

// Configure custom 404 handler
$app->onRouteNotFound(function($data) {
    $response = new Frame\Response\Phtml($data['project']);
    $response
        ->setStatusCode(404)
        ->setViewDir('../app/Frame/Core/Scripts')
        ->setViewFilename('error.phtml')
        ->setViewParams([
            'project' => $data['project'],
            'url' => $data['url'],
            'statusCode' => 404,
            'message' => 'Something went horribly wrong!'
        ])
        ->render();
});

// Start 'em up
$app->run();

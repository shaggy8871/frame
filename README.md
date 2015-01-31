# Frame

[![Build Status](https://travis-ci.org/shaggy8871/frame.svg?branch=master)](https://travis-ci.org/shaggy8871/frame)

Frame is a flyweight PHP framework. It's easy to get started, requires almost zero configuration, and can run within existing projects without a major rewrite.

Installation:

In composer.json:
```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/shaggy8871/frame"
    }
],
"require": {
    "shaggy8871/frame": "dev-master"
}
```

Then run:
```
composer install
```

[Grab the sample FRAME application](https://github.com/shaggy8871/frame-sample-app)

Example index.php file

```php
<?php
include_once "../vendor/autoload.php";

/*
 * Format:
 * 'domain' => ['Project name', '/path/to/project/files', debugMode]
 */
$projects = [
    $_SERVER['HTTP_HOST'] => ['Myapp', '../src', true],
];

$app = new Frame\Core\Init($projects);

// Start 'em up
$app->run();

```

## Controller example:

```php
<?php

namespace Myapp\Controllers;

use Frame\Core\Controller;
use Frame\Core\Url;
use Frame\Request\Get;
use Frame\Response\Twig;

class Index extends Controller
{

    /**
     * Add your controller-specific route lookups here if required
     */
    public function routeResolver(Url $url)
    {

    }

    /**
     * This is the home page
     */
    public function routeDefault(Get $request, Twig $response)
    {

        return [
            'title' => 'Welcome to Frame',
            'content' => 'You\'re on the home page. You can customize this view in <Yourapp>/Views/Index/default.html.twig and <Yourapp>/Views/base.html.twig.'
        ];

    }

    /**
     * This is an example about us page
     */
    public function routeAbout(Get $request, Twig $response)
    {

        $response->setViewFilename('Index/default.html.twig');

        return [
            'title' => 'About Us',
            'content' => 'You can customize this page in <Yourapp>/Views/Index/about.html.twig.'
        ];

    }

}

```

(More detailed docs coming soon...)

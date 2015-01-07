<?php

/*
 * To use Twig rendering, ensure your composer.json file contains
 * the following:
 *
 * "require": {
 *      "twig/twig": "~1.0"
 * }
 */

namespace Frame\Response;

class Twig extends Foundation implements ResponseInterface
{

    protected $contentType = 'text/html';
    protected $extension = '.twig';

    public function __construct()
    {

        // Check that Twig is loaded
        if (!class_exists("Twig_Environment")) {
            throw new \Exception("Twig is not installed, Response class cannot be used.");
        }

    }

    public function render($params = null)
    {

        // Instantiate the Twig library only once, keep it global
        if (!isset($this->project->config->twig)) {

            // Make sure we can determine which template to render
            if (!$this->viewBaseDir) {
                throw new \Exception("Twig responder class only works within a controller.")
            }

            // Check for existence of cache directory before instantiating Twig
            $cacheDir = $this->viewBaseDir . '/cache';
            if ((!file_exists($cacheDir)) || (!is_writable($cacheDir))) {
                throw new \Exception("Twig responder class requires the directory " . $cacheDir . ', and it must be writable');
            }

            // Initialize Twig
            $this->project->config->twig = new \Twig_Environment(new \Twig_Loader_Filesystem($this->viewBaseDir), array(
                'cache' => $this->viewBaseDir . '/cache',
            ));

        }

        $params = ($params ?: $this->viewParams);

        if (!headers_sent()) {
            header('Content-Type: ' . $this->contentType);
        }

        // Render a view file with a .twig extension
        $twig = $this->project->config->twig;
        echo $twig->render($this->viewFilename . $this->extension, $params);

    }

}

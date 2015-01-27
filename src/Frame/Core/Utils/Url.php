<?php

namespace Frame\Core\Utils;

class Url
{

    /*
     * Convert a URL template in format /path/to/:var(/:var2(/:var3)) to a parseable
     * regular expression. Optionally returns the keys.
     */
    public static function templateToRegex($urlTemplate, &$keys = [])
    {

        return '/' . str_replace('/', '\/',
            preg_replace_callback('/([\/\(]*)(\()?:([a-z]+)([\)]*)?/',
                function($matches) use (&$keys) {
                    $keys[] = $matches[3];
                    if ($matches[1] == '/') {
                        return '/([A-Za-z0-9_-]+)';
                    } else
                    if ($matches[1] == '(/') {
                        return '/?([A-Za-z0-9_-]+)?';
                    }
                }, $urlTemplate
            )) . '/';

    }

    /*
     * Replaces the parameters into the specified template
     */
    public static function replaceIntoTemplate($urlTemplate, $params = array())
    {

        return
            preg_replace_callback('/([\/\(]*)(\()?:([a-z]+)([\)]*)?/',
                function($matches) use ($params) {
                    if (($matches[1] == '/') || ($matches[1] == '(/')) {
                        return (isset($params[$matches[3]]) ? '/' . $params[$matches[3]] : '');
                    }
                }, $urlTemplate
            );

    }

    /*
     * Pass in a regular expression and we'll extract variables
     */
    public static function extract($urlTemplate, $requestUri)
    {

        $regex = static::templateToRegex($urlTemplate, $keys);

        if (preg_match($regex, $requestUri, $matches) !== false) {
            return array_combine($keys, array_slice($matches, 1));
        }

    }

}

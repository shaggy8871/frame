FRAME
=====

FRAME is a dead-simple PHP framework. Our goals:

1. No bulky installs
2. No yaml or ini configuration files - everything is configurable via PHP itself
3. Heavy use of dependency injection and aliasing to simplify developer interface.

Example:

```php
<?php

namespace Myapp\Controllers;

class Products
{

  /*
   * 'route' methods correspond to URLs
   * Input and output is managed via injected classes. Custom IO classes can be written.
   */
  public function routeDefault(Get $request, Twig $response)
  {

    // Render the product home page using Twig, send through GET variable 'category'
    // The view file is assumed to be /Myapp/Views/Products/default.twig.html
    return array(
      'category' => $request->category
    );

  }

}
```

## RouteResolvers

Each project makes use of one or more routeResolver methods to determine how best to respond to URL requests.

If a project has a Routes.php file within the base directory, and a method called `routeResolver` is defined within, it will be called first.

The `routeResolver` method receives a Url class and expects one of the following response types:

1. The name of a controller class (as a string)
2. A string in the format `Controller::Method` where the controller sits within the project's `Controllers/` folder
3. A string denoting a fully qualified namespaced method, for example `\Myapp\Controllers\Products::someMethod`. Fully qualified names must begin with a backslash.
4. A closure
5. A class method array in the format `array($object, $methodName)`. If the method is static, `$object` can be a string, otherwise it must be an instantiated object.

If the project's default `routeResolver` responds with the name of a controller class, the controller is instantiated and inspected for its own `routeResolver` method. This allows each controller to take charge of its own routing rules, rather than relying on a project-centric approach.

**RouteResolver example**
```php
<?php

namespace Myapp;

use \Frame\Core\RoutesInterface;

class Routes implements RoutesInterface
{

    public function routeResolver(Url $url)
    {

        $found = preg_match("/^\/product/", $url->requestUri, $matches);
        if ($found) {
            return 'Products'; // look in the Products controller
        }
        $found = preg_match("/^\/testing/", $url->requestUri, $matches);
        if ($found) {
            return 'Products::routeDirect'; // Go to a specific method
        }
        $found = preg_match("/^\/model/", $url->requestUri, $matches);
        if ($found) {
            return '\Myapp\Models\Test1::getSomething'; // Should route to a model class instead
        }
        $found = preg_match("/^\/closure/", $url->requestUri, $matches);
        if ($found) {
            return (function(Get $request, Json $response) {
                return 'I am inside a project closure';
            });
        }
        $found = preg_match("/^\/routemethod/", $url->requestUri, $matches);
        if ($found) {
            return array($this, 'routeMethod'); // point to the local method
        }

    }

    public function routeMethod(Get $request)
    {

        return "I am in routemethod";

    }

}
```

## Notes:

1. `Get` and `Twig` classes in the example above are automatically aliased to classes `\Frame\Request\Get` and `\Frame\Response\Twig`.
2. Additional classes can be injected by appending them to the parameter list of a controller method, but must be fully namespaced.
3. If you don't know the output type at compile time, use the generic `Response` class and call the `setType()` method before rendering.
4. The view file is automatically selected based on the controller name and output type. In the example above, the view filename would be /Views/Products/default.html.twig. Each `Response` class has its own fallback method if the view file is not found.

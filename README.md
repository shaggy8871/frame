FRAME
=====

FRAME is a dead-simple PHP framework. Our goals:

1. No bulky installs
2. No yaml or ini configuration files - everything is configurable via PHP itself
3. Heavy use of dependency injection and aliasing to simplify developer interface.

Example:

```
<?php

namespace Controllers;

class Products
{

  /*
   * 'route' methods correspond to URLs
   * Input and output is managed via injected classes. Custom IO classes can be written.
   */
  public function routeDefault(Get $in, Twig $out)
  {

    // Render the product home page using Twig, send through GET variable 'category'
    $out->render(array(
      'category' => $in->category
    ));

  }

}
```

Notes:

1. Get and Twig are automatically aliased to classes \Frame\Request\Get and \Frame\Response\Twig.
2. Additional classes can be injected by appending them to the parameter list of a controller method, but must be fully namespaced.
3. If you don't know the output type at compile time, use the generic Response class and call the setType() method before rendering.

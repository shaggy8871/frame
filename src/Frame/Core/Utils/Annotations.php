<?php

namespace Frame\Core\Utils;

class Annotations
{

    /*
     * Split docblock into annotations
     */
    public static function parseDocBlock($docComments)
    {

        $annotations = [];

        if (preg_match_all('#@(.*?)\n#s', $docComments, $components)) {
        	foreach($components[1] as $annotation) {
        	   list($key, $val) = preg_split('/[ :]+/', $annotation, 2);
        	   $annotations[$key] = $val;
        	}
        }

        return $annotations;

    }

}

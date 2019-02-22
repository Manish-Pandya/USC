<?php

/**
 * Trait intended to coincide with JsonSerializable. Provided functions
 * iterate registered Accessor functions, building an associative array
 * based on Accessor keys.
 *
 * This is functionally similar to how JsonManager builds its resulting
 * 'jsonable values,' but does not recurse into each field.
 */
trait DtoGenerator {
    function jsonSerialize(){
        $overrideMaps = ($this instanceof GenericCrud) ? $this->getEntityMaps() : null;
        $accessors = EntityManager::get_registered_accessors( get_class($this), $overrideMaps );

        $methodsIterator = function() use ($accessors){
            yield 'Class' => get_class($this);
            foreach ($accessors as $fn){
                $name = str_replace('get', '', $fn);
                yield $name => $this->$fn();
            }
        };

        return iterator_to_array($methodsIterator());
    }
}

?>
<?php

/**
 * Helper class for tracking registered Entity types
 */
class RegisteredEntity {
    public $entityClass;
    public $eagerAccessors;
    public function __construct($cls, $eagerAccessors){
        $this->entityClass = $cls;
        $this->setEagerAccessors($eagerAccessors);
    }

    public function apply_entity_maps( $mappings ){
        $this->setEagerAccessors( EntityManager::filter_merge_accessors($this->eagerAccessors, $mappings) );
    }

    function setEagerAccessors( $eagerAccessors ){
        sort($eagerAccessors);
        $LOG = Logger::getLogger('EntityManager.RegisteredEntity.' . $this->entityClass);
        if( $LOG->isTraceEnabled() ){
            $LOG->trace("$this->entityClass:\n  " . implode("\n  ", $eagerAccessors) );
        }

        $this->eagerAccessors = $eagerAccessors;
    }
}

/**
 * Manager class for tracking Entity mappings
 */
class EntityManager {
	public static $_FUNCTION_NAME_BLACKLIST = array(
		'getTableName',
		'getColumnData',
		'getEntityMaps'
    );

    private static $_REGISTERED_TYPES = array();

    public static function get_registered_accessors( &$objectOrClass, Array &$entityMapOverrides = NULL ){
        $cls = null;
        if( is_object($objectOrClass) ){
            $cls = get_class($objectOrClass);
        }
        else{
            $cls = $objectOrClass;
        }

        $type = EntityManager::register_entity_class($cls);
        $registeredAccessors = $type->eagerAccessors;

        // Filter accessors with overrides
        if( isset($entityMapOverrides) ){
            $registeredAccessors = EntityManager::filter_merge_accessors($registeredAccessors, $entityMapOverrides);
        }

        return $registeredAccessors;
    }

    public static function register_entity( &$object ){
        return EntityManager::register_entity_class( get_class($object), $object );
    }

    public static function register_entity_class($classname, &$instance = NULL){
        $LOG = Logger::getLogger(__CLASS__);
        if( !array_key_exists($classname, EntityManager::$_REGISTERED_TYPES) ){
            $LOG->debug("Registering entity class: $classname");

            $refclass = new ReflectionClass($classname);

            if( $refclass->isAbstract() ){
                // Class is abstract; not gonna do this
                return null;
            }

            // Register new type
            $eagerAccessors = EntityManager::get_class_accessors($refclass);
            $type = EntityManager::$_REGISTERED_TYPES[$classname] = new RegisteredEntity($classname, $eagerAccessors);

            $staticDefaultFactory = "$classname::defaultEntityMaps";
            if( is_callable($staticDefaultFactory) ){
                $defaultEntityMaps = call_user_func($staticDefaultFactory);
                if( isset($defaultEntityMaps) ){
                    $LOG->debug("Filtering natural entity accessors of $classname with Static defaults from $staticDefaultFactory");
                    $type->apply_entity_maps($defaultEntityMaps);
                }
            }
        }

        return EntityManager::$_REGISTERED_TYPES[$classname];
    }

    public static function with_entity_maps($classname, $entityMaps){
        if( !isset($entityMaps) ){
            // Nothing to do if there are no mappings
            return false;
        }

        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Get the registered type details (will register new if required)
        $type = EntityManager::register_entity_class($classname);

        // Apply new entity mappings
        $LOG->debug("Filtering existing entity accessors of $classname with provided entityMaps");
        $type->apply_entity_maps($entityMaps);
        return $type;
    }

    public static function merge_entity_maps(...$maps){
        $LOG = Logger::getLogger(__CLASS__);

		// Copy $maps to a new array, $merged
		$merged = array();

        $LOG->trace("Merging " . count($maps) . " sets of EntityMaps");
        foreach($maps as $overrides){
            if( !isset($overrides) ){
                $LOG->trace("Ignore empty array");
                // Skip nulls
                continue;
            }

			// Override or add mappings from $overrides
            $LOG->trace("Merging " . count($overrides) . " EntityMap entries");
			foreach($overrides as $over){
				// is this key already in $merged?
				$ref = null;
				foreach($merged as $em){
					if( $over->getEntityAccessor() == $em->getEntityAccessor() ){
						$ref = $em;
						break;
					}
				}

				if($ref == null){
					// Add
					$merged[] = $over;
				}
				else{
					// Update
					$LOG->trace('Override mapping ' . $ref->getEntityAccessor() . ' ' . $ref->getLoadingType()  . ' => ' . $over->getLoadingType());
					$ref->setLoadingType($over->getLoadingType());
				}
			}
		}

		return $merged;
    }

    public static function filter_merge_accessors(Array &$accessors, Array &$entityMaps){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Init new array
        $newEagers = $accessors;

        // Build a new array of Eager accessors by combining the current value
        //   with the incoming EntityMaps
        $lazyAccessors = array();
        $eagerAccessors = array();

        foreach($entityMaps as $em ){
            $LOG->trace("filter/merge: $em");
            switch( $em->getLoadingType() ){
                case EntityMap::$TYPE_LAZY:
                    $lazyAccessors[] = $em->getEntityAccessor();
                    break;

                default:
                    $LOG->error("Unrecognized EntityMap loading type: " . $em->getLoadingType());
                case EntityMap::$TYPE_EAGER:
                    $eagerAccessors[] = $em->getEntityAccessor();
                    break;
            }
        }

        // Get our existing accessors which are not defined as Lazy by the incoming overrides
        if( count($lazyAccessors) > 0 ){
            $newEagers = array_diff( $accessors, $lazyAccessors );
        }

        // Union our still-existing eager accessors with the incoming Eager overrides
        if( count($eagerAccessors) > 0 ){
            $newEagers = array_unique( array_merge($newEagers, $eagerAccessors));
        }

        if( $LOG->isTraceEnabled() ){
            // Compute differences
            $added = array_diff( $newEagers, $accessors );
            $removed = array_diff( $accessors, $newEagers );

            if( count($added) > 0) {
                $LOG->trace("Added eager accessors: \n  +" . implode("\n  +", $added));
            }

            if( count($removed) > 0){
                $LOG->trace("Removed eager accessors: \n  -" . implode("\n  -", $removed));
            }

            if( (count($removed) + count($added)) == 0 ){
                $LOG->warn("Filter/Merge Override resulted in no changes");
            }

            $stackex = new Exception();
            $LOG->trace("Filter/Merge origin:\n    " . str_replace("\n", "\n    ", $stackex->getTraceAsString()));
        }

        return $newEagers;
    }

    protected static function get_class_accessors( ReflectionClass &$refclass ){
        $accessors = array();
        $refMethods = $refclass->getMethods();

        // Find all get* functions which are not in our blacklist
        foreach( $refMethods as $refMethod ){
            //Make sure function starts with 'get' and not listed in blacklist
            $name = $refMethod->getName();
            if( strstr($name, 'get') && !in_array($name, EntityManager::$_FUNCTION_NAME_BLACKLIST) ){
                // Only register Public accessors
                if( $refMethod->isPublic() ){
                    $accessors[] = $name;
                }
            }
        }

        return $accessors;
    }
}
?>
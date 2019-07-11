<?php
class CacheFactory {
    private static $CACHE_CLASS;

    public static function init( bool $enable_caching ){
        if( !isset(CacheFactory::$CACHE_CLASS) ){
            CacheFactory::$CACHE_CLASS = $enable_caching ? AppCache::class
                                                         : NoOpCache::class;
            Logger::getLogger(__CLASS__)->debug("Initialized caching; type: " . CacheFactory::$CACHE_CLASS);
        }
        else {
            Logger::getLogger(__CLASS__)->warn("Attempt to override configured cache type");
        }
    }

    public static function create( $cache_name ){
        if( !isset(CacheFactory::$CACHE_CLASS) ){
            throw new Exception("Unable to create entity cache; no cache name provided!");
        }

        return new CacheFactory::$CACHE_CLASS($cache_name);
    }

}
?>

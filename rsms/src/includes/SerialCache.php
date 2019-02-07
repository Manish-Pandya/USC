<?php
class SerialCache {
    static $_SERIAL_CACHE = array();
	static $_STATS = array(
		'WRITES' => 0,
		'HITS' => 0,
		'MISSES' => 0,
		'OVERWRITES' => 0,
	);

    /**
     * Generates a simple cache key for the given data
     */
	public static function gen_entity_key($jsonable){
		if( is_array($jsonable) && array_key_exists('Key_id', $jsonable) ){
			return $jsonable['Class'] . ':' . $jsonable['Key_id'];
		}
		else if( $jsonable instanceof GenericCrud) {
			return get_class($jsonable) . ':' . $jsonable->getKey_id();
		}
		else{
			return null;
		}
	}

	public static function cacheSerializedEntity(&$objectVars){
		$LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

		// If this is a cacheable entity (ie: has an ID), then cache it
		$kid = self::gen_entity_key($objectVars);

		if( isset($kid) ){
			if( isset(self::$_SERIAL_CACHE[$kid]) ){
				$LOG->warn("Overwriting cached $kid");
				self::$_STATS['OVERWRITES']++;
			}

			$LOG->debug("Caching $kid");
			self::$_SERIAL_CACHE[$kid] = $objectVars;
			self::$_STATS['WRITES']++;
		}
	}

	public static function getCachedEntity($obj){
		$kid = self::gen_entity_key($obj);
		if( isset($kid) && isset(self::$_SERIAL_CACHE[$kid]) ){
			self::$_STATS['HITS']++;
			return self::$_SERIAL_CACHE[$kid];
		}

		self::$_STATS['MISSES']++;
		return null;
	}

	public static function stats(){
		$LOG = Logger::getLogger(SerialCache::class . '.stats');
		if( $LOG->isDebugEnabled() ){
			$mapped = array_map( function($v, $k){
				return "[$k: $v]";
			}, self::$_STATS, array_keys(self::$_STATS));

			$rlog = RequestLog::describe();
			$LOG->debug( implode(' ',  $mapped) . "[Request : $rlog]");
		}
	}
}

register_shutdown_function(function(){
	SerialCache::stats();
});
?>

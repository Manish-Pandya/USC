<?php
class AppCache {
    private $_CACHE = array();
	private $_STATS = array(
		'WRITES' => 0,
		'HITS' => 0,
		'MISSES' => 0,
		'OVERWRITES' => 0,
	);

	private $name;

    /**
     * Generates a simple cache key for the given data
     */
	public static function gen_entity_key($jsonable){
		if( is_array($jsonable) && array_key_exists('Key_id', $jsonable) ){
			return $jsonable['Class'] . ':' . $jsonable['Key_id'];
		}
		else if( $jsonable instanceof GenericCrud) {
			return self::key_class_id( get_class($jsonable), $jsonable->getKey_id());
		}
		else{
			return null;
		}
	}

	public static function key_class_id($class, $id){
		return "$class:$id";
	}

	public function __construct($name){
		$this->name = $name;
		$instance = $this;
		register_shutdown_function(function() use ($instance){
			$instance->stats();
		});
	}

	public function cacheEntity(&$obj, $key = null){
		$LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

		// If this is a cacheable entity (ie: has an ID), then cache it
		$kid = $key ?? self::gen_entity_key($obj);

		if( isset($kid) ){
			if( isset($this->_CACHE[$kid]) ){
				$LOG->warn("($this->name cache) Overwriting cached $kid");
				$this->_STATS['OVERWRITES']++;
			}

			$LOG->debug("($this->name cache) Caching $kid");
			$this->_CACHE[$kid] = $obj;
			$this->_STATS['WRITES']++;
		}
	}

	public function getCachedEntity($key){
		if( isset($this->_CACHE[$key]) ){
			$this->_STATS['HITS']++;
			return $this->_CACHE[$key];
		}

		$this->_STATS['MISSES']++;
		return null;
	}

	public function stats(){
		$LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
		if( $LOG->isDebugEnabled() ){
			$mapped = array_map( function($v, $k){
				return "[$k: $v]";
			}, $this->_STATS, array_keys($this->_STATS));

			$rlog = RequestLog::describe();
			$LOG->debug("($this->name cache) " . implode(' ',  $mapped) . "[Request : $rlog]");
		}
	}
}

?>

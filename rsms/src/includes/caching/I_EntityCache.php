<?php
interface I_EntityCache {
    public function cacheEntity(&$obj, $key = null);
    public function evict($objectOrKey);
    public function evictAll();
    public function getCachedEntity($key);
}
?>

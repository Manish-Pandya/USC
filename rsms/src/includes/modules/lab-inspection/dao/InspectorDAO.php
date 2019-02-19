<?php
class InspectorDAO extends GenericDAO {

    private static $_INSPECTOR_USER_CACHE;

    public function __construct(){
        parent::__construct(new Inspector());

		if( !isset(self::$_INSPECTOR_USER_CACHE) ){
			self::$_INSPECTOR_USER_CACHE = new AppCache('Inspector-User');
		}
    }

    function getAll($sortColumn = NULL, $sortDescending = false, $activeOnly = false){
        $allInspectors = parent::getAll($sortColumn, $sortDescending, $activeOnly);

        foreach($allInspectors as $insp){
            $cache_key = AppCache::key_class_id(User::class, $insp->getUser_id());
            self::$_INSPECTOR_USER_CACHE->cacheEntity($insp, $cache_key);
        }

        return $allInspectors;
    }

    function getInspectorByUserId($userId){
        $cache_key = AppCache::key_class_id(User::class, $userId);
        $cached = self::$_INSPECTOR_USER_CACHE->getCachedEntity($cache_key);
        if( isset($cached) ){
            return $cached;
        }

        $rel = DataRelationship::fromArray(User::$INSPECTOR_RELATIONSHIP);
        try {
            $q = QueryUtil::selectFrom( $this->modelObject )
                ->where(Field::create($rel->foreignKeyName, $rel->tableName), '=', $userId);

            $insp = $q->getOne();

            if( !$insp ){
                // No such inspector
                $insp = null;
            }

            self::$_INSPECTOR_USER_CACHE->cacheEntity($insp, $cache_key);
            return $insp;
        }
        catch(QueryException $er){
			return new QueryError($er->getMessage());
        }
    }
}
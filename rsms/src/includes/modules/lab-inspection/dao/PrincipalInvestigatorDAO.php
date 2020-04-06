<?php

class PrincipalInvestigatorDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new PrincipalInvestigator());
    }

    public function getByUserId( $userId ){
        $q = QueryUtil::selectFrom(new PrincipalInvestigator())
            ->where(Field::create('user_id', 'principal_investigator'), '=', $userId);

        return $q->getOne();
    }

    /**
     * Retrieves the primary (first) deparmtment of the provided PI
     *
     * @param int|string $piId the ID of the principal investigator
     * @return Department
     */
    public function getPrimaryDepartment( $piId ){
        // Retrieve only the first element
        $depts = $this->getRelatedItemsById(
            $piId,
            DataRelationship::fromArray(PrincipalInvestigator::$DEPARTMENTS_RELATIONSHIP),
            null,
            false,
            false,
            1
        );

        if( count($depts) > 0 ){
            return $depts[0];
        }

        return null;
    }

    private static $PI_BUILDING_ROOM_X_RELATION = array(
        "className"	=>	"PrincipalInvestigatorRoomRelation",
        "tableName"	=>	"principal_investigator_room",
        "sourceTableName" => "room",
        "keyName"	=>	"key_id",
        "foreignKeyName" =>	"room_id"
    );

    public function getBuildings( $piId ){
        try{
            // Select all buildings from the PI's Room relations
            $q = QueryUtil::selectFrom(new Building())
                ->joinTo(DataRelationship::fromArray(Building::$ROOMS_RELATIONSHIP))
                ->joinTo(DataRelationship::fromArray(self::$PI_BUILDING_ROOM_X_RELATION))
                ->groupBy(Field::create('key_id', 'building'))
                ->where(Field::create('principal_investigator_id', 'principal_investigator_room'), '=', $piId);

            $piBuildings = $q->getAll();
            return $piBuildings;
        }
        catch(QueryException $er){
			return new QueryError($er->getMessage());
        }
    }

    public function getRooms( $piId ){
        return $this->getRelatedItemsById(
            $piId, DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
    }

    public function getRoomsInBuilding($piId, $buildingId){
        try{

            $q = $this->_buildQueryFor_getRelatedItemsById($piId, DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
            $q->where(Field::create('building_id', 'room'), '=', $buildingId, PDO::PARAM_INT);
            $q->groupBy(Field::create('key_id', 'room'));

			$result = $q->getAll();

			if( $this->LOG->isTraceEnabled() ){
				$cnt = is_array($result) ? count($result) : $result != null ? 1 : 0;
				$this->LOG->trace("Result count: $cnt");
			}

			return $result;
		}
		catch(QueryException $er){
			return new QueryError($er->getMessage());
		}
    }

    public function getByDepartment( $department_id, $active_only = true ){
        $_pi_x_pidept = DataRelationship::fromArray([
            "className"	=>	Department::class,
            "tableName"	=>	"principal_investigator_department",
            "keyName"	=>	"key_id",
            "foreignKeyName" =>	"principal_investigator_id"
        ]);

        return QueryUtil::selectFrom( new PrincipalInvestigator() )
            ->joinTo( $_pi_x_pidept )
            ->where( Field::create('is_active', 'principal_investigator'), '=', TRUE)
            ->where( Field::create('department_id', 'principal_investigator_department'), '=', $department_id)
            ->getAll();
    }
}
?>
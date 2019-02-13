<?php

class PrincipalInvestigatorDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new PrincipalInvestigator());
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

    public function getRooms( $piId ){
        return $this->getRelatedItemsById(
            $piId, DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
    }

    public function getRoomsInBuilding($piId, $buildingId){
        try{

            $q = $this->_buildQueryFor_getRelatedItemsById($piId, DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
            $q->where(Field::create('building_id', 'room', '=', $buildingId, PDO::PARAM_INT));

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
}
?>
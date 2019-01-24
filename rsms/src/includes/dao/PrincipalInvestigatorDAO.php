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
}
?>
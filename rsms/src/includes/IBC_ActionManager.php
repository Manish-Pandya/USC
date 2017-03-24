
<?php
/**
 * Contains action functions specific to the Committees module.
 *
 * If a non-fatal error occurs, should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 *
 * @author Matt Breeden
 */
class IBC_ActionManager extends ActionManager {


    /*****************************************************************************\
     *                            Get Functions                                  *
    \*****************************************************************************/

    /*
     * Filters protocols and relevant data based on whether currently signed in user has Admin permissions or persmissions to retrieve and save protcols from a single lab
     */
    private function filterByPermission(){

    }

	public function getAllProtocols(){
		$dao = $this->getDao(new IBCProtocol());
		return $dao->getAll();
	}

	/*
     * @param int $id
     *
     */
	public function getProtocolById( $id = NULL ){

		if($id == NULL){
			$id = $this->getValueFromRequest('id', $id);
		}

		if( $id !== NULL ){
			$dao = $this->getDao(new IBCProtocol());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided");
		}

	}

	public function saveProtocol( IBCProtocol $decodedObject = NULL){
		$LOG = Logger::getLogger('Action:' . __function__);
		if( $decodedObject === NULL ){
			$decodedObject = $this->convertInputJson();
		}
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Deficiency');
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new IBCProtocol());
			$decodedObject = $dao->save($decodedObject);
			$LOG->fatal($decodedObject);
			return $decodedObject;
		}
	}
    /**
     * Summary of getAllProtocolRevisions
     * @return array
     */
    function getAllProtocolRevisions(){
        //TODO: restrict revisions to only those of protocols that belong to user
        $dao = $this->getDao(new IBCProtocolRevision());
        return $dao->getAll();
    }
    /**
     * @param integer $id
     * @return GenericCrud | IBCProtocolRevision | ActionError
     */
    function getProtocolRevisionById($id = null){
        if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCProtocolRevision());
        return $dao->getById($id);
    }
    /**
     * @param IBCProtocolRevision
     * @return GenericCrud | ActionError | IBCProtocolRevision
     */
    function saveProtocolRevision(IBCProtocolRevision $decodedObject = null){
		$l = Logger::getLogger("save revision");
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        //hold Preliminary and Primary reviewers

		$primaryReviewers = $decodedObject->getPrimaryReviewers();
        $preliminaryReviewers = $decodedObject->getPreliminaryReviewers();
        $dao = $this->getDao($decodedObject);
        $revision = $dao->save($decodedObject);

        foreach($revision->getPrimaryReviewers() as $reviewer){
            if(is_array($reviewer))$reviewer = JsonManager::assembleObjectFromDecodedArray($reviewer);
            $dao->removeRelatedItems($reviewer->getKey_id(), $revision->getKey_id(), DataRelationship::fromArray(IBCProtocolRevision::$PRIMARY_REVIEWERS_RELATIONSHIP));
        }

        foreach($primaryReviewers as $reviewer){
            if(is_array($reviewer))$reviewer = JsonManager::assembleObjectFromDecodedArray($reviewer);
            $dao->addRelatedItems($reviewer->getKey_id(), $revision->getKey_id(), DataRelationship::fromArray(IBCProtocolRevision::$PRIMARY_REVIEWERS_RELATIONSHIP));
        }

        foreach($revision->getPreliminaryReviewers() as $reviewer){
            if(is_array($reviewer))$reviewer = JsonManager::assembleObjectFromDecodedArray($reviewer);
            $dao->removeRelatedItems($reviewer->getKey_id(), $revision->getKey_id(), DataRelationship::fromArray(IBCProtocolRevision::$PRELIMINARY_REVIEWERS_RELATIONSHIP));
        }

        foreach($preliminaryReviewers as $reviewer){
            if(is_array($reviewer))$reviewer = JsonManager::assembleObjectFromDecodedArray($reviewer);
            $dao->addRelatedItems($reviewer->getKey_id(), $revision->getKey_id(), DataRelationship::fromArray(IBCProtocolRevision::$PRELIMINARY_REVIEWERS_RELATIONSHIP));
        }

		$revision->setPreliminaryReviewers(null);
		$revision->setPrimaryReviewers(null);

        $entityMaps = array();
        $entityMaps[] = new EntityMap("eager","getPreliminaryReviewers");
        $entityMaps[] = new EntityMap("eager","getPrimaryReviewers");
		$revision->setEntityMaps($entityMaps);
        return $revision;
    }

    public function saveProtocolRevisions(array $decodedObject = null){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        if(!is_array($decodedObject))return new ActionError("Not an array");
        $savedRevisions = array();
        foreach($decodedObject as $revision){
            if(is_array($revision))$revision = JsonManager::assembleObjectFromDecodedArray($revision);
            $savedRevisions[] = $this->saveProtocolRevision($revision);
        }
        return $savedRevisions;
    }

    /**
     * Summary of getAllIBCSections
     * @return array
     */
    function getAllIBCSections(){
        //TODO: restrict revisions to only those of protocols that belong to user
        $dao = $this->getDao(new IBCSection());
        return $dao->getAll();
    }
    /**
     * @param integer $id
     * @return GenericCrud | IBCSection | ActionError
     */
    function getIBCSectionById($id = null){
        if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCSection());
        return $dao->getById($id);
    }
    /**
     * @param IBCSection
     * @return GenericCrud | ActionError | IBCQuestion
     */
    function saveIBCSection($decodedObject = null){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);
        $s = $dao->save($decodedObject);
        return $s;
    }

    /**
     * Summary of getAllIBCQuestions
     * @return array
     */
    function getAllIBCQuestions(){
        //TODO: restrict revisions to only those of protocols that belong to user
        $dao = $this->getDao(new IBCQuestion());
        return $dao->getAll();
    }
    /**
     * @param integer $id
     * @return GenericCrud | IBCQuestion | ActionError
     */
    function getIBCQuestionById($id = null){
        if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCQuestion());
        return $dao->getById($id);
    }
    /**
     * @param IBCQuestion
     * @return GenericCrud | ActionError | IBCQuestion
     */
    function saveIBCQuestion($decodedObject = null){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);
        $q = $dao->save($decodedObject);
        return $q;
    }

    /**
	 * Summary of getAllIBCPossibleAnswers
     * @return array
     */
    function getAllIBCPossibleAnswers(){
        //TODO: restrict revisions to only those of protocols that belong to user
        $dao = $this->getDao(new IBCPossibleAnswer());
        return $dao->getAll();
    }
    /**
     * @param integer $id
     * @return GenericCrud | IBCPossibleAnswer | ActionError
     */
    function getIBCPossibleAnswerById($id = null){
        if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCPossibleAnswer());
        return $dao->getById($id);
    }
    /**
     * @param IBCPossibleAnswer
	 * @return GenericCrud | ActionError | IBCPossibleAnswer
     */
    function saveIBCPossibleAnswer($decodedObject = null){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);
        $a = $dao->save($decodedObject);
        return $a;
    }

    /**
     * Summary of getAllIBCResponses
     * @return array
     */
    function getAllIBCResponses(){
        //TODO: restrict revisions to only those of protocols that belong to user
        $dao = $this->getDao(new IBCResponse());
        return $dao->getAll();
    }
    /**
     * @param integer $id
     * @return GenericCrud | IBCResponse | ActionError
     */
    function getIBCResponseById($id = null){
        if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCResponse());
        return $dao->getById($id);
    }
    /**
     * @param Array
     * @return Array | ActionError | IBCResponse
     */
    function saveIBCResponse($decodedObject = null){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);
        foreach($decodedObject as $response){
            $responseDao = new GenericDAO($response);
            $question = $this->getQuestionById($response->getQuestion_id());
            if($question->getAnswer_type() == IBCQuestion::$ANSWER_TYPES["MULTIPLE_CHOICE"]){
                //find and update all relevant responses

                $existingResponses = $this->getSiblingReponses($response);
                foreach($existingResponses as $r){
                    $r->setIs_selected(false);
                    $r = $responseDao->save($r);
                }
                $response->setIs_selected(true);
                $response = $responseDao->save($response);
            }
        }
        $response = $dao->save($response);
        return $this->getSiblingReponses($response);
    }

    function saveIBCResponses($decodedObject = null){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        foreach($decodedObject as $response){
            $responseDao = new GenericDAO($response);
            $question = $this->getIBCQuestionById($response->getQuestion_id());
            if($question->getAnswer_type() == IBCQuestion::$ANSWER_TYPES["MULTIPLE_CHOICE"]){
                //find and update all relevant responses
                $existingResponses = $this->getSiblingReponses($response);
                foreach($existingResponses as $r){
                    $r->setIs_selected(false);
                    $r = $responseDao->save($r);
                }

				//in the case of multiple choice answers, we send an array of one IBCResponse from the client,
				//so we can safely set $response's Is_selected to true, knowing that it is the IBCResponse from the client
                $response->setIs_selected(true);
                $response = $responseDao->save($response);
            }else{
				$response->setText("butt");
			}
            $response = $responseDao->save($response);
        }
        return $this->getSiblingReponses($decodedObject[0]);
    }

    private function getSiblingReponses(IBCResponse $r){
        $responseDao = new GenericDAO($r);
        $whereClauseGroup = new WhereClauseGroup(
			array(
				new WhereClause('question_id','=', $r->getQuestion_id()),
				new WhereClause('revision_id','=',$r->getRevision_id())
			)
        );
        $responses = $responseDao->getAllWhere($whereClauseGroup);
        return $responses;
    }

    public function getAllIBCPIs(){
        $LOG = Logger::getLogger( 'Action:' . __function__ );


        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAll();
        /** TODO: Instead of $dao->getAll, we gather PIs which are either active or have rooms associated with them. **/
        /* $whereClauseGroup = new WhereClauseGroup( array( new WhereClause("is_active","=","1"), new WhereClause("key_id","IN","(SELECT principal_investigator_id FROM principal_investigator_room)") ) );
        $pis = $dao->getAllWhere($whereClauseGroup, "OR");*/

        $entityMaps = array();
        $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
        $entityMaps[] = new EntityMap("lazy","getRooms");
        $entityMaps[] = new EntityMap("lazy","getDepartments");
        $entityMaps[] = new EntityMap("lazy","getUser");
        $entityMaps[] = new EntityMap("lazy","getInspections");
        $entityMaps[] = new EntityMap("lazy","getPi_authorization");
        $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
        $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
        $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
        $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
        $entityMaps[] = new EntityMap("lazy", "getPickups");
        $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
        $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
        $entityMaps[] = new EntityMap("lazy","getOpenInspections");
        $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
        $entityMaps[] = new EntityMap("lazy","getVerifications");
        $entityMaps[] = new EntityMap("lazy","getBuidling");
        $entityMaps[] = new EntityMap("lazy","getWipeTests");
        $entityMaps[] = new EntityMap("lazy","getCurrentPi_authorization");
        $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
        $entityMaps[] = new EntityMap("lazy", "getCurrentIsotopeInventories");

        foreach($pis as $pi){
            $pi->setEntityMaps($entityMaps);
        }
        
        return $pis;
    }
}

?>
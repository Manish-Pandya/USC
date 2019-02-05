
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

			if( $decodedObject->getKey_id() != null && $this->getProtocolById($decodedObject->getKey_id()) != null &&  $decodedObject->getPrincipalInvestigators() != null){
				$oldProtocol = $this->getProtocolById($decodedObject->getKey_id());
				$oldPis = $oldProtocol->getPrincipalInvestigators();
			}

			$newPis = $decodedObject->getPrincipalInvestigators();
			$decodedObject = $dao->save($decodedObject);


			if ($decodedObject->getIBCProtocolRevisions() == null) {
				$revision = new IBCProtocolRevision();
				$revision->setProtocol_type("NEW");
				$revision->setProtocol_id( $decodedObject->getKey_id() );
				$revision->setRevision_number(0);
				$revision->setIs_active(true);
				$dao = $this->getDao($revision);
				$revision = $dao->save($revision);
			}
			$LOG->fatal($decodedObject);

			if($oldPis != null){
				foreach($oldPis as $pi){
					$dao->removeRelatedItems($pi->getKey_id(), $oldProtocol->getKey_id(), DataRelationship::fromArray(IBCProtocol::$PIS_RELATIONSHIP));
				}
			}

			if($newPis != null){
				foreach($newPis as $pi){
					if(is_array($pi))$pi = JsonManager::assembleObjectFromDecodedArray($pi);
					$dao->addRelatedItems($pi->getKey_id(), $decodedObject->getKey_id(), DataRelationship::fromArray(IBCProtocol::$PIS_RELATIONSHIP));
				}
			}

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
    function saveProtocolRevision(IBCProtocolRevision $decodedObject = null, $cloneIfReturnedForRevision = true){
		$l = Logger::getLogger(__FUNCTION__);
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        //hold Preliminary and Primary reviewers
		$primaryReviewers = $decodedObject->getPrimaryReviewers();
        $preliminaryReviewers = $decodedObject->getPreliminaryReviewers();
        $protocolEditors = $decodedObject->getProtocolFillOutUsers();

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

		foreach($revision->getProtocolFillOutUsers() as $reviewer){
            if(is_array($reviewer))$reviewer = JsonManager::assembleObjectFromDecodedArray($reviewer);
            $dao->removeRelatedItems($reviewer->getKey_id(), $revision->getKey_id(), DataRelationship::fromArray(IBCProtocolRevision::$PROTOCOL_FILLOUT_USERS_RELATIONSHIP));
        }

        foreach($protocolEditors as $reviewer){
            if(is_array($reviewer))$reviewer = JsonManager::assembleObjectFromDecodedArray($reviewer);
            $dao->addRelatedItems($reviewer->getKey_id(), $revision->getKey_id(), DataRelationship::fromArray(IBCProtocolRevision::$PROTOCOL_FILLOUT_USERS_RELATIONSHIP));
        }

		//Protocol's current IBCProtocolRevision has been returned for revision, to be revised for revisions
		if($revision->getStatus() === IBCProtocolRevision::$STATUSES["RETURNED_FOR_REVISION"] && $cloneIfReturnedForRevision){
			$newRevision = clone($revision);//new IBCProtocolRevision()
			$this->purgeKeyIds($newRevision);
			$newRevision->setPreliminaryReviewers($preliminaryReviewers);
			$newRevision->setPrimaryReviewers($primaryReviewers);
			$newRevision->setProtocolFillOutUsers($protocolEditors);
			$responses = $revision->getIBCResponses();
			$preComments = $revision->getIBCPreliminaryComments();
			//TODO: get our primary comments and save them after we write the other stuff for that thing
			//$primaryComments = $newRevision->getIBCPriminaryComments();
			$newRevision->setRevision_number(intval ($revision->getRevision_number()) + 1);
			$newRevision = $this->saveProtocolRevision($newRevision, false);

			foreach($responses as $response){
				$response->setKey_id(null);
				$response->setKey_id(null);
				$response->setRevision_id($newRevision->getKey_id());
				$response = $this->saveIBCResponses(array($response));
			}

			foreach($preComments as $comment){
				$comment->setKey_id(null);
				$comment->setRevision_id($newRevision->getKey_id());
				$comment = $this->saveIBCPreliminaryComment($comment);
			}

			//TODO: get our primary comments and save them after we write the other stuff for that thing
			/*
			foreach($primaryComments as $comment){
				$comment->getRevision_id($newRevision->getKey_id());
				$comment = $this->saveIBCPiminaryComment($comment);
			}
			*/

			return array($newRevision,$revision);
		}

		$revision->setPreliminaryReviewers(null);
		$revision->setPrimaryReviewers(null);
		$entityMaps = array();
        $entityMaps[] = EntityMap::eager("getPreliminaryReviewers");
        $entityMaps[] = EntityMap::eager("getPrimaryReviewers");
		$revision->setEntityMaps($entityMaps);

		//TODO: use getIbcEmailByStatus to determine if an email, and if so, which, should be sent
		//if($revision->getStatus() == IBCProtocolRevision::$STATUSES["SUBMITTED"]){
			$l->fatal($revision);

			$emailGen = $this->getIBCEmailGenById(6);
			$emailGen->setRevision($revision);
			$l->fatal($emailGen);
		//}

        return $revision;
    }

	//TODO: write simple factory to get proper IBCEmail gen by status. refactor title column in email_madlib to match status column
	public function getIbcEmailByStatus($status){

	}

	/*
	 * RECURSIVELY PURGE KEY_IDS FROM OBJECT TREE FOR FRESH SAVES
	 * @param GenericCrud $currentObject
	 *
	 */
	private function purgeKeyIds(GenericCrud $currentObject){
		$keys = get_class_methods($currentObject);
		if(count($keys) > 0){
			if(method_exists($currentObject, "getKey_id") && $currentObject->hasPrimaryKeyValue()){
				$currentObject->setKey_id(null);
			}

			foreach($keys as $method){
				//only call getters
				if(stristr($method,"get")){
					if(is_array($currentObject->$method())){
						foreach($currentObject->$method() as $sub){
							$this->purgeKeyIds($sub);
						}
					}else{
						$this->purgeKeyIds($currentObject->$method());
					}
				}
			}
		}

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

        $l = Logger::getLogger(__FUNCTION__);

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );


        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAll();
        /** TODO: Instead of $dao->getAll, we gather PIs which are either active or have rooms associated with them. **/
        /* $whereClauseGroup = new WhereClauseGroup( array( new WhereClause("is_active","=","1"), new WhereClause("key_id","IN","(SELECT principal_investigator_id FROM principal_investigator_room)") ) );
        $pis = $dao->getAllWhere($whereClauseGroup, "OR");*/

        $entityMaps = array();
        $entityMaps[] = EntityMap::lazy("getLabPersonnel");
        $entityMaps[] = EntityMap::lazy("getRooms");
        $entityMaps[] = EntityMap::eager("getDepartments");
        $entityMaps[] = EntityMap::lazy("getUser");
        $entityMaps[] = EntityMap::lazy("getInspections");
        $entityMaps[] = EntityMap::lazy("getPi_authorization");
        $entityMaps[] = EntityMap::lazy("getActiveParcels");
        $entityMaps[] = EntityMap::lazy("getCarboyUseCycles");
        $entityMaps[] = EntityMap::lazy("getPurchaseOrders");
        $entityMaps[] = EntityMap::lazy("getSolidsContainers");
        $entityMaps[] = EntityMap::lazy("getPickups");
        $entityMaps[] = EntityMap::lazy("getScintVialCollections");
        $entityMaps[] = EntityMap::lazy("getCurrentScintVialCollections");
        $entityMaps[] = EntityMap::lazy("getOpenInspections");
        $entityMaps[] = EntityMap::lazy("getQuarterly_inventories");
        $entityMaps[] = EntityMap::lazy("getVerifications");
        $entityMaps[] = EntityMap::lazy("getBuidling");
        $entityMaps[] = EntityMap::lazy("getWipeTests");
        $entityMaps[] = EntityMap::lazy("getCurrentPi_authorization");
        $entityMaps[] = EntityMap::lazy("getCurrentVerifications");
        $entityMaps[] = EntityMap::lazy("getCurrentIsotopeInventories");

        foreach($pis as $pi){
            $pi->setEntityMaps($entityMaps);
        }

        return $pis;
    }

    /**
     * Summary of getAllIBCPreliminaryComments
     * @return array
     */
    function getAllIBCPreliminaryComments(){
        //TODO: restrict revisions to only those of protocols that belong to user
        $dao = $this->getDao(new IBCPreliminaryComment());
        return $dao->getAll();
    }
    /**
     * @param integer $id
     * @return GenericCrud | IBCPreliminaryCommentById | ActionError
     */
    function getIBCPreliminaryCommentById($id = null){
        if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCPreliminaryComment());
        return $dao->getById($id);
    }

    public function saveIBCPreliminaryComment(IBCPreliminaryComment $decodedObject){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);
        return $dao->save($decodedObject);
    }

	/**
	 * Summary of getAllIBCPrimaryComments
	 * @return array
	 */
    function getAllIBCPrimaryComments(){
        //TODO: restrict revisions to only those of protocols that belong to user
        $dao = $this->getDao(new IBCPrimaryComment());
        return $dao->getAll();
    }

    /**
	 * @param integer $id
	 * @return GenericCrud | IBCPrimaryCommentById | ActionError
	 */
    function getIBCPrimaryCommentById($id = null){
        if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCPrimaryComment());
        return $dao->getById($id);
    }

    public function saveIBCPrimaryComment(IBCPrimaryComment $decodedObject){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);
        return $dao->save($decodedObject);
    }

	// IBC Email Mgmt functions //
	//////////////////////////////

	public function getAllIBCEmails() {
		$dao = $this->getDao(new IBCEmailGen());
        $whereClauseGroup = new WhereClauseGroup(
			array(
				new WhereClause('module','=', IBCEmailGen::$MODULE_NAME)
			)
        );
        $responses = $dao->getAllWhere($whereClauseGroup);
        return $responses;
	}

	public function getIBCEmailGenById($id = null){
		if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCEmailGen());
        return $dao->getById($id);
	}

	public function saveIBCEmailGen(IBCEmailGen $decodedObject){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);
        return $dao->save($decodedObject);
    }

	public function getPreviewCorpus($decodedObject = null){
		if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
		return $decodedObject->parse($decodedObject->getCorpus());
	}

	// IBC Meetings Mgmt functions //
	//////////////////////////////

	public function getAllIBCMeetings() {
		$dao = $this->getDao(new IBCMeeting());
        return $dao->getAll();
	}

	public function getIBCMeetingById($id = null){
		if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCMeeting());
        return $dao->getById($id);
	}

	public function saveIBCMeeting(IBCMeeting $decodedObject){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);

		$oldMeeting = $this->getIBCMeetingById($decodedObject->getKey_id());
		if($oldMeeting != null){
			$oldAttendees = $oldMeeting->getAttendees();
			foreach($oldAttendees as $attendee){
				$dao->removeRelatedItems($attendee->getKey_id(), $oldMeeting->getKey_id(), DataRelationship::fromArray(IBCMeeting::$ATTENDEES_RELATIONSHIP));
			}
		}

		$newAttendees = $decodedObject->getAttendees();
		$decodedObject = $dao->save($decodedObject);
		foreach($newAttendees as $attendee){
			if(is_array($attendee))$attendee = JsonManager::assembleObjectFromDecodedArray($attendee);
			$dao->addRelatedItems($attendee->getKey_id(), $decodedObject->getKey_id(), DataRelationship::fromArray(IBCMeeting::$ATTENDEES_RELATIONSHIP));
		}

		if ($decodedObject->getProtocolRevisions() == null) {
			$revision = new IBCProtocolRevision();
			$revision->setProtocol_type("NEW");
			$revision->setProtocol_id( $decodedObject->getKey_id() );
			$revision->setRevision_number(0);
			$revision->setIs_active(true);
			$dao = $this->getDao($revision);
			$revision = $dao->save($revision);
		}

        return $dao->save($decodedObject);
    }

	public function getIBCPossibleMeetingAttendees(){
		$users = $this->getAllUsers();

		$users = array_filter($users,
			/**
			  * @var User
			  */
			function($user) {
				foreach($user->getRoles() as $role) {
					if (stristr($role->getName(), "ibc")) return true;
				}
				return false;
			});
		return $users;
	}
}

?>
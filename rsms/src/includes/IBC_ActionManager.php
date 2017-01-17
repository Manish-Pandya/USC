
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
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        //hold Preliminary and Primary reviewers
        $decodedObject = new IBCProtocolRevision();
        $primaryReviewers = $decodedObject->getPrimaryReviewers();
        $preliminaryReviewers = $decodedObject->getPreliminaryReviewers();
        $dao = $this->getDao($decodedObject);
        $revision = $dao->save($decodedObject);

        foreach($revision->getPrimaryReviewers() as $reviwer){
            if(is_array($reviwer))$reviwer = JsonManager::assembleObjectFromDecodedArray($reviwer);
            $dao->removeRelatedItems($revision->getKey_id(), $reviwer->getKey_id(), IBCProtocolRevision::$PRIMARY_REVIEWERS_RELATIONSHIP);
        }

        foreach($primaryReviewers as $reviwer){
            if(is_array($reviwer))$reviwer = JsonManager::assembleObjectFromDecodedArray($reviwer);
            $dao->addRelatedItems($revision->getKey_id(), $reviwer->getKey_id(), IBCProtocolRevision::$PRIMARY_REVIEWERS_RELATIONSHIP);
        }

        foreach($revision->getPreliminaryReviwers() as $reviwer){
            if(is_array($reviwer))$reviwer = JsonManager::assembleObjectFromDecodedArray($reviwer);
            $dao->removeRelatedItems($revision->getKey_id(), $reviwer->getKey_id(), IBCProtocolRevision::$PRELIMINARY_REVIEWERS_RELATIONSHIP);
        }

        foreach($preliminaryReviewers as $reviwer){
            if(is_array($reviwer))$reviwer = JsonManager::assembleObjectFromDecodedArray($reviwer);
            $dao->addRelatedItems($revision->getKey_id(), $reviwer->getKey_id(), IBCProtocolRevision::$PRELIMINARY_REVIEWERS_RELATIONSHIP);
        }

        return $revision;
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
     * Summary of getAllIBCAnswers
     * @return array
     */
    function getAllIBCAnswers(){
        //TODO: restrict revisions to only those of protocols that belong to user
        $dao = $this->getDao(new IBCAnswer());
        return $dao->getAll();
    }
    /**
     * @param integer $id
     * @return GenericCrud | IBCAnswer | ActionError
     */
    function getIBCAnswerById($id = null){
        if($id == NULL)$id = $this->getValueFromRequest('id', $id);
        if($id == NULL)return new ActionError("No request param 'id' provided.");
        $dao = $this->getDao(new IBCAnswer());
        return $dao->getById($id);
    }
    /**
     * @param IBCAnswer
     * @return GenericCrud | ActionError | IBCAnswer
     */
    function saveIBCAnswer($decodedObject = null){
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
     * @param IBCResponse
     * @return GenericCrud | ActionError | IBCResponse
     */
    function saveIBCResponse($decodedObject = null){
        if($decodedObject == NULL)$decodedObject = $this->convertInputJson();
        if($decodedObject == NULL)return new ActionError("No input read from stream");
        $dao = $this->getDao($decodedObject);
        $r = $dao->save($decodedObject);
        return $r;
    }
}

?>
<?php
class AuthSecurity {

    public static function userIsCandidate(){
        return AuthManager::hasCandidateUser();
    }

    public static function candidateCanSubmitNewRequest(){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        if( !self::userIsCandidate() ){
            $LOG->debug("Current user is not a Candidate");
            return false;
        }

        // This candidate can only submit a new request if they have no PENDING requests

        // Get requests for this candidate
        $candidate = AuthManager::getCandidateUser();
        $reqDao = new UserAccessRequestDAO();
        $pendingRequests = $reqDao->getByNetworkUsername( $candidate->getUsername(), UserAccessRequest::STATUS_PENDING );

        // Count them
        if( empty($pendingRequests) ){
            $LOG->debug("$candidate has no PENDING requests");
            return true;
        }
        else {
            $LOG->debug("$candidate has " . count($pendingRequests) . " PENDING requests");
            return false;
        }
    }
}
?>

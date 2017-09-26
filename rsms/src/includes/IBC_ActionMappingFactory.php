<?php
/**
 * Class that wraps a static accessor that returns all Committees Module Action Mappings
 *
 * @author Matt Breeden
 */
class IBC_ActionMappingFactory extends ActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new IBC_ActionMappingFactory();

		return $mappings->getConfig();

	}

	public function getConfig() {
		return array(
				"getAllProtocols" 				=> new ActionMapping("getAllProtocols", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getProtocolById" 				=> new ActionMapping("getProtocolById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveProtocol" 					=> new ActionMapping("saveProtocol", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB "]),

                "getAllProtocolRevisions" 		=> new ActionMapping("getAllProtocolRevisions", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getProtocolRevisionById" 		=> new ActionMapping("getProtocolRevisionById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveProtocolRevision" 			=> new ActionMapping("saveProtocolRevision", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
                "saveProtocolRevisions" 		=> new ActionMapping("saveProtocolRevisions", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllIBCSections" 			=> new ActionMapping("getAllIBCSections", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getIBCSectionById" 			=> new ActionMapping("getIBCSectionById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveIBCSection" 				=> new ActionMapping("saveIBCSection", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllProtocols" 				=> new ActionMapping("getAllProtocols", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getProtocolById" 				=> new ActionMapping("getProtocolById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveProtocol" 					=> new ActionMapping("saveProtocol", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllIBCQuestions" 			=> new ActionMapping("getAllIBCQuestions", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getIBCQuestionById" 			=> new ActionMapping("getIBCQuestionById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveIBCQuestion" 				=> new ActionMapping("saveIBCQuestion", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllIBCPossibleAnswers" 				=> new ActionMapping("getAllIBCPossibleAnswers", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getIBCPossibleAnswerById" 				=> new ActionMapping("getIBCPossibleAnswerById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveIBCPossibleAnswer" 				=> new ActionMapping("saveIBCPossibleAnswer", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllIBCResponses" 			=> new ActionMapping("getAllIBCResponses", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getIBCResponseById" 			=> new ActionMapping("getIBCResponseById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveIBCResponse" 				=> new ActionMapping("saveIBCResponse", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
                "saveIBCResponses" 				=> new ActionMapping("saveIBCResponses", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),


                "getAllProtocols" 				=> new ActionMapping("getAllProtocols", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getProtocolById" 				=> new ActionMapping("getProtocolById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"saveProtocol" 					=> new ActionMapping("saveProtocol", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllDepartments" 			=> new ActionMapping("getAllDepartments", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllIBCPIs"	 				=> new ActionMapping("getAllIBCPIs", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),

                "getAllIBCPreliminaryComments"  => new ActionMapping("getAllIBCPreliminaryComments", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "getIBCPreliminaryCommentById"  => new ActionMapping("getIBCPreliminaryCommentById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "saveIBCPreliminaryComment"     => new ActionMapping("saveIBCPreliminaryComment", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),

				"getAllIBCPrimaryComments"  => new ActionMapping("getAllIBCPrimaryComments", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "getIBCPrimaryCommentById"  => new ActionMapping("getIBCPrimaryCommentById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "saveIBCPrimaryComment"     => new ActionMapping("saveIBCPrimaryComment", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),

				"testEmailGen"     => new ActionMapping("testEmailGen", "", "" ),
				"getAllIBCEmails"     => new ActionMapping("getAllIBCEmails", "", "", $this::$ROLE_GROUPS["ADMIN"] ),
				"saveIBCEmailGen"     => new ActionMapping("saveIBCEmailGen", "", "", $this::$ROLE_GROUPS["ADMIN"] ),
		);
	}
}
?>

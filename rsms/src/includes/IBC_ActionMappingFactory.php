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

                "getAllIBCSections" 			=> new ActionMapping("getAllIBCSections", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getIBCSectionById" 			=> new ActionMapping("getIBCSectionById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveIBCSection" 				=> new ActionMapping("saveIBCSection", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllProtocols" 				=> new ActionMapping("getAllProtocols", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getProtocolById" 				=> new ActionMapping("getProtocolById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveProtocol" 					=> new ActionMapping("saveProtocol", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllIBCQuestions" 			=> new ActionMapping("getAllIBCQuestions", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getIBCQuestionById" 			=> new ActionMapping("getIBCQuestionById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveIBCQuestion" 				=> new ActionMapping("saveIBCQuestion", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllIBCAnswers" 				=> new ActionMapping("getAllIBCAnswers", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getIBCAnswerById" 				=> new ActionMapping("getIBCAnswerById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveIBCAnswer" 				=> new ActionMapping("saveIBCAnswer", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllIBCResponses" 			=> new ActionMapping("getAllIBCResponses", "", "", $this::$ROLE_GROUPS["IBC_COMMITTEE"] ),
				"getIBCResponseById" 			=> new ActionMapping("getIBCResponseById", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),
				"saveIBCResponse" 				=> new ActionMapping("saveIBCResponse", "", "", $this::$ROLE_GROUPS["IBC_AND_LAB"] ),

                "getAllProtocols" 				=> new ActionMapping("getAllProtocols", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getProtocolById" 				=> new ActionMapping("getProtocolById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"saveProtocol" 					=> new ActionMapping("saveProtocol", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllDepartments" 			=> new ActionMapping("getAllDepartments", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllPIs"	 					=> new ActionMapping("getAllPIs", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
		);
	}
}
?>
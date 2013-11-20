<?php

//TODO: Remove this class when real DAO functionality is completed
class MockDAO{
	
	protected $LOG;
	
	public function __construct(){
		$this->LOG = Logger::getLogger(__CLASS__);
	}
	
	private function getRandomKey(){
		return mt_rand(0, 9999);
	}
	
	public function save( GenericCrud &$obj ){
		$this->LOG->info("TODO: SAVE $obj");
		
		if( $obj->getKeyId() === NULL ){
			//Assign random key for now
			$obj->setKeyId( mt_rand(0, $this->getRandomKey() ) );
		}
		
		//passed by reference; no need to return (for now)
	}
	
	public function getUserById( $keyid ){
		$user = new User();
		$user->setIsActive(TRUE);
		$user->setEmail("user$keyid@host.com");
		$user->setName("User #$keyid");
		$user->setUsername("user$keyid");
		$user->setKeyId($keyid);
		
		$this->LOG->info("Defined User: $user");
		
		return $user;
	}
	
	public function getChecklistById( $keyid ){
		$checklist = new Checklist();
		$checklist->setKeyId($keyid);
		
		$this->LOG->info("Defined Checklist: $checklist");
		
		return $checklist;
	}
	
	public function getHazardById( $keyid ){
		$hazard = new Hazard();
		$hazard->setKeyId($keyid);
		$hazard->setName("Dangerous thing #$keyid");
		
		// Conditionally build subhazard(s)
		$randomChance = 6;
		if( mt_rand(0, $randomChance) === $randomChance ){
			$subhazards = array();
			
			//generate 1-3 of them
			$count = mt_rand(1, 3);
			
			for( $i = 0; $i < $count; $i++ ){
				//build subhazard
				$subhazard = getHazardById( $this->getRandomKey()  );
				
				//associate hazards
				$subhazard->setParentHazardId($hazard->getKeyId());
				$subhazards[] = $subhazard;
			}
			
			$hazard->setSubHazards( $subhazards );
		}
		
		$this->LOG->info("Defined Hazard: $hazard");
		
		return $hazard;
	}
	
	public function getQuestionById($keyid){
		$question = new Question();
		$question->setIsActive(TRUE);
		$question->setKeyId($keyid);
		$question->setText("Is this question $keyid?");
		$question->setStandardsAndGuidelines("Guidelines for question $keyid");
		
		$this->LOG->info("Defined Question: $question");
		
		return $question;
	}
	
	public function getPiById($keyid){
		$pi = new PrincipalInvestigator();
		$pi->setKeyId($keyid);
		$pi->setUser( $this->getUserById( $keyid ) );
		
		$this->LOG->info("Defined PrincipalInvestigator: $pi");
		
		return $pi;
	}
	
	public function getRoomById($keyid){
		$room = new Room();
		$room->setIsActive(TRUE);
		$room->setKeyId($keyid);
		$room->setName("Room $keyid");
		$room->setSafetyContactInformation('Call 911');
		
		$this->LOG->info("Defined Room: $room");
		
		return $room;
	}
	
	public function getBuildingById($keyid){
		$building = new Building();
		$building->setIsActive(TRUE);
		$building->setKeyId($keyid);
		$building->setName("Building $keyid");
		
		//TODO: rooms?
		
		$this->LOG->info("Defined Building: $building");
		
		return $building;
	}
	
	public function getDeficiencyById($keyid){
		$deficiency = new Deficiency();
		$deficiency->setIsActive(True);
		$deficiency->setKeyId($keyid);
		$deficiency->setText("Deficiency #$keyid");
		
		$this->LOG->info("Defined Deficiency: $deficiency");
		
		return $deficiency;
	}
	
	public function getInspectionById($keyid){
		$inspection = new Inspection();
		$inspection->setIsActive(True);
		$inspection->setKeyId($keyid);
		
		$this->LOG->info("Defined Inspection: $inspection");
		
		return $inspection;
	}
	
	public function getDeficiencySelectionById($keyid){
		$selection = new DeficiencySelection();
		$selection->setIsActive(True);
		$selection->setKeyId($keyid);
		
		$this->LOG->info("Defined DeficiencySelection: $selection");
		
		return $selection;
	}
	
	public function getRecommendationById($keyid){
		$recommendation = new Recommendation();
		$recommendation->setIsActive(True);
		$recommendation->setKeyId($keyid);
		
		$this->LOG->info("Defined Recommendation: $recommendation");
		
		return $recommendation;
	}
	
	public function getResponseById($keyid){
		$response = new Response();
		$response->setIsActive(TRUE);
		$response->setKeyId($keyid);
		
		$randomAnswerKey = array_rand(Response::$POSSIBLE_ANSWERS);
		$response->setAnswer( Response::$POSSIBLE_ANSWERS[ $randomAnswerKey ] );
		
		$this->LOG->info("Defined Response: $response");
		
		return $response;
	}
}

?>
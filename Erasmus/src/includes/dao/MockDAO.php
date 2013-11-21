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
			$obj->setDateCreated( time() );
		}
		
		$obj->setDateLastModified( time() );
		
		//passed by reference; no need to return (for now)
	}
	
	private function initGenericCrudObject( GenericCrud &$obj ){
		$obj->setDateCreated(time());
		$obj->setDateLastModified(time());
		$obj->setIsActive(TRUE);
		
		return $obj;
	}
	
	public function getUserById( $keyid ){
		$user = $this->initGenericCrudObject( new User() );
		$user->setEmail("user$keyid@host.com");
		$user->setName("User #$keyid");
		$user->setUsername("user$keyid");
		$user->setKeyId($keyid);
		
		$this->LOG->info("Defined User: $user");
		
		return $user;
	}
	
	public function getChecklistById( $keyid ){
		$checklist = $this->initGenericCrudObject(new Checklist());
		$checklist->setKeyId($keyid);
		
		$this->LOG->info("Defined Checklist: $checklist");
		
		return $checklist;
	}
	
	public function getHazardById( $keyid ){
		$hazard = $this->initGenericCrudObject(new Hazard());
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
		$question = $this->initGenericCrudObject(new Question());
		$question->setKeyId($keyid);
		$question->setText("Is this question $keyid?");
		$question->setStandardsAndGuidelines("Guidelines for question $keyid");
		
		$this->LOG->info("Defined Question: $question");
		
		return $question;
	}
	
	public function getPiById($keyid){
		$pi = $this->initGenericCrudObject(new PrincipalInvestigator());
		$pi->setKeyId($keyid);
		$pi->setUser( $this->getUserById( $keyid ) );
		
		// depts
		$depts = array();
		for($i = $keyid; $i < $keyid + 1; ++$i ){
			$dept = $this->getDepartmentById( $this->getRandomKey() );
			$depts[] = $dept;
		}
		
		$pi->setDepartments( $depts );
		
		// rooms
		$rooms = array();
		for($i = 0; $i < 2; $i++ ){
			$room = getRoomById($this->getRandomKey());
			$rooms[] = $room;
		}
		
		$pi->setRooms( $rooms );
		
		//TODO: personnel
		
		$this->LOG->info("Defined PrincipalInvestigator: $pi");
		
		return $pi;
	}
	
	public function getDepartmentById($keyid){
		$dept = $this->initGenericCrudObject(new Department());
		$dept->setKeyId($keyid);
		$dept->setName("Department $keyid");
		
		//TODO: PIs
		
		return $dept;
	}
	
	public function getRoomById($keyid){
		$room = $this->initGenericCrudObject(new Room());
		$room->setKeyId($keyid);
		$room->setName("Room $keyid");
		$room->setSafetyContactInformation('Call 911');
		
		$this->LOG->info("Defined Room: $room");
		
		return $room;
	}
	
	public function getBuildingById($keyid){
		$building = $this->initGenericCrudObject(new Building());
		$building->setKeyId($keyid);
		$building->setName("Building $keyid");
		
		//TODO: rooms?
		
		$this->LOG->info("Defined Building: $building");
		
		return $building;
	}
	
	public function getDeficiencyById($keyid){
		$deficiency = $this->initGenericCrudObject(new Deficiency());
		$deficiency->setKeyId($keyid);
		$deficiency->setText("Deficiency #$keyid");
		
		$this->LOG->info("Defined Deficiency: $deficiency");
		
		return $deficiency;
	}
	
	public function getInspectionById($keyid){
		$inspection = $this->initGenericCrudObject(new Inspection());
		$inspection->setKeyId($keyid);
		
		$this->LOG->info("Defined Inspection: $inspection");
		
		return $inspection;
	}
	
	public function getDeficiencySelectionById($keyid){
		$selection = $this->initGenericCrudObject(new DeficiencySelection());
		$selection->setKeyId($keyid);
		
		$this->LOG->info("Defined DeficiencySelection: $selection");
		
		return $selection;
	}
	
	public function getRecommendationById($keyid){
		$recommendation = $this->initGenericCrudObject(new Recommendation());
		$recommendation->setKeyId($keyid);
		
		$this->LOG->info("Defined Recommendation: $recommendation");
		
		return $recommendation;
	}
	
	public function getResponseById($keyid){
		$response = $this->initGenericCrudObject(new Response());
		$response->setKeyId($keyid);
		
		$randomAnswerKey = array_rand(Response::$POSSIBLE_ANSWERS);
		$response->setAnswer( Response::$POSSIBLE_ANSWERS[ $randomAnswerKey ] );
		
		$this->LOG->info("Defined Response: $response");
		
		return $response;
	}
}

?>
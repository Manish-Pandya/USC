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
	
	public function getAllRoles(){
		return array(
			'Administrator',
			'AppUser',
		);
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
		$user->setRoles( $this->getAllRoles() );
		
		$this->LOG->info("Defined User: $user");
		
		return $user;
	}
	
	public function getChecklistById( $keyid ){
		$checklist = $this->initGenericCrudObject(new Checklist());
		$checklist->setKeyId($keyid);
		
		//add some questions
		$questions = array();
		for($i = 0; $i < 5; $i++){
			$questions[] = $this->getQuestionById($this->getRandomKey());
		}
		$checklist->setQuestions($questions);
		
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
		
		$hazard->setChecklists( $this->getChecklistById($this->getRandomKey()));
		
		$this->LOG->info("Defined Hazard: $hazard");
		
		return $hazard;
	}
	
	public function getQuestionById($keyid){
		$question = $this->initGenericCrudObject(new Question());
		$question->setKeyId($keyid);
		$question->setOrderIndex($keyid);
		$question->setText("Is this question $keyid?");
		$question->setStandardsAndGuidelines("Guidelines for question $keyid");
		$question->setIsMandatory( (bool) mt_rand(0, 1));
		
		// get stock deficiencies
		$deficiencies = array();
		for( $i = 0; $i < 2; $i++ ){
			$def = $this->getDeficiencyById( $this->getRandomKey() );
			$def->setText( "Stock " . $def->getText() );
			$deficiencies[] = $def;
		}
		$question->setDeficiencies( $deficiencies );
		
		// stock recommendations
		$recommendations = array();
		for( $i = 0; $i < 2; $i++ ){
			$rec = $this->getRecommendationById( $this->getRandomKey() );
			$rec->setText( "Stock " . $rec->getText() );
			$recommendations[] = $rec;
		}
		$question->setRecommendations($recommendations);
		
		// stock observations
		$observations = array();
		for( $i = 0; $i < 2; $i++ ){
			$obs = $this->getObservationById( $this->getRandomKey() );
			$obs->setText( "Stock " . $obs->getText() );
			$observations[] = $obs;
		}
		$question->setObservations($observations);
		
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
	
	public function getInspectorById($keyid){
		$inspector = $this->initGenericCrudObject( new Inspector() );
		$inspector->setKeyId($keyid);
		$inspector->setEmail("inspector$keyid@host.com");
		$inspector->setName("Inspector #$keyid");
		$inspector->setUsername("inspector$keyid");
		$inspector->setRoles( $this->getAllRoles() );
		
		$inspections = array();
		for( $i = 0; $i < 2; $i++){
			$inspection = $this->getInspectionById( $this->getRandomKey() );
			$inspections[] = $inspection;
		}
		$inspector->setInspections( $inspections );

		$this->LOG->info("Defined Inspector: $inspector");
		
		return $inspector;
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
		
		// Hazards
		$hazards = array();
		for($i = 0; $i < 3; $i++){
			$hazard = $this->getHazardById( $this->getRandomKey() );
			$hazards[] = $hazard;
		}
		
		$room->setHazards( $hazards );
		
		$this->LOG->info("Defined Room: $room");
		
		return $room;
	}
	
	public function getBuildingById($keyid){
		$building = $this->initGenericCrudObject(new Building());
		$building->setKeyId($keyid);
		$building->setName("Building $keyid");
		
		//rooms
		$rooms = array();
		for($i = 0; $i < 2; $i++ ){
			$room = getRoomById($this->getRandomKey());
			$rooms[] = $room;
		}
		
		$building->setRooms( $rooms );
		
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
		
		// Inspector(s)
		$inspection->setInspectors( array(getUserById( $this->getRandomKey() )) );
		
		// PI
		$inspection->setPrincipalInvestigator( $this->getPiById( $this->getRandomKey() ) );
		
		// Responses?
		$responses = array();
		for($i = 0; $i < 10; $i++){
			$res = $this->getResponseById($i);
			$responses[] = $res;
		}
		$inspection->setResponses($responses);
		
		// Start/End date
		$inspection->setDateStarted( time() );
		$inspection->setDateClosed( time() );
		
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
		$recommendation->setText("Recommendation #$keyid");
		
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
	
	public function getObservationById($keyid){
		$observation = $this->initGenericCrudObject(new Observation());
		$observation->setKeyId($keyid);
		$observation->setText("Observation #$keyid");
		
		$this->LOG->info("Defined Observation: $observation");
		
		return $observation;
	}
}

?>
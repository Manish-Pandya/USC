<?php

require_once dirname(__FILE__) . '/statichazards.php';

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
		
		if( $obj->getKey_Id() === NULL ){
			//Assign random key for now
			$obj->setKey_Id( mt_rand(0, $this->getRandomKey() ) );
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
		$user->setKey_Id($keyid);
		$user->setRoles( $this->getAllRoles() );
		
		$this->LOG->info("Defined User: $user");
		
		return $user;
	}
	
	public function getChecklistById( $keyid ){
		$checklist = $this->initGenericCrudObject(new Checklist());
		$checklist->setKey_Id($keyid);
		
		//add some questions
		$questions = array();
		for($i = 0; $i < 5; $i++){
			$questions[] = $this->getQuestionById($this->getRandomKey());
		}
		$checklist->setQuestions($questions);
		
		$this->LOG->info("Defined Checklist: $checklist");
		
		return $checklist;
	}
	
	public function getAllHazards(){
		return getStaticHazardsAsTree();
	}
	
	//FIXME: Remove $name
	public function getHazardById( $keyid, $name = NULL ){
		$hazard = $this->initGenericCrudObject(new Hazard());
		$hazard->setKey_Id($keyid);
		
		if( $name === NULL ){			
			$hazard->setName("Dangerous thing #$keyid");
		}
		else{
			$hazard->setName( $name );
		}
		
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
				$subhazard->setParentHazardId($hazard->getKey_Id());
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
		$question->setKey_Id($keyid);
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
		$pi->setKey_Id($keyid);
		$pi->setUser( $this->getUserById( $keyid ) );
		
		// depts
		$depts = array();
		for($i = $keyid; $i < $keyid + 1; ++$i ){
			$dept = $this->getDepartmentById( $this->getRandomKey() );
			$depts[] = $dept;
		}
		
		$pi->setDepartments( $depts );
		
		// Personnel
		$personnel = array();
		for($i = 0; $i < 3; $i++ ){
			$user = $this->getUserById( $this->getRandomKey() );
			$personnel[] = $user;
		}
		
		$pi->setLabPersonnel( $personnel );
		
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
		$inspector->setKey_Id($keyid);
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
		$dept->setKey_Id($keyid);
		$dept->setName("Department $keyid");
		
		//TODO: PIs
		
		return $dept;
	}
	
	public function getRoomById($keyid){
		$room = $this->initGenericCrudObject(new Room());
		$room->setKey_Id($keyid);
		$room->setName("$keyid");
		$room->setSafetyContactInformation('Call 911');
		
		//FIXME: Remove DEMO names
		$demoHazardNames = array(
			"Biological Materials",
			"General Laboratory Safety",
			"Radiation Safety"
		);
		
		// Hazards
		$hazards = array();
		for($i = 0; $i < 3; $i++){
			$hazard = $this->getHazardById( $this->getRandomKey(), $demoHazardNames[$i] );
			$hazards[] = $hazard;
		}
		
		$room->setHazards( $hazards );
		
		$this->LOG->info("Defined Room: $room");
		
		return $room;
	}
	
	public function getRoomDtoById($keyid){
		$room = $this->initGenericCrudObject(new RoomDto());
		$room->setKey_Id($keyid);
		$room->setName("$keyid");
		
		return $roomDto;
	}
	
	public function getBuildingById($keyid){
		$building = $this->initGenericCrudObject(new Building());
		$building->setKey_Id($keyid);
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
		$deficiency->setKey_Id($keyid);
		$deficiency->setText("Deficiency #$keyid");
		
		$this->LOG->info("Defined Deficiency: $deficiency");
		
		return $deficiency;
	}
	
	public function getInspectionById($keyid){
		$inspection = $this->initGenericCrudObject(new Inspection());
		$inspection->setKey_Id($keyid);
		
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
		$selection->setKey_Id($keyid);
		
		$this->LOG->info("Defined DeficiencySelection: $selection");
		
		return $selection;
	}
	
	public function getRecommendationById($keyid){
		$recommendation = $this->initGenericCrudObject(new Recommendation());
		$recommendation->setKey_Id($keyid);
		$recommendation->setText("Recommendation #$keyid");
		
		$this->LOG->info("Defined Recommendation: $recommendation");
		
		return $recommendation;
	}
	
	public function getResponseById($keyid){
		$response = $this->initGenericCrudObject(new Response());
		$response->setKey_Id($keyid);
		
		$randomAnswerKey = array_rand(Response::$POSSIBLE_ANSWERS);
		$response->setAnswer( Response::$POSSIBLE_ANSWERS[ $randomAnswerKey ] );
		
		$this->LOG->info("Defined Response: $response");
		
		return $response;
	}
	
	public function getObservationById($keyid){
		$observation = $this->initGenericCrudObject(new Observation());
		$observation->setKey_Id($keyid);
		$observation->setText("Observation #$keyid");
		
		$this->LOG->info("Defined Observation: $observation");
		
		return $observation;
	}
}

?>
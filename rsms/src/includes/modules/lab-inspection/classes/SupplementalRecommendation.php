<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class SupplementalRecommendation extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "supplemental_recommendation";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"text"		=> "text",
		"response_id"	=>	"integer",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);
	
	/** Reference to the Response entity to which this SupplementalRecommendation applies */
	private $response;
	private $response_id;
	
	/** String containing the text describing this SupplementalRecommendation */
	private $text;
	
	public function __construct(){

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getResponse");
		return $entityMaps;	
	}
		
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getResponse(){ 
		$responseDAO = new GenericDAO(New Response());
		$this->response = $responseDAO->getById($this->response_id);
		return $this->response; 
	}
	public function setResponse($response){
		$this->response = $response; 
	}
	
	public function getResponse_id(){ return $this->response_id; }
	public function setResponse_id($response_id){ $this->response_id = $response_id; }
	
		
	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }
}
?>
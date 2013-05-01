<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Question {
	
	/** Question text */
	private $text;
	
	/** String that describes (or excerpts) the Standards and Guidelines to which this Question pertains */
	private $standardsAndGuidelines;
	
	/** Boolean that determines whether this Question may be skipped (FALSE) or must be answered (TRUE) */
	private $isMandatory;
	
	/** Array of pre-defined Deficiency entities that may be selected for this Question */
	private $deficiencies;
	
	/** Array of pre-defined DeficiencyRootCause entities that may be selected
	 * 	as a cause for this Question being answered Deficiently */
	private $deficiencyRootCauses;
	
	/** Array of Recommendation entities that may be selected for this Question */
	private $recommendations;
	
	public function __construct(){
	
	}
}
?>
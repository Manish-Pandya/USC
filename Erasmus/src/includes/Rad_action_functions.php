<?php
/*
 * This file contains action functions specific to the radiation module.
 * 
 * If a non-fatal error occurs, Rad_action_functions should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 * 
 */


/*****************************************************************************\
 *                            Get Functions                                  *
\*****************************************************************************/


function getIsotopeById($id = NULL) {
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao(new Isotope());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getCarboyById($id = NULL) {
	$LOG = Logger::getLogger('Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$dao = getDao(new Carboy());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getCarboyUseCycleById($id = NULL) {
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$dao = getDao(new CarboyUseCycle());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getDisposalLotById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$dao = getDao(new DisposalLot());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getDrumById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$dao = getDao(new Drum());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getParcelById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ) {
		$dao = getDao(new Parcel());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request Parameter 'id' was provided");
	}
}

function getParcelUseById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$dao = getDao(new ParcelUse());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getPickupById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ) {
		$dao = getDao(new Pickup());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getPickupLotById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$dao = getDao(new PickupLot());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getPurchaseOrderById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ) {
		$dao = getDao(new PurchaseOrder());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getWasteTypeById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ) {
		$dao = getDao(new WasteType());
		return $dao->getById($id);
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}


/*****************************************************************************\
 *                        Get By Relationships Functions                     *
 *  Gets functions dependent on another entity or some form of relationship  *
\*****************************************************************************/


function getAuthorizationsByPIId($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__);
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$piDao = getDao(new PrincipalInvestigator());
		$selectedPi = $piDao->getById($id);
		return $selectedPi->getAuthorizations();
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getPickupLotsByPickupId($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__);
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$pickupDao = getDao(new Pickup());
		$selectedPickup = $pickupDao->getById($id);
		return $selectedPickup->getPickupLots();
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getDisposalLotsByPickupLotId($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$pickupLotDao = getDao(new PickupLot());
		$pickupLot = $pickupLotDao->getById($id);
		return $pickupLot->getDisposalLots();
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getDisposalLotsByDrumId($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ) {
		$drumDao = getDao(new PickupLot());
		$selectedDrum = $drumDao->getById($id);
		return $selectedDrum->getDisposalLots();
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getParcelUsesByParcelId($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$parcelDao = getDao(new Parcel());
		$selectedParcel = $parcelDao->getById($id);
		return $selectedParcel->getUses();
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getActiveParcelsFromPIById($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$PiDao = getDao(new PrincipalInvestigator());
		$selectedPi = $PiDao->getById($id);
		return $selectedPi->getActiveParcels();
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}


/*****************************************************************************\
 *                               getAll functions                            *
\*****************************************************************************/

function getAllCarboys() {
	$carboyDao = new GenericDAO(new Carboy());
	return $carboyDao->getAll();
}

function getAllDrums() {
	$drumDao = new GenericDAO(new Drum());
	return $drumDao->getAll();
}

 
/*****************************************************************************\
 *                              Save Functions                               *
\*****************************************************************************/


function saveAuthorization() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to Authorziation');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new Authorization());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function saveIsotope() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to Isotope');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new Isotope());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function saveCarboy() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to Carboy');
	}
	else if ( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new Carboy());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function saveCarboyUseCycle() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to CarboyUseCycle');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new CarboyUseCycle());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function saveDisposalLot() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to DisposalLot');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new DisposalLot());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function saveDrum() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to Drum');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new Drum());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function saveParcel() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to Parcel');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new Parcel());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function saveParcelUse($parcel = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	// check if this function was called from another action function
	if($parcel == NULL) {
        $decodedObject = convertInputJson();
	}
	else {
		// use method parameters if they exist
		$decodedObject = $parcel;
	}
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to ParcelUse');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new ParcelUse());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function savePickup() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to Pickup');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new Pickup());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function savePickupLot() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to PickupLot');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new PickupLot());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function savePurchaseOrder() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to PurchaseOrder');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new PurchaseOrder());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}

function saveWasteType() {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ) {
		return new ActionError('Error converting input stream to WasteType');
	}
	else if( $decodedObject instanceof ActionError) {
		return $decodedObject;
	}
	else {
		$dao = getDao(new WasteType());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
}


/*****************************************************************************\
 *                             Other Functions                               *
 *  For a very specific purpose, or I couldn't think of a category for them. *
\*****************************************************************************/


// Returns amount of unused isotope in a parcel
function getParcelRemainder($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);
	if($id !== NULL) {
        $parcelDao = new GenericDAO(new Parcel());
        $parcel = $parcelDao->getById($id);
        return $parcel->getRemainder();
	}
	else {
        return new ActionError("No request parameter 'id' was provided.");
	}
}

// Assigns all remaining isotope in a parcel to a usage
function disposeParcelRemainder($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if($id !== NULL) {
		// get remaining isotope in the parcel
		$parcelDao = new GenericDAO(new Parcel());
		$parcel = $parcelDao->getById($id);
		$remainder = $parcel->getRemainder();

        // if no isotope remaining to assign, return ActionError saying so.
        if($remainder == 0) {
        	return new ActionError("No remainder left to dispose of.");
        }

		// create new parcel usage, fill with remainder of old parcel
		$parcelUse = new ParcelUse();
		$parcelUse->setQuantity($remainder);
		$parcelUse->setParcel_id($parcel->getKey_id());
		$parcelUse->setIs_active(true);

		// save record of new parcel use
		$parcelUseDao = new GenericDAO(new ParcelUse());
		$parcelUse = $parcelUseDao->save($parcelUse);

		return $parcelUse;
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

// Returns associative array of Waste Amounts containing waste types and respective amounts 
function getWasteAmountsByParcelId($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		// get selected parcel's usages
		$parcelDao = new GenericDAO(new Parcel());
		$parcelUseDao = new GenericDAO(new ParcelUse());
		$parcel = $parcelDao->getById($id);
		$parcelUses = $parcel->getUses();

		$typesAndAmounts = array();
		
		// iterate through parcel uses, adding up waste types and amounts
		foreach($parcelUses as $use) {
			$usedAmounts = $use->getParcelUseAmounts();
			
			// Note to self: nested loops are annoying, and it feels like
			//     all the abstractions are getting in the way to some extent
			//     Parcel, ParcelUse, ParcelUseAmount, etc. Could there be a
			//     better way?
			
			// sum the amount of waste present for each type of waste
			foreach($usedAmounts as $amount) {
				$wasteType = $amount->getWaste_type();
				$wasteName = $wasteType->getName();
				$wasteAmount = $amount->getCurie_level();
				
				$typesAndAmounts[$wasteName] += $wasteAmount;
				
			}
		}
		
		// associative array isn't transfered correctly over JSON, convert into
		// 	   array of Waste Dtos
		$wasteDtos = array();
		foreach($typesAndAmounts as $type => $amount) {

			$waste = new WasteDto($type, $amount);
			$wasteDtos[] = $waste;
		}
		
		return $wasteDtos;
		
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

// Returns array of this parcelUse's types and quantities of waste.
function getParcelUseWaste($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ) {
		$parcelUseDao = new GenericDAO(new ParcelUse());
		$parcelUse = $parcelUseDao->getById($id);
		
		return $parcelUse->getParcelUseAmounts();
	}
	else {
		return new ActionError("No request parameter 'id' was provided");
	}
}

// Returns the waste this PI has from all of its active parcels, in the form of 
// an array of waste amounts, one waste amount per type.
function getTotalWasteFromPI($id = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$id = getValueFromRequest('id', $id);
	
	if( $id == NULL ) {
		return new ActionError("No request parameter 'id' was provided");
	}
	
	// get parcels belonging to this PI to check for waste
	$parcels = getActiveParcelsFromPIById($id);
	
	$totalWastes = array();
	
	// get waste used in each parcel, adding up totals for each waste type as we go.
	foreach($parcels as $parcel) {
		$wastes = getWasteAmountsByParcelId($parcel->getKey_id());
		// convert waste amounts into associative array for ease of manipulation
		$wastes = unpackWasteDtos($wastes);
		$totalWastes = addArrays($totalWastes, $wastes);
	}
	
	// wrap result in Dtos for returning to client
	$totalWastes = packWasteDtos($totalWastes);
	
	return $totalWastes;
}

// Returns all parcel uses from this PI that have taken place since the given date
function getParcelUsesFromPISinceDate($id = NULL, $date = NULL) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$date = getValueFromRequest('date', $date);
	$id = getValueFromRequest('id', $id);

	if( $id === NULL ) {
		return new ActionError("No request paramter 'id' was provided");
	}
	if( $date === NULL ) {
		return new ActionError("No request parameter 'date' was provided");
	}

	// convert string input date to a format we can do comparisons with
	$inputDate = strtotime($date);

	// get selected PI
	$piDao = new GenericDAO(new PrincipalInvestigator());
	$pi = $piDao->getById($id);

	// get parcels from PI, search for recent uses
	$parcelUses = array();
	$parcels = $pi->getActiveParcels();
	foreach( $parcels as $parcel ) {
		$uses = $parcel->getUses();
		
		foreach( $uses as $use ) {
			// convert date of use into format we can do comparisons with
			$useDate = strtotime($use->getDate_of_use());
        	
			// check if this use took place since the given input date
			if( $useDate > $inputDate ) {
				$parcelUses[] = $use;
			}
		}
	}
	
	return $parcelUses;
}

// Returns the amount of waste produced by this PI since the given date
function getWasteFromPISinceDate($id = NULL, $date = null) {
	$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
	$id = getValueFromRequest('id', $id);
	$date = getValueFromRequest('date', $date);
	
	if( $id = NULL ) {
		return new ActionError("No request parameter 'id' was provided");
	}
	if( $date = NULL ) {
		return new ActionError("No request parameter 'date' was provided");
	}
	
	// get relevant uses
	$parcelUses = getParcelUsesFromPISinceDate($id, $date);
	
	// total the wastes from each uses
	$wasteAmounts = array();
	foreach($parcelUses as $use) {
		// get amount of waste from this PI's use, convert into associative array (easier to work with)
		$useAmounts = $use->getParcelUseAmounts();
		$wastes = convertParcelUseAmountsToWasteArray($useAmounts);
		
		// add waste from this parcel use to total waste
		$wasteAmounts = addArrays($wasteAmounts, $wastes);
	}
	
	// convert associative array into easily JSONable array of waste Dtos
	$waste = packWasteDtos($wasteAmounts);

	return $waste;
}


/*****************************************************************************\
 *                            Utility Functions                              *
 *         Not exposed to frontend, just helpful for internal use.           *
\*****************************************************************************/


/**
 * Converts array of ParcelUseAmounts into associative array of [Type] => [Amount].
 * (This format is nicer to work with when adding waste totals)
 * 
 * @param array $uses
 * @return Associative array: [string Type] => [integer Amount]
 */
function convertParcelUseAmountsToWasteArray($uses) {
	$wasteArray = array();
	
	foreach($uses as $use) {
		$wasteType = $use->getWaste_type();
		$amount = $use->getCurie_level();
		$wasteName = $wasteType->getName();

		if( array_key_exists($wasteName, $wasteArray) ) {
			$wasteArray[$wasteName] += $amount;
		}
		else {
			$wasteArray[$wasteName] = $amount;
		}
	}
	
	return $wasteArray;
}

/**
 * Returns all items in the second associative array added to the first.
 * If two keys are shared, their value is combined.
 * If a key exists in the second array but not the first, a new key will be created in the new array.
 * 
 * @param Associative array: [string Type] => [integer Amount] $firstArray
 * @param Associative array: [string Type] => [integer Amount] $secondArray
 * @return Associative array: [string Type] => [integer Amount]
 */
function addArrays($firstArray, $secondArray) {
	
	// base array to start adding items in the second array to
	$combinedArrays = $firstArray;
	
	foreach( $secondArray as $key => $value ) {
		if( array_key_exists($key, $combinedArrays) ) {
			// First array already has some existing quantity of that waste type, add to it.
			$combinedArrays[$key] += $value;
		}
		else {
			// waste array doesn't yet have that type of waste, create a new entry for it.
			$combinedArrays[$key] = $value;
		}
	}

	return $combinedArrays;
}

/**
 * Converts array of waste amount dtos into associative arrays of types and amounts.
 * 
 * @param array( WasteDto ) $wasteDtos
 * @return Associative array: [string Type] => integer Amount
 */
function unpackWasteDtos($wasteDtos) {
	$wastes = array();

	foreach( $wasteDtos as $waste ) {
		$wastes[$waste->getType()] = $waste->getAmount();
	}
	
	return $wastes;
}

/**
 * Converts associative array of types and amounts into array of waste amount dtos.
 * (The opposite of unpackWasteDtos)
 * 
 * @param  Associative array: [string Type] => integer Amount   $wasteArray
 * @return array( WasteDto )
 */
function packWasteDtos($wasteArray) {
	$wasteDtos = array();
	
	foreach($wasteArray as $type => $amount ) {
		$wasteDtos[] = new WasteDto($type, $amount);
	}
	
	return $wasteDtos;
}

?>
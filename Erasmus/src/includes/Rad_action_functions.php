<?php
/*
 * This file contains action functions specific to the radiation module.
 * 
 * If a non-fatal error occurs, Rad_action_functions should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 * 
 */


// get functions

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

// get by relationships

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

// save functions

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


// other functions

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

?>
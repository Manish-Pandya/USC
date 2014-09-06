<?php
/*
 * This file contains action functions specific to the radiation module.
 * 
 * If a non-fatal error occurs, Rad_action_functions should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 * 
 */

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
	$LOG = LOgger::getLogger( 'Action' . __FUNCTION__ );
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
	$LOG = LOgger::getLogger( 'Action' . __FUNCTION__ );
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
	$LOG = LOgger::getLogger( 'Action' . __FUNCTION__ );
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
	$LOG = LOgger::getLogger( 'Action' . __FUNCTION__ );
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

?>
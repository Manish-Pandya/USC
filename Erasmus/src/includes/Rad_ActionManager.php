<?php
/**
 * Contains action functions specific to the radiation module.
 *
 * If a non-fatal error occurs, should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 *
 * @author Perry
 */
class Rad_ActionManager extends ActionManager {


	/*****************************************************************************\
	 *                            Get Functions                                  *
	\*****************************************************************************/


	function getIsotopeById($id = NULL) {
		$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Isotope());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getCarboyById($id = NULL) {
		$LOG = Logger::getLogger('Action:' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new Carboy());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getCarboyUseCycleById($id = NULL) {
		$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new CarboyUseCycle());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getDrumById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new Drum());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getParcelById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new Parcel());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request Parameter 'id' was provided", 201);
		}
	}

	function getParcelUseById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new ParcelUse());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getPickupById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new Pickup());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getPurchaseOrderById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new PurchaseOrder());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getWasteTypeById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new WasteType());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getWasteBagById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new WasteBag());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getSolidsContainerById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$dao = $this->getDao(new SolidsContainer());
			return $dao->getById($id);
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getAllRadPis(){
		$dao = $this->getDao(new PrincipalInvestigator());
		$pis = $dao->getAll();

		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getLabPersonnel");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("lazy","getDepartments");
		$entityMaps[] = new EntityMap("lazy","getUser");
		$entityMaps[] = new EntityMap("lazy","getInspections");
		$entityMaps[] = new EntityMap("lazy","getAuthorizations");
		$entityMaps[] = new EntityMap("lazy", "getActiveParcels");

		foreach($pis as $pi){
			$pi->setEntityMaps($entityMaps);
		}

		return $pis;

	}

	public function getAllRadUsers(){
		$dao = $this->getDao(new User());
		$users = $dao->getAll();

		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("lazy","getInspector");
		$entityMaps[] = new EntityMap("lazy","getSupervisor");
		$entityMaps[] = new EntityMap("lazy","getRoles");
		$entityMaps[] = new EntityMap("eager","getEmergency_phone");

		foreach($users as $user){
			$user->setEntityMaps($entityMaps);
		}

		return $users;
	}

	// getPIById already exists in the base module, however different entity maps
	// are used in RadiationModule, so this sepparate method exists.
	public function getRadPIById( $id = null ){
		if($id == null)$id = $this->getValueFromRequest( "id", $id );
		if($rooms == null)$rooms = $this->getValueFromRequest( "rooms", $rooms );

		$dao = $this->getDao(new PrincipalInvestigator());
		$pi = $dao->getById($id);
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getLabPersonnel");
		if($rooms == null){
			$entityMaps[] = new EntityMap("lazy","getRooms");
		}else{
			$entityMaps[] = new EntityMap("eager","getRooms");
		}
		$entityMaps[] = new EntityMap("lazy","getDepartments");
		$entityMaps[] = new EntityMap("eager","getUser");
		$entityMaps[] = new EntityMap("lazy","getInspections");
		$entityMaps[] = new EntityMap("eager","getAuthorizations");
		$entityMaps[] = new EntityMap("eager", "getActiveParcels");
		$entityMaps[] = new EntityMap("eager","getAuthorizations");
		$entityMaps[] = new EntityMap("eager", "getCarboyUseCycles");
		$entityMaps[] = new EntityMap("eager", "getPurchaseOrders");
		$entityMaps[] = new EntityMap("eager", "getSolidsContainers");
		$pi->setEntityMaps($entityMaps);
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug($pi);
		return $pi;

	}

	/*****************************************************************************\
	 *                        Get By Relationships Functions                     *
	 *  Gets functions dependent on another entity or some form of relationship  *
	\*****************************************************************************/


	function getAuthorizationsByPIId($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__);

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$piDao = $this->getDao(new PrincipalInvestigator());
			$selectedPi = $piDao->getById($id);
			return $selectedPi->getAuthorizations();
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getWasteBagsByPickupId($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__);

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$pickupDao = $this->getDao(new Pickup());
			$selectedPickup = $pickupDao->getById($id);
			return $selectedPickup->getWasteBags();
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getResultingDrumsByPickupId($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			// get pickup
			$pickupDao = $this->getDao(new Pickup());
			$selectedPickup = $pickupDao->getById($id);

			// get waste bags picked up
			$wasteBags = $selectedPickup->getWasteBags();

			// make list of drums these bags went into
			$drumIds = array();
			foreach($wasteBags as $bag) {
				$drumId = $bag->getDrum_id();
				if( !in_array($drumId, $drumIds) ) {
					$drumIds[] = $drumId;
				}
			}

			$drumDao = $this->getDao(new Drum());
			$drums = array();
			foreach($drumIds as $id) {
				$drums[] = $drumDao->getById($id);
			}

			return $drums;
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getParcelUsesByParcelId($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$parcelDao = $this->getDao(new Parcel());
			$selectedParcel = $parcelDao->getById($id);
			return $selectedParcel->getUses();
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getActiveParcelsFromPIById($id = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$PiDao = $this->getDao(new PrincipalInvestigator());
			$selectedPi = $PiDao->getById($id);
			return $selectedPi->getActiveParcels();
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	function getSolidsContainersByRoomId($id = NULL) {
		$LOG = Logger::getLogger('Action' . __FUNCTION__);

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ) {
			$roomDao = $this->getDao(new Room());
			$selectedRoom = $roomDao->getById($id);
			return $selectedRoom->getSolidsContainers();
		}
		else {
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}


	/*****************************************************************************\
	 *                               getAll functions                            *
	\*****************************************************************************/

	public function getAllAuthorizations(){
		$dao = $this->getDao(new Authorization());
		return $dao->getAll();
	}

	function getAllCarboys() {
		$carboyDao = $this->getDao(new Carboy());
		return $carboyDao->getAll();
	}

	public function getAllCarboyUseCycles(){
		$dao = $this->getDao(new CarboyUseCycle());
		return $dao->getAll();
	}

	function getAllDrums() {
		$drumDao = $this->getDao(new Drum());
		return $drumDao->getAll();
	}

	function getAllIsotopes() {
		$isotopeDao = $this->getDao(new Isotope());
		return $isotopeDao->getAll();
	}

	public function getAllParcels(){
		$dao = $this->getDao(new Parcel());
		return $dao->getAll();
	}

	public function getAllParcelUses(){
		$dao = $this->getDao(new ParcelUse());
		return $dao->getAll();
	}

	public function getAllParcelUseAmounts(){
		$dao = $this->getDao(new ParcelUseAmount());
		return $dao->getAll();
	}

	public function getAllPickups(){
		$dao = $this->getDao(new Pickup());
		return $dao->getAll();
	}

	public function getAllPurchaseOrders(){
		$dao = $this->getDao(new PurchaseOrder());
		return $dao->getAll();
	}

	public function getAllWasteBags(){
		$dao = $this->getDao(new WasteBag());
		return $dao->getAll();
	}

	function getAllWasteTypes() {
		$typeDao = $this->getDao(new WasteType());
		return $typeDao->getAll();
	}

	function getAllSolidsContainers() {
		$dao = $this->getDao(new SolidsContainer());
		return $dao->getAll();
	}


	/*****************************************************************************\
	 *                              Save Functions                               *
	\*****************************************************************************/


	function saveAuthorization() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to Authorization', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new Authorization());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveIsotope() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to Isotope', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new Isotope());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveCarboy() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to Carboy', 202);
		}
		else if ( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new Carboy());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveCarboyUseCycle() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to CarboyUseCycle', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new CarboyUseCycle());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveDrum() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to Drum', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new Drum());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveParcel() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to Parcel', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new Parcel());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveParcelUse($parcel = NULL) {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

		// check if this function was called from another action function
		if($parcel == NULL) {
	        $decodedObject = $this->convertInputJson();
		}
		else {
			// use method parameters if they exist
			$decodedObject = $parcel;
		}
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to ParcelUse', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new ParcelUse());
			$use = $dao->save($decodedObject);

			$amounts = $decodedObject->getParcelUseAmounts();
			foreach($amounts as $amount){
				$amountDao = $this->getDao(new ParcelUseAmount());
				$newAmount = new ParcelUseAmount();

				$newAmount->setParcel_use_id($use->getKey_id());
				$newAmount->setCurie_level($amount['Curie_level']);
				if($amount['Waste_bag_id'] != NULL)$newAmount->setWaste_bag_id($amount['Waste_bag_id']);
				if($amount['Carboy_id'] != NULL)$newAmount->setWaste_bag_id($amount['Carboy_id']);
				if($amount['Key_id'] != NULL)$newAmount->setWaste_bag_id($amount['Key_id']);
				$newAmount->setWaste_type_id($amount['Waste_type_id']);
				$amountDao->save($newAmount);
			}

			return $use;
		}
	}

	function savePickup() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to Pickup', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new Pickup());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function savePurchaseOrder() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to PurchaseOrder', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new PurchaseOrder());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveWasteType() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to WasteType', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new WasteType());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveWasteBag() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		$LOG->debug($decodedObject);
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to WasteBag', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new WasteBag());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	function saveSolidsContainer() {
		$LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ) {
			return new ActionError('Error converting input stream to WasteType', 202);
		}
		else if( $decodedObject instanceof ActionError) {
			return $decodedObject;
		}
		else {
			$dao = $this->getDao(new SolidsContainer());
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

		$id = $this->getValueFromRequest('id', $id);
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

		$id = $this->getValueFromRequest('id', $id);

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

		$id = $this->getValueFromRequest('id', $id);

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
		$id = $this->getValueFromRequest('id', $id);

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
		$id = $this->getValueFromRequest('id', $id);

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
		$date = $this->getValueFromRequest('date', $date);
		$id = $this->getValueFromRequest('id', $id);

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
		$id = $this->getValueFromRequest('id', $id);
		$date = $this->getValueFromRequest('date', $date);

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

	/**
	 * Creates, saves, and returns a new Pickup based on inputted user, date,
	 * and array of containers to empty.
	 */
	function createPickup() {
		// get required info from info Dto
		$decodedObject = $this->convertInputJson();

		$userId = $decodedObject->getUser_id();
		$date = $decodedObject->getDate();
		$containers = $decodedObject->getContainers();

		if($decodedObject === null) {
			return new ActionError("Error converting input stream to PickupDto", 202);
		}
		else if ($decodedObject instanceof ActionError) {
			return $decodedObject;
		}


		// create pickup with user and date
		$newPickup = new Pickup();
		$newPickup->setPickup_user_id($userId);
		$newPickup->setPickup_date($date);

		// save new pickup, get assigned key id to use later
		$newPickup = $this->savePickup($newPickup);
		$pickupKeyId = $newPickup->getKey_id();

		// get list of all WasteBags to be picked up
		$wasteBags = array();
		foreach($containers as $container) {
			$wasteBags = array_merge( $wasteBags, $container->getCurrentWasteBags() );
		}

		// mark waste bags to be picked up by pickup id
		foreach($wasteBags as $bag) {
			$bag->setPickup_id($pickupKeyId);
			$this->saveWasteBag($bag);
		}

		$newPickup->getWasteBags();
		return $newPickup;
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
}

?>
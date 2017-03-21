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

    function getRadInspectionById($id = NULL) {
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new Inspection());
            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy","getInspectors");
            $entityMaps[] = new EntityMap("eager","getRooms");
            $entityMaps[] = new EntityMap("lazy","getResponses");
            $entityMaps[] = new EntityMap("lazy","getDeficiency_selections");
            $entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
            $entityMaps[] = new EntityMap("eager","getStatus");
            $entityMaps[] = new EntityMap("lazy","getChecklists");
            $entityMaps[] = new EntityMap("eager","getInspection_wipe_tests");

            $inspection =  $dao->getById($id);
            $inspection->setEntityMaps($entityMaps);
            return $inspection;
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

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
            $parcel = $dao->getById($id);

            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
            $entityMaps[] = new EntityMap("lazy", "getPurchase_order");
            $entityMaps[] = new EntityMap("eager", "getIsotope");
            $entityMaps[] = new EntityMap("eager", "getParcelUses");
            $entityMaps[] = new EntityMap("eager", "getRemainder");
            $entityMaps[] = new EntityMap("lazy", "getWipe_test");

            $parcel->setEntityMaps($entityMaps);

            $useMaps = array();
            $useMaps[] = new EntityMap("lazy", "getParcel");
            $useMaps[] = new EntityMap("eager", "getParcelUseAmounts");

            $amountMaps = array();
            $amountMaps[] = new EntityMap("eager", "getCarboy");
            $amountMaps[] = new EntityMap("eager", "getWaste_type");
            $amountMaps[] = new EntityMap("eager", "getContainer_name");

            foreach($parcel->getParcelUses() as $use){
                $use->setEntityMaps($useMaps);

                foreach($use->getParcelUseAmounts() as $amount){
                    $amount->setEntityMaps($amountMaps);
                }

            }

            return $parcel;

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

    function getInspectionWipeTestById($id = NULL) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new InspectionWipeTest());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    function getInspectionWipeById($id = NULL) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new InspectionWipe());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    function getParcelWipeTestById($id = NULL) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new ParcelWipeTest());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    function getParcelWipeById($id = NULL) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new ParcelWipe());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    function getMiscellaneousWipeTestById($id = NULL){
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new MiscellaneousWipeTest());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    function getMiscellaneousWipeById($id = NULL){
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new MiscellaneousWipe());
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
        $entityMaps[] = new EntityMap("eager","getDepartments");
        $entityMaps[] = new EntityMap("lazy","getUser");
        $entityMaps[] = new EntityMap("lazy","getInspections");
        $entityMaps[] = new EntityMap("lazy","getPrincipal_investigator_room_relations");
        $entityMaps[] = new EntityMap("lazy","getPi_authorization");
        $entityMaps[] = new EntityMap("lazy","getActiveParcels");
        $entityMaps[] = new EntityMap("lazy","getPurchaseOrders");
        $entityMaps[] = new EntityMap("lazy","getCarboyUseCycles");
        $entityMaps[] = new EntityMap("lazy","getSolidsContainers");
        $entityMaps[] = new EntityMap("lazy","getPickups");
        $entityMaps[] = new EntityMap("lazy","getScintVialCollections");
        $entityMaps[] = new EntityMap("lazy","getCurrentScintVialCollections");
        $entityMaps[] = new EntityMap("lazy","getInspection_notes");
        $entityMaps[] = new EntityMap("lazy","getVerifications");
        $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
        $entityMaps[] = new EntityMap("lazy","getOpenInspections");
        $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");

        foreach($pis as $pi){
            $pi->setEntityMaps($entityMaps);
        }

        return $pis;

    }

    public function getAllRadUsers(){
        $dao = $this->getDao(new User());
        $users = $dao->getAll();
        $entityMaps = array();

        $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigator");
        $entityMaps[] = new EntityMap("lazy","getInspector");
        $entityMaps[] = new EntityMap("lazy","getSupervisor");
        $entityMaps[] = new EntityMap("lazy","getRoles");
        $entityMaps[] = new EntityMap("lazy","getPrimary_department");

        foreach($users as $user){
            $user->setEntityMaps($entityMaps);
        }

        return $users;
    }

    // getPIById already exists in the base module, however different entity maps
    // are used in RadiationModule, so this sepparate method exists.
    public function getRadPIById( $id = null, $rooms = null ){
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
        $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
        $entityMaps[] = new EntityMap("eager","getDepartments");
        $entityMaps[] = new EntityMap("eager","getUser");
        $entityMaps[] = new EntityMap("lazy","getInspections");
        $entityMaps[] = new EntityMap("eager","getPi_authorization");
        $entityMaps[] = new EntityMap("eager", "getActiveParcels");
        $entityMaps[] = new EntityMap("eager", "getCarboyUseCycles");
        $entityMaps[] = new EntityMap("eager", "getPurchaseOrders");
        $entityMaps[] = new EntityMap("eager", "getSolidsContainers");
        $entityMaps[] = new EntityMap("eager", "getPickups");
        $entityMaps[] = new EntityMap("eager", "getScintVialCollections");
        $entityMaps[] = new EntityMap("eager", "getCurrentScintVialCollections");
        $entityMaps[] = new EntityMap("lazy","getOpenInspections");
        $entityMaps[] = new EntityMap("eager","getQuarterly_inventories");
        $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
        $entityMaps[] = new EntityMap("lazy","getVerifications");
        $entityMaps[] = new EntityMap("eager","getWipeTests");

        $authMaps = array();
        $authMaps[] = new EntityMap("lazy", "getRooms");
        $authMaps[] = new EntityMap("eager", "getAuthorizations");
        $authMaps[] = new EntityMap("lazy", "getDepartments");

        $parcelMaps = array();
        $parcelMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
		$parcelMaps[] = new EntityMap("lazy", "getPurchase_order");
		$parcelMaps[] = new EntityMap("lazy", "getIsotope");
		$parcelMaps[] = new EntityMap("eager", "getParcelUses");
		$parcelMaps[] = new EntityMap("eager", "getRemainder");
		$parcelMaps[] = new EntityMap("lazy", "getWipe_test");

        $useMaps = array();
        $useMaps[] = new EntityMap("lazy", "getParcel");
		$useMaps[] = new EntityMap("eager", "getParcelUseAmounts");

        $amountMaps = array();
    	$amountMaps[] = new EntityMap("eager", "getCarboy");
    	$amountMaps[] = new EntityMap("eager", "getWaste_type");
    	$amountMaps[] = new EntityMap("eager", "getContainer_name");

        $cycleMaps = array();
		$cycleMaps[] = new EntityMap("eager", "getCarboy");
		$cycleMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
		$cycleMaps[] = new EntityMap("lazy", "getParcelUseAmounts");
		$cycleMaps[] = new EntityMap("eager", "getContents");
		$cycleMaps[] = new EntityMap("lazy", "getCarboy_reading_amounts");
		$cycleMaps[] = new EntityMap("lazy", "getRoom");
		$cycleMaps[] = new EntityMap("lazy", "getPickup");
		$cycleMaps[] = new EntityMap("lazy", "getPour_allowed_date");

        if($pi->getPi_authorization() != NULL){
        	$piAuths = $pi->getPi_authorization();
            foreach($piAuths as $piAuth){
                $piAuth->setEntityMaps($authMaps);
            }
        }

        foreach($pi->getActiveParcels() as $parcel){
            $parcel->setEntityMaps($parcelMaps);
            foreach($parcel->getParcelUses() as $use){
                $use->setEntityMaps($useMaps);
                foreach($use->getParcelUseAmounts() as $amount){
                    $amount->setEntityMaps($amountMaps);
                }
            }
        }

        foreach($pi->getCarboyUseCycles() as $cycle){
            $cycle->setEntityMaps(eager);
        }

        $pi->setEntityMaps($entityMaps);
        $LOG = Logger::getLogger(__CLASS__);
        return $pi;

    }

    public function getAllSVCollections(){
        $dao = $this->getDao(new ScintVialCollection());
        $collections = $dao->getAll();
        return $collections;
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
            return $selectedParcel->getParcelUses();
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

    public function getAllCarboyReadingAmounts(){
        $dao = $this->getDao(new CarboyReadingAmount());
        return $dao->getAll();
    }

    function getAllDrums() {
        $drumDao = $this->getDao(new Drum());
        return $drumDao->getAll();
    }

    function getAllIsotopes() {
        $isotopeDao = $this->getDao(new Isotope());
        return $isotopeDao->getAll('name', true);
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
        $entityMaps = array();
        $entityMaps[] = new EntityMap("eager", "getCarboy_use_cycles");
        $entityMaps[] = new EntityMap("eager", "getWaste_bags");
        $entityMaps[] = new EntityMap("eager", "getScint_vial_collections");
        $entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");

        $pickups = $dao->getAll();
        foreach($pickups as $pickup){
            $pickup->setEntityMaps($entityMaps);
        }

        return $pickups;
    }

    public function getAllActivePickups(){
        $allPickups = $this->getAllPickups();
        $activePickups = array();
        foreach ($allPickups as $pickup){
            if($pickup->getStatus() == "REQUESTED" || $pickup->getStatus() == "PICKED UP"){
                $activePickups[] = $pickup;
            }
        }
        return $activePickups;
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

    function getAllMiscellaneousWipeTests(){
        $dao = $this->getDao(new MiscellaneousWipeTest());
        return $dao->getAll();
    }

    function getOpenMiscellaneousWipeTests(){
        foreach($this->getAllMiscellaneousWipeTests() as $test){
            $openTests = array();
            if($test->getIs_active() == TRUE && $test->getClosoutDate() != '0000-00-00 00:00:00'){
                $openTests[] = $test;
            }
        }
        return $openTests;
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
            if($decodedObject->getApproval_date == NULL){
                $decodedObject->setApproval_date(date('Y-m-d H:i:s'));
            }
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

            //set the half_life in days, based on the display_half_life and unit
            //default to days, don't change half-life
            $factor = 1;
            if( $decodedObject->getUnit() == "Years" ){
            	//half life in years, 365.25 (days in a year) is the magic number, yes it is.
            	$factor = 365.25;
            }elseif ( $decodedObject->getUnit() == "Hours" ){
            	//half life in hours, 1/24 (days in an hour) is the magic number, yes it is.
            	$factor = 1/24;
            }
            $decodedObject->setHalf_life( $decodedObject->getDisplay_half_life() * $factor );
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
        else if ( $decodedObject instanceof ActionError ) {
            return $decodedObject;
        }
        else {
            $key_id = $decodedObject->getKey_id();
            $dao = $this->getDao(new Carboy());
            $carboy = $dao->save($decodedObject);
            if ( $decodedObject->getKey_id() == NULL ) {
                $carboy->setCommission_date( date('Y-m-d H:i:s') );
                $carboy = $dao->save($carboy);
            }

            if($carboy->getCarboy_use_cycles() == NULL){
                $carboyCycle = new CarboyUseCycle();
                $carboyCycle->setCarboy_id( $carboy->getKey_id() );
                $carboyCycle->setStatus( "Available" );
                $carboyUseCycle_dao = $this->getDao($carboyCycle);
                $savedCarboyCycle = $carboyUseCycle_dao->save($carboyCycle);
            }

            $entityMaps = array();
            $entityMaps[] = new EntityMap("eager", "getCarboy_use_cycles");
            $carboy->setEntityMaps($entityMaps);

            return $carboy;
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

            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy", "getCarboy");
            $entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
            $entityMaps[] = new EntityMap("lazy", "getParcelUseAmounts");
            $entityMaps[] = new EntityMap("eager", "getContents");
            $entityMaps[] = new EntityMap("eager", "getCarboy_reading_amounts");
            $entityMaps[] = new EntityMap("lazy", "getRoom");
            $entityMaps[] = new EntityMap("lazy", "getPickup");
            $entityMaps[] = new EntityMap("eager", "getPour_allowed_date");
            $decodedObject->setEntityMaps($entityMaps);
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
            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
            $entityMaps[] = new EntityMap("lazy", "getPurchase_order");
            $entityMaps[] = new EntityMap("lazy", "getIsotope");
            $entityMaps[] = new EntityMap("lazy", "getParcelUses");
            $entityMaps[] = new EntityMap("eager", "getRemainder");
            $entityMaps[] = new EntityMap("eager", "getWipe_test");
            $decodedObject->setEntityMaps($entityMaps);
            return $decodedObject;
        }
    }

    function saveParcelWipesAndChildren() {
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
            $tests = $decodedObject->getWipe_test ();
            if($decodedObject->getStatus() == "Ordered" && $tests != null){
                $decodedObject->setStatus("Wipe Tested");
                $decodedObject= new Parcel();
                $decodedObject->setArrival_date(date('Y-m-d G:i:s'));
            }

            $decodedObject = $dao->save($decodedObject);

            if ( $tests != null ) {
                $test = JsonManager::assembleObjectFromDecodedArray ( $tests[0][0] );
                $wipes = $test->getParcel_wipes();
                $wipeTestDao = $this->getDao ( new ParcelWipeTest () );
                $savedTest = $wipeTestDao->save($test);

                //$LOG->fatal( $test );
                foreach ( $wipes as $wipe ) {
                    $wipe = JsonManager::assembleObjectFromDecodedArray ( $wipe );
                    // there will be a collection of at least 6 ParcelWipes. User intends only to save those with Location provided
                    if ($wipe->getLocation () != null) {
                        $dao = $this->getDao ( new ParcelWipe () );
                        $wipe->setParcel_wipe_test_id($savedTest->getKey_id());
                        $wipes[] = $dao->save ( $wipe );
                    }
                }
            }

            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
            $entityMaps[] = new EntityMap("lazy", "getPurchase_order");
            $entityMaps[] = new EntityMap("lazy", "getIsotope");
            $entityMaps[] = new EntityMap("lazy", "getParcelUses");
            $entityMaps[] = new EntityMap("eager", "getRemainder");
            $entityMaps[] = new EntityMap("eager", "getWipe_test");
            $decodedObject->setEntityMaps($entityMaps);
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

            if($decodedObject->getDate_transferred() != null){
                //this is a use for a transfer
                if($decodedObject->getDestinationParcel() != null){
                    $p =  $decodedObject->getDestinationParcel();
                    $p->setIs_active(true);
                    $p->setArrival_date($p->getTransfer_in_date());
                    $authDao = $this->getDao(new Authorization());
                    $piAuthDao = $this->getDao(new PIAuthorization());
                    $auth = $authDao->getById($p->getAuthorization_id());
                    if($auth != null){
                        $piAuth = $piAuthDao->getById($auth->getPi_authorization_id());
                        if($piAuth != null){
                            $p->setPrincipal_investigator_id($piAuth->getPrincipal_investigator_id());
                        }
                    }

                    $parcelDao = new GenericDAO(new Parcel());
                    $p->setQuantity($decodedObject->getQuantity());
                    $dParcel = $parcelDao->save($p);
                    $decodedObject->setDestination_parcel_id($dParcel->getKey_id());
                }
                $use = $dao->save($decodedObject);
                if($dParcel != null){
                    $dParcel->setTransfer_amount_id($use->getKey_id());
                    $dParcel = $parcelDao->save($dParcel);
                }
                //do we already have a parcelUseAmount for this parcel?
                $amountDao = new GenericDAO(new ParcelUseAmount());
                if($decodedObject->getParcelUseAmounts() != null){
                    $amount = end($decodedObject->getParcelUseAmounts());
                    if(is_array($amount)) $amount = JsonManager::assembleObjectFromDecodedArray($amount);
                }else{
                    $amount = new ParcelUseAmount();
                }
                $amount->setParcel_use_id($use->getKey_id());
                $amount->setCurie_level($use->getQuantity());
                $amount = $amountDao->save($amount);
            }else{
                $use = $dao->save($decodedObject);

                $amounts = $decodedObject->getParcelUseAmounts();
                foreach($amounts as $amount){
                    $amountDao = $this->getDao(new ParcelUseAmount());
                    $newAmount = new ParcelUseAmount();
                    if($amount['Curie_level'] != NULL && $amount['Curie_level'] > 0){
                        $newAmount->setParcel_use_id($use->getKey_id());
                        $newAmount->setCurie_level($amount['Curie_level']);
                        if($amount['Key_id'] != NULL)$newAmount->setKey_id($amount['Key_id']);

                        if($amount['Waste_bag_id'] != NULL){

                            if($newAmount->getKey_id() != null){
                                //this amount has been saved previously.
                                //We need to make sure that we aren't accidentally moving it to a new waste bag in the same container
                                $amountDao = $this->getDao(new ParcelUseAmount());
                                $relevantAmount = $amountDao->getById($newAmount->getKey_id());
                                $bag = $this->getWasteBagById($amount['Waste_bag_id']);
                                $oldBag = $this->getWasteBagById($relevantAmount->getWaste_bag_id());
                                if( $oldBag == null || ( $oldBag->getContainer_id() != $bag->getContainer_id() ) ){
                                    $newAmount->setWaste_bag_id($amount['Waste_bag_id']);
                                }else{
                                    $newAmount->setWaste_bag_id($relevantAmount->getWaste_bag_id());
                                }
                            }
                            //this ParcelUseAmount is either new or was missing its waste_bag_id
                            else{
                                $newAmount->setWaste_bag_id($amount['Waste_bag_id']);
                            }
                        }
                        if($amount['Carboy_id'] != NULL){
                            $newAmount->setCarboy_id($amount['Carboy_id']);
                            $entityMaps = array();
                            $entityMaps[] = new EntityMap("eager", "getCarboy");
                            $entityMaps[] = new EntityMap("lazy", "getWaste_type");
                            $entityMaps[] = new EntityMap("lazy", "getContainer_name");
                            $newAmount->setEntityMaps($entityMaps);
                        }
                        if($amount['Comments'] != NULL)$newAmount->setComments($amount['Comments']);
                        $newAmount->setWaste_type_id($amount['Waste_type_id']);

                        if($newAmount->getWaste_type()->getName() == "Vial"){
                            //get the pi
                            $use = $this->getParcelUseById($newAmount->getParcel_use_id());
                            $parcelDao = $this->getDao(new Parcel());
                            $parcel = $parcelDao->getById($use->getParcel_id());
                            $authDao = $this->getDao(new Authorization());
                            $auth = $authDao->getById($parcel->getAuthorization_id());
                            $piAuthDao = $this->getDao(new PIAuthorization());
                            $piAuth = $piAuthDao->getById($auth->getPi_authorization_id());
                            $pi = $this->getPIById($piAuth->getPrincipal_investigator_id());
                            if($pi->getCurrentScintVialCollections() == null){

                                //make new svCollection
                                $collectionDao = $this->getDao(new ScintVialCollection());
                                $collection = new ScintVialCollection();
                                $collection->setIs_active(true);
                                $collection->setPrincipal_investigator_id($pi->getKey_id());
                                $collection = $collectionDao->save($collection);

                                //$LOG->fatal($collection);

                                $newAmount->setScint_vial_collection_id($collection->getKey_id());
                            }else{
                                $id = end($pi->getCurrentScintVialCollections())->getKey_id();
                                $newAmount->setScint_vial_collection_id($id);
                            }
                        }

                        $amountDao->save($newAmount);
                    }
                    //if a ParcelUseAmount has no activity, we assume it's supposed to be deleted
                    else{
                        if($amount['Key_id'] != NULL){
                            $amountDao = $this->getDao(new ParcelUseAmount());
                            $amountDao->deleteById($amount['Key_id']);
                        }
                    }
                }
            }

            $entityMaps = array();
		    $entityMaps[] = new EntityMap("eager", "getParcel");
		    $entityMaps[] = new EntityMap("eager", "getParcelUseAmounts");
            $entityMaps[] = new EntityMap("eager", "getParcelAmountOnHand");
            $entityMaps[] = new EntityMap("eager", "getParcelRemainder");
            $entityMaps[] = new EntityMap("eager", "getDestinationParcel");


            $use->setEntityMaps($entityMaps);
            return $use;
        }
    }

    function savePickup($saveChildren = null) {
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
            $pickup = $dao->save($decodedObject);
            $wasteBags = $decodedObject->getWaste_bags();
            $svCollections = $decodedObject->getScint_vial_collections();
            $carboys = $decodedObject->getCarboy_use_cycles();
            $saveChildren = $this->getValueFromRequest('saveChildren', $saveChildren);

            if($saveChildren != NULL){
                foreach($wasteBags as $bagArray){
                    $LOG->debug('bag with key id '+$bagArray['Key_id']);
                    $bagDao = $this->getDao(new WasteBag());
                    $bag = $bagDao->getById($bagArray['Key_id']);
                    $bag->setPickup_id($pickup->getKey_id());
                    $bagDao->save($bag);
                    //if this pickup has been picked up, we but a new waste bag in the container that previously held the bag
                    if($decodedObject->getStatus() == "PICKED UP"){
                        $this->changeWasteBag($bag);
                    }
                }

                foreach($svCollections as $collectionArray){
                    $LOG->debug('collection with key id ');
                    $svColDao = $this->getDao(new ScintVialCollection());
                    $collection = $svColDao->getById($collectionArray['Key_id']);
                    $collection->setPickup_id($pickup->getKey_id());
                    $svColDao->save($collection);
                }

                foreach($carboys as $carboyArray){
                    $LOG->debug('carboyUseCycle with key id '+$carboyArray['Key_id']);
                    $carboyDao = $this->getDao(new CarboyUseCycle());
                    $cycle = $carboyDao->getById($carboyArray['Key_id']);
                    $cycle->setPickup_id($pickup->getKey_id());
                    //carboy has been picked up.  If it is back at the radiation safety office, we set it to decaying and set its hot room date
                    if($decodedObject->getStatus() == "AT RSO"){
                        $cycle->setStatus("AT RSO");
                        $cycle->setHotroom_date(NULL);
                    }
                    elseif($decodedObject->getStatus() == "PICKED UP"){
                        $cycle->setStatus("Picked up");
                        $cycle->setHotroom_date(NULL);
                    }
                    $carboyDao->save($cycle);
                }
            }
            $entityMaps = array();
            $entityMaps[] = new EntityMap("eager", "getCarboy_use_cycles");
            $entityMaps[] = new EntityMap("eager", "getWaste_bags");
            $entityMaps[] = new EntityMap("eager", "getScint_vial_collections");
            $entityMaps[] = new EntityMap("eager", "getPrincipalInvestigator");
            $pickup->setEntityMaps($entityMaps);

            return $pickup;
        }
    }

    function saveSVCollection($collection = null){
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to Parcel', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new ScintVialCollection());
            $collection = $dao->save($decodedObject);
            return $collection;
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
    function changeWasteBag(WasteBag $bag = null) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        if($bag == null){
            $decodedObject = $this->convertInputJson();
        }else{
            $decodedObject = $bag;
        }
        $LOG->debug($decodedObject);
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteBag', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao($decodedObject);
            $decodedObject->setDate_removed(date('Y-m-d H:i:s'));
            //get the bags for the container and find the one we need to remove
            $bag = $dao->save($decodedObject);
            $container = $bag->getContainer();

            $group = new WhereClauseGroup(array(new WhereClause("container_id","=",$container->getKey_id())));
            $curBags = $dao->getAllWhere($group,"AND","date_removed");
            $latestBag = $curBags[0];
            $LOG->fatal($latestBag);
            if($latestBag == null || $latestBag->getDate_removed() != null){
                $newBag = new WasteBag();
                $newBag->setDate_added(date('Y-m-d H:i:s'));
                $newBag->setIs_active(true);
                $newBag->setContainer_id($container->getKey_id());
                return $dao->save($newBag);
            }

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
            $container = $dao->save($decodedObject);
            return $container;
        }
    }

    function saveInspectionWipeTest() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new InspectionWipeTest());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function saveInspectionWipes() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();

        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else if( $decodedObject->getInspection_wipes() == null) {
            return new ActionError('No Inspection wipes were passed', 202);
        }
        else {
            $wipes = array();
            foreach($decodedObject->getInspection_wipes() as $wipe){
                $wipe = JsonManager::assembleObjectFromDecodedArray($wipe);
                if($wipe->getLocation() != NULL){
                    //if this is the background wipe, set the parent InspectionWipe's background_level and lab_background_level properties
                    if($wipe->getLocation() == "Background"){
                        $LOG->debug('background');
                        $wipeTest = $wipe->getInspection_wipe_test();
                        $wipeTest->setBackground_level($wipe->getCurie_level());
                        $wipeTest->setLab_background_level($wipe->getLab_curie_level());
                        $wipeTestDao = $this->getDao(new InspectionWipeTest());
                        $wipeTestDao->save($wipeTest);
                    }

                    $dao = $this->getDao(new InspectionWipe());
                    $wipes[] = $dao->save($wipe);
                }
            }
            return $wipes;
        }
    }

    function saveInspectionWipe() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new InspectionWipe());
            $decodedObject = $dao->save($decodedObject);
            //if this is the background wipe, set the parent InspectionWipe's background_level and lab_background_level properties
            if($decodedObject->getLocation() == "Background"){
                $wipeTest = $decodedObject->getInspection_wipe_test();
                $wipeTest->setBackground_level($decodedObject->getCurie_level());
                $wipeTest->setLab_background_level($decodedObject->getLab_curie_level());
                $wipeTestDao = $this->getDao(new InspectionWipeTest());
                $wipeTestDao->save($wipeTest);
            }
            return $decodedObject;
        }
    }

    function saveParcelWipeTest() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new ParcelWipeTest());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function saveParcelWipes() {
        $LOG = Logger::getLogger ( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson ();
        if ($decodedObject === NULL) {
            return new ActionError ( 'Error converting input stream to WasteType', 202 );
        } else if ($decodedObject instanceof ActionError) {
            return $decodedObject;
        } else if ($decodedObject->getWipe_test() == null) {
            return new ActionError ( 'No Parcel wipes were passed', 202 );
        } else {
            if ($decodedObject->getKey_id () == null) {
                $wipeTestDao = $this->getDao ( new ParcelWipeTest () );
                $decodedObject = $wipeTestDao->save ( $decodedObject );
            }
            $wipes = array ();
            foreach ( $decodedObject->getWipe_test () as $wipe ) {
                // there will be a collection of at least 6 ParcelWipes. User intends only to save those with Location provided
                if ($wipe->getLocation () != null) {
                    $dao = $this->getDao ( new ParcelWipe () );
                    $wipes [] = $dao->save ( $wipe );
                }
            }
            return $wipes;
        }
    }

    function saveParcelWipe() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new ParcelWipe());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function saveMiscellaneousWipeTest() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        $LOG->debug($decodedObject);
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new MiscellaneousWipeTest());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function saveMiscellaneousWipes() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else if( $decodedObject->getMiscellaneous_wipes() == null) {
            return new ActionError('No Misc wipes were passed', 202);
        }
        else {
            $wipes = array();
            foreach($decodedObject->getMiscellaneous_wipes() as $wipe){
                $wipe = JsonManager::assembleObjectFromDecodedArray($wipe);
                //there will be a collection of at least 10 MiscellaneousWipes.  User intends only to save those with Location provided
                if($wipe->getLocation() != null){
                    $dao = $this->getDao(new MiscellaneousWipe());
                    $wipes[] = $dao->save($wipe);
                }
            }
            $LOG->debug($wipes);
            return $wipes;
        }
    }

    function saveMiscellaneousWipe() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new MiscellaneousWipe());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function getAllPIWipeTests(){
        $dao = $this->getDao(new PIWipeTest());
        return $dao->getAll();
    }

    function savePIWipeTest() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new PIWipeTest());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function savePIWipes() {
        $LOG = Logger::getLogger ( 'Action' . __FUNCTION__ );
        $decodedObject =  $this->convertInputJson ();
        if ($decodedObject === NULL) {
            return new ActionError ( 'Error converting input stream to WasteType', 202 );
        } else if ($decodedObject instanceof ActionError) {
            return $decodedObject;
        } else if ($decodedObject->getPIWipes() == null) {
            return new ActionError ( 'No Parcel wipes were passed', 202 );
        } else {
            if ($decodedObject->getKey_id () == null) {
                $wipeTestDao = $this->getDao ( new PIWipeTest () );
                $test = $wipeTestDao->save ( $decodedObject );
            }

            $wipes = array ();
            foreach ( $decodedObject->getPIWipes() as $wipe ) {
                $wipe = JsonManager::assembleObjectFromDecodedArray ( $wipe );
                if ($wipe->getLocation () != null) {
                    $dao = $this->getDao ( new PIWipe () );
                    $wipes [] = $dao->save ( $wipe );
                }
            }
            return $wipes;
        }
    }

    function savePIWipe() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new PIWipe());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function getAllDrumWipeTests(){
        $dao = $this->getDao(new DrumWipeTest);
        return $dao->getAll();
    }

    function getDrumWipeTestById(){
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);
        if($id !== NULL) {
            $dao = new GenericDAO(new DrumWipeTest());
            $test = $dao->getById($id);
            return $test;
        }
        else {
            return new ActionError("No request parameter 'id' was provided.");
        }
    }

    function getAllDrumWipes(){
        $dao = $this->getDao(new DrumWipe);
        return $dao->getAll();
    }

    function saveDrumWipeTest(){
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new DrumWipeTest());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function saveDrumWipe(){
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new DrumWipe());
            $decodedObject = $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    function saveDrumWipesAndChildren() {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to Parcel', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {

            $tests = $decodedObject->getWipe_test ();
            $newTests = array();
            foreach($tests as $test){
                $newTests[] = JsonManager::assembleObjectFromDecodedArray ( $test );
            }

            $decodedObject->setWipe_test($newTests);
            $test = $newTests[0];
            if ( $test != null ) {

                $wipes = $test->getDrum_wipes();

                $wipeTestDao = $this->getDao ( $test );
                $savedTest = $wipeTestDao->save($test);

                $wipeMaps = array();
                $wipeMaps[] = new EntityMap("lazy","getDrum");
                $wipeMaps[] = new EntityMap("eager","getDrum_wipes");

                foreach ( $wipes as $key=>$wipe ) {
                    $wipe = JsonManager::assembleObjectFromDecodedArray ( $wipe );
                    // there will be a collection of at least 3 DrumWipes. User intends only to save those with Curie_level provided
                    if ($wipe->getCurie_level () != null) {
                        $dao = $this->getDao ( new DrumWipe () );
                        $wipe->setDrum_wipe_test_id($savedTest->getKey_id());
                        $wipes[$key] = $dao->save ( $wipe );
                        $wipes[$key]->setEntityMaps($wipeMaps);
                    }
                }
            }

            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy", "getDrum");
            $entityMaps[] = new EntityMap("eager", "getWipe_test");
            $decodedObject->setEntityMaps($entityMaps);
            return $decodedObject;
        }
    }

    function saveCarboyReadingAmount($reading = null){
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteType', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new CarboyReadingAmount());
            $decodedObject->setDecayed_carboy_uci(null);
            $decodedObject = $dao->save($decodedObject);
            $LOG->fatal($decodedObject);
            $cycle = $decodedObject->getCarboy_use_cycle();
            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy", "getCarboy");
            $entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
            $entityMaps[] = new EntityMap("lazy", "getParcelUseAmounts");
            $entityMaps[] = new EntityMap("eager", "getContents");
            $entityMaps[] = new EntityMap("eager", "getCarboy_reading_amounts");
            $entityMaps[] = new EntityMap("lazy", "getRoom");
            $entityMaps[] = new EntityMap("lazy", "getPickup");
            $entityMaps[] = new EntityMap("eager", "getPour_allowed_date");
            $cycle->setEntityMaps($entityMaps);
            return $cycle;
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
            $parcelUses = $parcel->getParcelUses();

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
            $uses = $parcel->getParcelUses();

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

    function deletePickupById($id = null){
        if($id == null){
            $id = $this->getValueFromRequest('id', $id);
        }

        if($id == null){
            return new ActionError("No request parameter 'id' was provided");
        }

        $dao = $this->getDao(new Pickup());
        if($dao->deleteById($id) !== true){
            return new ActionError("No request parameter 'id' was provided");
        }

        return true;

    }

    function getInventoriesByDateRanges(){

    }

    /**
     * Creates, saves, and returns a collection of QuarterlyInventories for all PIs who have Rad Auths
     *
     */

    function createQuarterlyInventories( $dueDate = NULL, $endDate = null ){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

        $dueDate = $this->getValueFromRequest('dueDate', $dueDate);
        $endDate = $this->getValueFromRequest('endDate', $endDate);

        if( $dueDate == NULL && $endDate == NULL ) {
            return new ActionError("Request parameters 'dueDate' and 'endDate' were not provided");
        }
        if( $dueDate == NULL ) {
            return new ActionError("No request parameter 'dueDate' was provided");
        }
        if( $endDate == NULL ) {
            return new ActionError("No request parameter 'endDate' was provided");
        }

        $pis = $this->getAllRadPis();

        //create a master inventory, since all pis will have one with the same dates
        $inventoryDao = $this->getDao(new QuarterlyInventory());

        //make sure we only have one inventory for the given due and end dates
        $whereClauseGroup = new WhereClauseGroup();
        $clauses = array(
                new WhereClause('due_date','=', $dueDate ),
                new WhereClause('end_date', '=', $endDate)
        );
        $whereClauseGroup->setClauses($clauses);
        $inventories = $inventoryDao->getAllWhere($whereClauseGroup);

        //do we already have a master inventory for this period?
        if($inventories != NULL){
            $inventory = $inventories[0];
        }
        //we don't have one, so make one
        else{
            $inventory = new QuarterlyInventory();
            //get the last inventory so we can set the start date, if there is one
            $inventoryDao = $this->getDao(new QuarterlyInventory());
            $previousInventory = end($inventoryDao->getAll());
            if($previousInventory == NULL){
                $LOG->debug('was null');
                $time = strtotime("-1 year", time());
                $inventory->setStart_date(date('Y-m-d H:i:s', $time));
            }else{
                $LOG->debug('was not null');
                $inventory->setStart_date($previousInventory->getEnd_date());
            }

            $inventory->setDue_date($dueDate);
            $inventory->setEnd_date($endDate);
            $inventory->setIs_active(true);
            $inventory = $inventoryDao->save($inventory);
        }

        $piInventories = array();

        foreach($pis as $pi){
            $piInventory = $this->getPiInventory( $pi->getKey_id(), $inventory->getKey_id() );
            if($piInventory != NULL){
                $piInventories[] = $piInventory;
            }
        }

        $inventory->setPi_quarterly_inventories($piInventories);
        $entityMaps = array();
    	$entityMaps[] = new EntityMap("eager", "getQuarterly_isotope_amounts");
    	$entityMaps[] = new EntityMap("eager", "getPi_quarterly_inventories");
        $inventory->setEntityMaps($entityMaps);

        $LOG->fatal($inventory);

        return $inventory;
    }

    public function getPiInventory( $piId = NULL, $inventoryId = NULL ){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

        $inventoryId = $this->getValueFromRequest('inventoryId', $inventoryId);
        $piId = $this->getValueFromRequest('piId', $piId);

        if( $inventoryId == NULL && $piId == NULL ) {
            return new ActionError("Request parameters 'piId' and 'inventoryId' were not provided");
        }
        elseif( $inventoryId == NULL ) {
            return new ActionError("No request parameter 'inventoryId' was provided");
        }
        elseif( $piId == NULL ) {
            return new ActionError("No request parameter 'piId' was provided");
        }else{

            $inventoryDao = $this->getDao(new QuarterlyInventory());
            $inventory = $inventoryDao->getById($inventoryId);
            $startDate = $inventory->getStart_date();
            $endDate = $inventory->getEnd_date();
            $pi = $this->getPIById($piId, false);

        }
        if($pi->getPi_authorization() == NULL)return NULL;
        //make sure we only have one inventory for this pi for this period
        $piInventoryDao = $this->getDao(new PIQuarterlyInventory());

        $whereClauseGroup = new WhereClauseGroup();
        $clauses = array(
                new WhereClause('principal_investigator_id','=', $pi->getKey_id() ),
                new WhereClause('quarterly_inventory_id', '=', $inventory->getKey_id())
        );
        $whereClauseGroup->setClauses($clauses);

        $pastPiInventories = $piInventoryDao->getAllWhere($whereClauseGroup);
        if($pastPiInventories != NULL){
            $piInventory = $pastPiInventories[0];
        }
        else{
            $piInventory = new PIQuarterlyInventory();
            $piInventory->setQuarterly_inventory_id($inventory->getKey_id());
            $piInventory->setPrincipal_investigator_id($pi->getKey_id());
            $piInventory->setIs_active(true);
        }


        $piInventory = $piInventoryDao->save($piInventory);

        //get the most recent inventory for this PI so we can use the quantities of its QuarterlyIsotopeAmounts to set new ones
        //$pi->getQuarterly_inventories()'s query is ordered by date_modified column, so the last in the array will be the most recent
        $mostRecentIntentory = end($pi->getQuarterly_inventories());

        //build the QuarterlyIsotopeAmounts for each isotope the PI could have
        $amounts = array();
        foreach($pi->getPi_authorization() as $piAuth){

            foreach($piAuth->getAuthorizations() as $authorization){

                $quarterlyAmountDao = $this->getDao(new QuarterlyIsotopeAmount());

                //do we already have a QuarterlyIsotopeAmount?
                $whereClauseGroup = new WhereClauseGroup();
                $clauses = array(
                        new WhereClause('authorization_id','=', $authorization->getKey_id() ),
                        new WhereClause('quarterly_inventory_id','=', $piInventory ->getKey_id() ),
                );

                $whereClauseGroup->setClauses($clauses);
                $oldAmounts = $quarterlyAmountDao->getAllWhere($whereClauseGroup);

                if($oldAmounts != NULL){
                    $newAmount = $oldAmounts[0];
                }else{
                    $newAmount = new QuarterlyIsotopeAmount();
                }

                //boolean to determine if this isotope has been accounted for
                $isotopeFound = false;

                //if we have a previous inventory, find the matching isotope in the previous inventory, so we can get its amount at that time
                if($mostRecentIntentory != null){
                    foreach($mostRecentIntentory->getQuarterly_isotope_amounts() as $amount){
                        if($amount->getAuthorization_id() == $authorization->getIsotope_id()){
                            $newAmount->setStarting_amount($amount->getEnding_amount());
                            $isotopeFound = true;
                        }
                    }
                }

                $newAmount->setIs_active(true);
                $newAmount->setAuthorization_id($authorization->getKey_id());
                $newAmount->setQuarterly_inventory_id($piInventory->getKey_id());

                //calculate the decorator properties (use amounts, amounts received by PI as parcels and transfers, amount left on hand)
                $newAmount = $this->calculateQuarterlyAmount($newAmount, $startDate, $endDate);
                $newAmount = $quarterlyAmountDao->save($newAmount);

                $amounts[] = $newAmount;

            }
        }
        $piInventory->setQuarterly_isotope_amounts($amounts);
        return $piInventory;
    }

    /**
     * * calculate the values for the decorator properties of a QuarterlyInventoryAmount
     * @param QuarterlyIsotopeAmount $amount
     * @param string $startDate
     * @param string $endDate
     */
    private function calculateQuarterlyAmount($amount, $startDate, $endDate){
        $LOG = Logger::getLogger("calculon");
        //get the total ordered since the previous inventory
        $ordersDao = $this->getDao($amount);

        //if there wasn't a previous inventory, the amount's starting_amount will be null, and we need to query for it
        if($amount->getStarting_amount() == NULL){
            $amount->setStarting_amount($ordersDao->getTransferAmounts('0000-00-00 00:00:00', $startDate));
        }


        //get parcels for this QuarterlyIsotopsAmount's authorization that have an RS ID for the given dates
        $amount->setTotal_ordered($ordersDao->getTransferAmounts($startDate, $endDate, false));

        //get the total transfered in since the previous inventory
        $transferInDao = $this->getDao($amount);

        //get parcels for this QuarterlyIsotopsAmount's authorization that DON'T have an RSID for the given dates
        $amount->setTransfer_in($transferInDao->getTransferAmounts($startDate, $endDate, true));


        //get the total transfered out since the previous inventory
        //??what is a tranfer out?
        $amount->setTransfer_out($transferInDao->getTransferOutAmounts($startDate, $endDate));

        //subtract this quarters parcel uses, going by parcel use amount, maintaining a count of each kind of disposal (liquid, solid or scintvial)

        //get liquid amounts
        $liquidSumDao = $this->getDao($amount);
        $amount->setLiquid_waste($liquidSumDao->getUsageAmounts( $startDate, $endDate, 1));

        //get scint vial amounts
        $svSumDao = $this->getDao($amount);
        $amount->setScint_vial_waste($svSumDao->getUsageAmounts( $startDate, $endDate, 3));

        //get solid amounts
        $svSumDao = $this->getDao($amount);
        $amount->setSolid_waste($svSumDao->getUsageAmounts( $startDate, $endDate, 5));

        //get other amounts
        $solidSumDao = $this->getDao($amount);
        $amount->getOther_waste($solidSumDao->getUsageAmounts( $startDate, $endDate, 4));

        //calculate the amount currently on hand
        $totalIn = $amount->getStarting_amount() + $amount->getTransfer_in() + $amount->getTotal_ordered();
        $totalOut = $amount->getTransfer_out() + $amount->getSolid_waste() + $amount->getLiquid_waste() + $amount->getOther_waste() + $amount->getScint_vial_waste();

        $amount->setOn_hand($totalIn - $totalOut);
        return $amount;

    }

    public function getCurrentPIInventory($piId){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

        $piId = $this->getValueFromRequest('piId', $piId);

        if( $piId == NULL ) {
            return new ActionError("No request parameter 'piId' was provided");
        }else{
            $pi = $this->getPIById($piId, false);
        }

        $pi_inventory = end($pi->getQuarterly_inventories());
        $qinventory = $pi_inventory->getQuarterly_inventory();
        $startDate = $qinventory->getStart_date();
        $endDate = $qinventory->getEnd_date();
        //get the most recent inventory for this PI so we can use the quantities of its QuarterlyIsotopeAmounts to set new ones
        //$pi->getQuarterly_inventories()'s query is ordered by date_modified column, so the last in the array will be the most recent
        $mostRecentIntentory = end($pi->getQuarterly_inventories());

        if($pi_inventory == NULL){
            return NULL;
        }

        //build the QuarterlyIsotopeAmounts for each isotope the PI could have
        $amounts = array();
        $piAuth = $pi->getCurrentPi_authorization();

        $auths = $piAuth->getAuthorizations();
        foreach($auths as $authorization){
            $isotopeFound = false;

            $quarterlyAmountDao = $this->getDao(new QuarterlyIsotopeAmount());

            //do we already have a QuarterlyIsotopeAmount?
            $whereClauseGroup = new WhereClauseGroup();
            $clauses = array(
                    new WhereClause('authorization_id','=', $authorization->getKey_id() ),
                    new WhereClause('quarterly_inventory_id','=', $pi_inventory->getKey_id() ),
            );

            $whereClauseGroup->setClauses($clauses);
            $oldAmounts = $quarterlyAmountDao->getAllWhere($whereClauseGroup);

            if($oldAmounts != NULL){
                $newAmount = $oldAmounts[0];
            }else{
                $newAmount = new QuarterlyIsotopeAmount();
                $newAmount->setAuthorization_id($authorization->getKey_id());
            }
            $newAmount->setQuarterly_inventory_id($mostRecentIntentory->getKey_id());

            //if we have a previous inventory, find the matching isotope in the previous inventory, so we can get its amount at that time
            if($mostRecentIntentory != null){
                foreach($mostRecentIntentory->getQuarterly_isotope_amounts() as $amount){
                    if($amount->getAuthorization_id() == $authorization->getIsotope_id()){
                        $newAmount->setStarting_amount($amount->getEnding_amount());
                        $isotopeFound = true;
                    }
                }
            }

            //there wasn't an record of this isotope for the previous quarter, so we assume the starting amount to be 0
            if($isotopeFound == false){
                $newAmount->setStarting_amount(0);
            }

            //calculate the decorator properties (use amounts, amounts received by PI as parcels and transfers, amount left on hand)
            $newAmount = $this->calculateQuarterlyAmount($newAmount, $startDate, $endDate);
            $LOG->fatal($newAmount);

            $newAmount = $quarterlyAmountDao->save($newAmount);
            $LOG->fatal($newAmount);
            $auth = $newAmount->getAuthorization();
            if($auth != null){
                $entityMaps = array();
                $entityMaps[] = new EntityMap("eager", "getIsotope");
                $newAmount->getAuthorization()->setEntityMaps($entityMaps);
                $amounts[] = $newAmount;
            }
        }

        $LOG->fatal($amounts);

        $pi_inventory->setQuarterly_isotope_amounts($amounts);
        $entityMaps = array();
        $entityMaps[] = new EntityMap("eager", "getQuarterly_isotope_amounts");
        $entityMaps[] = new EntityMap("lazy", "getQuarterly_inventory");
        $pi_inventory->setEntityMaps($entityMaps);

        return $pi_inventory;
    }

    public function getMostRecentInventory(){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

        $inventoryDao = $this->getDao(new QuarterlyInventory());
        $LOG->debug(end($inventoryDao->getAll("end_date")));
        return end($inventoryDao->getAll("end_date"));
    }


    public function getInventoriesByPiId( $piId = NULL ){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
        $piId = $this->getValueFromRequest("piId", $piId);

        $inventoriesDao = $this->getDao(new PIQuarterlyInventory());
        $clauses = array(new WhereClause("principal_investigator_id", "=", $piId));
        $whereClauseGroup = new WhereClauseGroup($clauses);
        $inventories=  $inventoriesDao->getAllWhere($whereClauseGroup);

        $entityMaps = array();
        $entityMaps[] = new EntityMap("lazy", "getQuarterly_isotope_amounts");
        $entityMaps[] = new EntityMap("eager", "getQuarterly_inventory");

        foreach($inventories as $inventory){
            $inventory->setEntityMaps($entityMaps);
        }

        return $inventories;
    }

    public function getPIInventoryById( $piId = NULL ){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
        $piId = $this->getValueFromRequest("piId", $piId);

        $inventoriesDao = $this->getDao(new PIQuarterlyInventory());
        $clauses = array(new WhereClause("principal_investigator_id", "=", $piId));
        $whereClauseGroup = new WhereClauseGroup($clauses);
        $inventories=  $inventoriesDao->getAllWhere($whereClauseGroup);

        $entityMaps = array();
        $entityMaps[] = new EntityMap("lazy", "getQuarterly_isotope_amounts");
        $entityMaps[] = new EntityMap("eager", "getQuarterly_inventory");

        foreach($inventories as $inventory){
            $inventory->setEntityMaps($entityMaps);
        }

        return $inventories;
    }


    public function savePIQuarterlyInventory($inventory = NULL){
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to PIQuarterlyInventory', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new PIQuarterlyInventory());
            $piq = $dao->save($decodedObject);
            return $piq;
        }
    }

    public function getQuartleryInventoryById($id = null){

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new QuarterlyInventory());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    public function getAllPIAuthorizations(){
    	$dao = $this->getDao(new PIAuthorization());
    	$auths = $dao->getAll();
    	return $auths;
    }

    public function getPIAuthorizationByPIId(){
    	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
    	$id = $this->getValueFromRequest("id", $id);

    	$inventoriesDao = $this->getDao(new PIAuthorization());
    	$clauses = array(new WhereClause("principal_investigator_id", "=", $id));
    	$whereClauseGroup = new WhereClauseGroup($clauses);
    	$auth =  reset($inventoriesDao->getAllWhere($whereClauseGroup));
    	return $auth;
    }

    public function getPIAuthorizationById($id = null){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

        if( $id == NULL ){
            $id = $this->getValueFromRequest('id', $id);
        }

        if( $id !== NULL ){
            $dao = $this->getDao(new PIAuthorization());
            $piAuth =  $dao->getById($id);
            return $piAuth;
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }

    }

    public function savePIAuthorization(){
    	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
    	$decodedObject = $this->convertInputJson();

    	if( $decodedObject === NULL ) {
    		return new ActionError('Error converting input stream to PIAuthorization', 202);
    	}
    	else if( $decodedObject instanceof ActionError) {
    		return $decodedObject;
    	}
    	else {
    		//remove all the departments and rooms from the Authorization, if it is an old one
    		if($decodedObject->getKey_id() != NULL){
    			$origDao = $this->getDao(new PIAuthorization());
    			$origAuth = $origDao->getById($decodedObject->getKey_id());

    			foreach($origAuth->getRooms() as $room){
    				$origDao->removeRelatedItems($room->getKey_id(),$origAuth->getKey_id(),DataRelationship::fromArray(PIAuthorization::$ROOMS_RELATIONSHIP));
    			}

    			foreach($origAuth->getDepartments() as $dept){
    				$origDao->removeRelatedItems($dept->getKey_id(),$origAuth->getKey_id(),DataRelationship::fromArray(PIAuthorization::$DEPARTMENTS_RELATIONSHIP));
    			}
    		}

    		$rooms = $decodedObject->getRooms();
    		$departments = $decodedObject->getDepartments();

    		$dao = $this->getDao(new PIAuthorization());

            if($decodedObject->getKey_id() != null){
                $piAuth = $this->getPIAuthorizationById($decodedObject->getKey_id());
            }

            $id = $decodedObject->getKey_id();
    		$piAuth = $dao->save($decodedObject);
    		// add the relevant rooms and departments to the db
    		foreach($rooms as $room){
    			$dao->addRelatedItems($room["Key_id"],$decodedObject->getKey_id(),DataRelationship::fromArray(PIAuthorization::$ROOMS_RELATIONSHIP));
    		}

    		foreach($departments as $dept){
    			$dao->addRelatedItems($dept["Key_id"],$decodedObject->getKey_id(),DataRelationship::fromArray(PIAuthorization::$DEPARTMENTS_RELATIONSHIP));
    		}


			//New PIAuthorizations may be amendments of old ones, in which case we save relationships for child Authorizations, if any
			if($decodedObject->getAuthorizations() != NULL){
                $authDao = new GenericDAO(new Authorization());

				foreach($decodedObject->getAuthorizations() as $auth){
					$newAuth = new Authorization();
					$newAuth->setPi_authorization_id($piAuth->getKey_id());
					$newAuth->setIsotope_id($auth["Isotope_id"]);
					$newAuth->setMax_quantity($auth["Max_quantity"]);
					$newAuth->setApproval_date($auth["Approval_date"]);
                    $newAuth->setForm($auth["Form"]);
					$newAuth->setIs_active($decodedObject->getTermination_date == null);
                    //if the PiAuthorization has a key_id, we know we are editing one that already exists.
                    //In that case, we should save it's old authorizations, rather than creating new ones, so we set the key_id for each of them
                    if($id != null){
                        $newAuth->setKey_id($auth["Key_id"]);
                        $newAuth->setDate_created($auth["Date_created"]);
                    }
					$newAuth = $authDao->save($newAuth);
                }
            }
            //force reload of authorizations from db
            $piAuth->setAuthorizations(null);

    		return $piAuth;
    	}


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

    public function getAllInspectionWipes(){
    	$dao = $this->getDao(new InspectionWipe());
    	$wipes = $dao->getAll();
    	return $wipes;
    }

    public function getAllInspectionWipeTests(){
    	$dao = $this->getDao(new InspectionWipeTest());
    	$tests = $dao->getAll();
    	return $tests;
    }

    public function getAllScintVialCollections(){
    	$dao = $this->getDao(new ScintVialCollection());
    	$collections = $dao->getAll();
    	return $collections;
    }


    public function getAllMiscellaneousWaste(){
    	$dao = $this->getDao(new MiscellaneousWaste());
    	$miscs = $dao->getAll();
    	return $miscs;
    }

    public function getAllParcelWipes(){
    	$dao = $this->getDao(new ParcelWipe());
    	$wipes = $dao->getAll();
    	return $wipes;
    }

    public function getAllParcelWipeTests(){
    	$dao = $this->getDao(new ParcelWipeTest());
    	$tests = $dao->getAll();
    	return $tests;
    }

    public function getAllQuarterlyInventories(){
    	$dao = $this->getDao(new QuarterlyInventory());
    	$inventories = $dao->getAll();
    	return $inventories;
    }

    public function getAllPIQuarterlyInventories(){
    	$dao = $this->getDao(new PIQuarterlyInventory());
    	$inventories = $dao->getAll();
    	return $inventories;
    }

    public function getRadModels(){
    	$dto = new RadModelDto();
    	$dto->setUser($this->getAllRadUsers());
    	$dto->setAuthorization($this->getAllAuthorizations());
    	$dto->setPIAuthorization($this->getAllPIAuthorizations());
    	$dto->setCarboy($this->getAllCarboys());
    	$dto->setCarboyUseCycle($this->getAllCarboyUseCycles());
        $dto->setCarboyReadingAmount($this->getAllCarboyReadingAmounts());
    	$dto->setDrum($this->getAllDrums());
    	$dto->setDepartment($this->getAllDepartments());
    	$dto->setInspectionWipe($this->getAllInspectionWipes());
    	$dto->setInspectionWipeTest($this->getAllInspectionWipeTests());
    	$dto->setIsotope($this->getAllIsotopes());
    	$dto->setParcelUseAmount($this->getAllParcelUseAmounts());
    	$dto->setParcelUse($this->getAllParcelUses());
    	$dto->setParcelWipe($this->getAllParcelWipes());
    	$dto->setParcelWipeTest($this->getAllParcelWipeTests());
    	$dto->setParcel($this->getAllParcels());
    	$dto->setPickup($this->getAllPickups());
    	$dto->setPurchaseOrder($this->getAllPurchaseOrders());
    	//$dto->getQuarterlyIsotopeAmount($this->getAllQuarterlyInventories());
    	//$dto->setQuarterlInventory($this->getMostRecentInventory());
    	//$dto->setPIQuarterlyInventory($this->getAllPIQuarterlyInventories());
    	$dto->setScintVialCollection($this->getAllScintVialCollections());
        $dto->setMiscellaneousWaste($this->getAllMiscellaneousWaste());
    	$dto->setWasteBag($this->getAllWasteBags());
    	$dto->setSolidsContainer($this->getAllSolidsContainers());
    	$dto->setWasteType($this->getAllWasteTypes());
    	$dto->setRoom($this->getAllRooms(true));
    	$dto->setPrincipalInvestigator($this->getAllRadPis());
        $dto->setDrumWipe($this->getAllDrumWipes());
        $dto->setDrumWipeTest($this->getAllDrumWipeTests());

    	return $dto;

    }

    /*
     *@param MiscellaneousWaste $waste
     *@return MiscellaneousWaste $savedWasted
     */
    function saveMiscellaneousWaste(MiscellaneousWaste $waste){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
        if($waste == null){
            $waste = $this->convertInputJson();
        }
    	if( $waste === NULL ) {
    		return new ActionError('Error converting input stream to MiscWaste', 202);
    	}
    	else if( $waste instanceof ActionError) {
    		return $waste;
    	}

        //$LOG->fatal($waste);

        $dao = $this->getDao($waste);
        $savedWaste = $dao->save($waste);

        $amountDao = $this->getDao(new ParcelUseAmount());
        $amts = $waste->getParcel_use_amounts();
        foreach($amts as &$amount){
            if(is_array($amount)){
                $amt = new ParcelUseAmount();
                $amt->setCurie_level($amount["Curie_level"]);
                $amt->setIsotope_id($amount["Isotope_id"]);
            }else{
                $amt = clone $amount;
            }
            $amt->setMiscellaneous_waste_id($savedWaste->getKey_id());
            //set the waste type to "Other"
            $amt->setWaste_type_id(4);
            $amt = $amountDao->save($amt);
            $LOG->fatal($amt);
            $amount = $amt;
        }
        $entityMaps = array();
		$entityMaps[] = new EntityMap("eager", "getParcel_use_amounts");
		$entityMaps[] = new EntityMap("lazy", "getPickup");
		$entityMaps[] = new EntityMap("lazy", "getDrum");
		$entityMaps[] = new EntityMap("eager", "getContents");

		$savedWaste->setEntityMaps($entityMaps);
        return $savedWaste;
    }
}

?>

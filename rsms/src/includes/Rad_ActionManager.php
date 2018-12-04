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
    public function getAllRadRooms($allLazy = NULL){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $dao = $this->getDao(new Room());

        $rooms = $dao->getAll();

        // initialize an array of entityMap settings to assign to rooms, instructing them to lazy-load children
        // necessary because rooms by default eager-load buildings, and this would set up an infinite load loop between building->room->building->room...
        $roomMaps = array();
	    $roomMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
	    $roomMaps[] = new EntityMap("lazy","getHazards");
	    $roomMaps[] = new EntityMap("lazy","getBuilding");
	    $roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
	    $roomMaps[] = new EntityMap("lazy","getHas_hazards");
	    $roomMaps[] = new EntityMap("lazy","getSolidsContainers");

        foreach($rooms as $room){
            $room->setEntityMaps($roomMaps);
        }

        return $rooms;
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

        if($id == null)$id = $this->getValueFromRequest('id', $id);

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
            $bag = $dao->getById($id);

            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy", "getContainer");
            $entityMaps[] = new EntityMap("lazy", "getPickup");
            $entityMaps[] = new EntityMap("lazy", "getDrum");
            $entityMaps[] = new EntityMap("eager", "getParcelUseAmounts");

            $bag->setEntityMaps($entityMaps);
            return $bag;
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
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAll();

        $LOG->debug('Read ' . count($pis) . ' PIs from db');

        $entityMaps = array();

        // PrincipalInvestigator //
        $entityMaps[] = new EntityMap("eager","getDepartments");
        $entityMaps[] = new EntityMap("eager","getActiveParcels");
        $entityMaps[] = new EntityMap("eager","getPurchaseOrders");
        $entityMaps[] = new EntityMap("eager","getPi_authorization");
        $entityMaps[] = new EntityMap("eager","getUser");

        $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
        $entityMaps[] = new EntityMap("lazy","getRooms");
        $entityMaps[] = new EntityMap("lazy","getInspections");
        $entityMaps[] = new EntityMap("lazy","getPrincipal_investigator_room_relations");
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
        ////

        // Sub-objects...
        // Parcel //
        $entityMaps[] = new EntityMap("eager","getPurchase_order");
        $entityMaps[] = new EntityMap("eager","getIsotope");
        ////

        $LOG->debug("Filter PIs with no user...");
        foreach($pis as $key => $pi){
            // Filter out PIs with no User details
            if($pi->getName() == null){
                $LOG->debug("Remove PI " . $pi->getKey_Id() . " from list; it has no name");
                unset($pis[$key]);
                continue;
            }

            // Set entitymaps for future RAD operations
            $pi->setEntityMaps($entityMaps);
        }

        $LOG->debug("Returning " . count($pis) . " PIs");
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

        foreach($users as $key => $user){
            $user->setEntityMaps($entityMaps);
            if($user->getLast_name() == null)unset($users[$key]);
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
        $entityMaps[] = new EntityMap("eager","getLabPersonnel");
        if($rooms == null){
            $entityMaps[] = new EntityMap("lazy","getRooms");
        }else{
            $entityMaps[] = new EntityMap("eager","getRooms");
        }

        $entityMaps[] = new EntityMap("eager","getLabPersonnel");
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
		$entityMaps[] = new EntityMap("eager", "getWasteBags");
        $entityMaps[] = new EntityMap("eager", "getCurrentWasteBag");
        $entityMaps[] = new EntityMap("eager","getGetOtherWasteTypes");
        $entityMaps[] = new EntityMap("eager","getOtherWasteContainers");

        $authMaps = array();
        $authMaps[] = new EntityMap("eager", "getRooms");
        $authMaps[] = new EntityMap("eager", "getAuthorizations");
        $authMaps[] = new EntityMap("lazy", "getDepartments");

        $parcelMaps = array();
        $parcelMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
		$parcelMaps[] = new EntityMap("lazy", "getPurchase_order");
		$parcelMaps[] = new EntityMap("lazy", "getIsotope");
		$parcelMaps[] = new EntityMap("eager", "getParcelUses");
		$parcelMaps[] = new EntityMap("eager", "getRemainder");
		$parcelMaps[] = new EntityMap("eager", "getWipe_test");

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

        if($pi->getCurrentWasteBag() != null){
            $bagMaps = array();
            $bagMaps[] = new EntityMap("lazy", "getContainer");
            $bagMaps[] = new EntityMap("lazy", "getPickup");
            $bagMaps[] = new EntityMap("lazy", "getDrum");
            $bagMaps[] = new EntityMap("eager", "getParcelUseAmounts");
            $bag = $pi->getCurrentWasteBag();
            $bag->setEntityMaps($bagMaps);
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

    function getAuthorizationById($id = NULL) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__);

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $authorizationDao = $this->getDao(new Authorization());
            $selectedAuthorization = $authorizationDao->getById($id);
            return $selectedAuthorization;
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
            $selectedPi = $this->getRadPIById($id);
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
        $auths = $dao->getAll();
        foreach($auths as $a){
            //$a = new Authorization();
            $a->makeOriginal_pi_auth_id();
        }
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
        $LOG = Logger::getLogger('Action.' . __FUNCTION__);
        $LOG->debug("getAllParcels");

        $dao = $this->getDao(new Parcel());
        $parcels = $dao->getAll();

        $LOG->debug("Retrieved " . count($parcels) . " parcels");
        return $parcels;
    }

    public function getAllParcelUses(){
        $dao = $this->getDao(new ParcelUse());
        return $dao->getAll();
    }

    public function getAllParcelUseAmounts(){
        $dao = $this->getDao(new ParcelUseAmount());
        return $dao->getAll();
    }

    public function getAllPickups( $piId = NULL ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->info("Get all pickups" . ($piId ? " for PI $piId" : ''));

        $dao = $this->getDao(new Pickup());
        $entityMaps = array();
        $entityMaps[] = new EntityMap("eager", "getCarboy_use_cycles");
        $entityMaps[] = new EntityMap("eager", "getWaste_bags");
        $entityMaps[] = new EntityMap("eager", "getScint_vial_collections");
        $entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
        $entityMaps[] = new EntityMap("eager", "getPiName");

        if( $piId ){
            // Get all, limited by PI
            $whereGroup = new WhereClauseGroup(array(
                new WhereClause('principal_investigator_id', '=', $piId)
            ));

            $pickups = $dao->getAllWhere($clauses);
        }
        else{
            // Get all
            $pickups = $dao->getAll();
        }

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

    function saveCarboyDisposalDetails(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        // Read DTO from requets
        // We want this raw because we are not expecting a formal DTO
        $dto = $this->readRawInputJson();
        if( $dto === NULL ) {
            return new ActionError('Error converting input stream to Pickup', 202);
        }
        else if( $dto instanceof ActionError) {
            return $dto;
        }

        $LOG->debug("Saving Carboy Cycle Disposal...");
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($dto);
        }

        // Extract details from DTO
        $cycle_id = $dto['cycle']['id'];

        $cycleDao = new GenericDAO(new CarboyUseCycle());
        $LOG->debug("Read existing CarboyUseCycle $cycle_id");
        $cycle = $cycleDao->getById($cycle_id);

        DBConnection::get()->beginTransaction();
        $newStatus = $dto['cycle']['status'];

        if( $newStatus != null && $newStatus != $cycle->getStatus() ){
            $LOG->info('Transition cycle ' . $cycle . ' ' . $cycle->getStatus() . " => $newStatus");
            $cycle->setStatus($newStatus);
        }
        else{
            $LOG->info('Updating cycle details; no status transition');
        }

        if($dto['cycle']['hotDate'] && $cycle->getStatus() == 'In Hot Room'){
            $LOG->debug("set hot date");
            $cycle->setHotroom_date($dto['cycle']['hotDate']);
        }

        if($dto['cycle']['drumId'] && $cycle->getStatus() == 'In Drum'){
            $LOG->debug("set drum ID");
            $cycle->setDrum_id($dto['cycle']['drumId']);
        }

        // "Pour date" is technically a more general "disposal date"
        $is_disposed = $cycle->getStatus() == 'Poured' || $cycle->getStatus() == 'In Drum';
        if($dto['cycle']['pourDate'] && $is_disposed ){
            $LOG->debug("set pour/drum date");
            $cycle->setPour_date($dto['cycle']['pourDate']);
        }

        // Readings
        if( count($dto['cycle']['readings']) > 0 ){
            $LOG->debug('add/update Readings');

            // Add/Update reading(s)
            $readingDao = new GenericDAO( new CarboyReadingAmount() );
            foreach ( $dto['cycle']['readings'] as $readingDto ) {
                if( $readingDto['Key_id'] ){
                    $reading = $readingDao->getById($readingDto['Key_id']);
                    $LOG->debug("Update existing reading: $reading");
                }
                else{
                    $LOG->debug("Add new reading");
                    $reading = new CarboyReadingAmount();
                    $reading->setCarboy_use_cycle_id($cycle->getKey_id());
                }

                $reading->setIsotope_id($readingDto['Isotope_id']);
                $reading->setCurie_level($readingDto['Curie_level']);
                $reading->setDate_read($readingDto['Date_read']);

                $readingDao->save($reading);
            }
        }

        $cycle->setComments($dto['cycle']['comments']);
        $cycle->setVolume($dto['cycle']['volume']);

        // Save
        $cycle = $cycleDao->save($cycle);

        DBConnection::get()->commit();

        $LOG->info("Saved Carboy cycle details: " . $cycle);

        return $cycle;
    }

    function saveCarboy() {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to Carboy', 202);
        }
        else if ( $decodedObject instanceof ActionError ) {
            return $decodedObject;
        }
        else {
            $key_id = $decodedObject->getKey_id();
            $carboyDao = $this->getDao(new Carboy());

            // Create or update?
            if ( $decodedObject->getKey_id() == NULL ) {
                // New carboy
                $LOG->info("Create new Carboy: " . $decodedObject);

                // Set commission date
                $decodedObject->setCommission_date( date('Y-m-d H:i:s') );

                // Save the carboy so we can map to our first use-cycle
                $carboy = $carboyDao->save($decodedObject);

                $LOG->debug("Saved new carboy: " . $carboy);
            }
            else{
                // Update
                $LOG->info("Update Carboy: " . $decodedObject);
                $carboy = $carboyDao->save($decodedObject);
            }

            // Check that it has an active use-cycle
            $this->checkCycleCarboy($carboy->getKey_id());

            // Override entitymaps to eagerly load use-cycle(s)
            $entityMaps = array(new EntityMap(EntityMap::$TYPE_EAGER, "getCarboy_use_cycles"));
            $carboy->setEntityMaps($entityMaps);

            return $carboy;
        }
    }

    function saveCarboyUseCycle() {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to CarboyUseCycle', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            // Save the cycle
            $LOG->info("Save " . $decodedObject);
            $dao = $this->getDao(new CarboyUseCycle());
            $decodedObject = $this->onWasteContainerUpdated($decodedObject);
            $cycle = $dao->save($decodedObject);

            if( $LOG->isTraceEnabled()){
                $LOG->trace($cycle);
            }

            // Determine if carboy needs a new cycle
            $this->checkCycleCarboy($cycle->getCarboy_id());
            $cycle->setCarboy(null);

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

            $LOG->debug("SAVE COMPLETE");
            return $cycle;
        }
    }

    function checkCycleCarboy($carboyId){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        if( !$carboyId ){
            $LOG->error("Cannot check carboy cycle(s) without a key id");
            return;
        }

        $LOG->debug("Get carboy by ID: '$carboyId'");
        $carboyDao = new GenericDAO(new Carboy());
        $carboy = $carboyDao->getById($carboyId);

        // Ignore if this carboy is retired
        if( $carboy->getRetirement_date() != null ){
            $LOG->info("$carboy is retired");
            return;
        }

        $LOG->debug("Check current cycle for " . $carboy);
        $currentCycle = $carboy->getCurrent_carboy_use_cycle();

        if( $currentCycle == null ){
            $LOG->debug("Carboy requires new cycle");

            $cycle = new CarboyUseCycle();
            $cycle->setCarboy_id($carboy->getKey_id());
            $cycle->setIs_active(true);
            $cycle->setStatus("Available");

            // Save
            $cycleDao = new GenericDAO($cycle);
            $cycle = $cycleDao->save($cycle);

            $LOG->info("Created new use cycle $cycle");

            // Invalidate the carboy's cached cycles, forcing it to re-load them (thus including the newly-saved cycle)
            $carboy->setCarboy_use_cycles(null);
        }
        else{
            $LOG->debug("Carboy does not require new cycle");
        }
    }

    function retireCarboy( $carboyId = NULL, $retireDate = NULL ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $carboyId = $this->getValueFromRequest('id', $carboyId);
        $retireDate = $this->getValueFromRequest('date', $retireDate);

        if( !$carboyId ){
            return new ActionError('Carboy ID is required', 400);
        }

        if( !$retireDate ){
            $retireDate = date('Y-m-d H:i:s');
        }

        $LOG->info("Request to retire carboy $carboyId on $retireDate");

        $carboyDao = new GenericDAO(new Carboy());

        // Look up the carboy
        $carboy = $carboyDao->getById( $carboyId );

        if( !$carboy ){
            return new ActionError("No such carboy $carboyId", 404);
        }

        // Validate that this can be retired
        // 1. Cannot already be retired
        if( $carboy->getRetirement_date() != null ){
            // Nothing to do
            $LOG->info("Carboy is already retired: $carboy");
            return $carboy;
        }

        // 2. Current cycle must be disposed or not in-use
        $currentCycle = $carboy->getCurrent_carboy_use_cycle();
        $finishedCycle = $currentCycle == null || $currentCycle->isDisposed();
        $availableCycle = $currentCycle != null && $currentCycle->getStatus() == 'Available';

        if( $finishedCycle || $availableCycle ){
            // Set the date
            $carboy->setRetirement_date($retireDate);

            // Save
            $carboy = $carboyDao->save($carboy);
            $LOG->info("Retired carboy $carboy on " . $carboy->getRetirement_date());

            if( $availableCycle ){
                $LOG->info("Retired carboy has available cycle; deleting this cycle: $currentCycle");
                $cycleDao = new GenericDAO($currentCycle);
                $cycleDao->deleteById($currentCycle->getKey_id());
            }

            // Invalidate the carboy's cached cycles, forcing it to re-load them (thus including the newly-deleted cycle)
            $carboy->setCarboy_use_cycles(null);

            return $carboy;
        }
        else{
            return new ActionError('Cannot retire Carboy', 401);
        }
    }

    function recirculateCarboy( $carboyId = NULL ) {
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $carboyId = $this->getValueFromRequest('id', $carboyId);

        if( !$carboyId ){
            return new ActionError('Carboy ID is required', 400);
        }

        $LOG->debug("Recirculate carboy $carboyId");

        $carboyDao = new GenericDAO(new Carboy());

        // Look up the carboy
        $carboy = $carboyDao->getById( $carboyId );

        if( !$carboy ){
            return new ActionError("No such carboy $carboyId", 404);
        }

        // Validate that the carboy can be recycled
        // 1. Cannot recycle a retired carboy
        $isNotRetired = $carboy->getRetirement_date() == null;

        // 2. Current cycle must be disposed
        $currentCycle = $carboy->getCurrent_carboy_use_cycle();
        $finishedCycle = $currentCycle == null || $currentCycle->isDisposed();

        if( $isNotRetired && $finishedCycle ){
            // Create a new cycle
            $LOG->debug("Create new carboy cycle");

            $cycle = new CarboyUseCycle();
            $cycle->setCarboy_id($carboy->getKey_id());
            $cycle->setIs_active(true);
            $cycle->setStatus("Available");

            // Save
            $cycleDao = new GenericDAO($cycle);
            $cycle = $cycleDao->save($cycle);

            $LOG->info("Created new use cycle for carboy $carboyId: $cycle");

            // Invalidate the carboy's cached cycles, forcing it to re-load them (thus including the newly-saved cycle)
            $carboy->setCarboy_use_cycles(null);
        }
        else{
            // Can't do it
            return new ActionError('Cannot recirculate this Carboy', 401);
        }

        return $carboy;
    }

    function removeContainerFromDrum( $containerId, $containerType) {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        
        $id = $this->getValueFromRequest('id', $containerId);
        $type = $this->getValueFromRequest('type', $containerType);

        if( $id === NULL || $type === NULL) {
            return new ActionError('Insufficient data to remove from drum', 401);
        }

        $dao = $this->getDaoForWasteContainer($type);
        $container = $dao->getById($id);

        if( !$container ){
            return new ActionError('No such container', 404);
        }
        else if( $container->getDrum_id() == null ){
            // Container is not in a drum; do nothing else
            return $container;
        }

        $LOG->debug("Remove container from its drum: $container");

        // Unlink from drum
        $container->setDrum_id(null);

        if( $container instanceof CarboyUseCycle ){
            // Transition cycle back to Mixed Waste
            $container->setStatus('Mixed Waste');

            $LOG->debug("Transition carboy back to Mixed Waste");
        }

        return $dao->save($container);
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

    function updateParcelUse(){
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to ParcelUse', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        return $this->saveParcelUse($decodedObject);
    }


    function saveParcelUseAmount(ParcelUseAmount $decodedObject = null ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to ParcelUse', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }

        $LOG->debug("Save parcel use amount");

        $dao = new GenericDAO($decodedObject);
        $container = false;

        // Look up container into which this is being placed
        if($decodedObject->getWaste_bag_id() != null){
            $container = $this->getWasteBagById($decodedObject->getWaste_bag_id());
        }
        else if($decodedObject->getCarboy_id() != null){
            $container = $this->getCarboyUseCycleById($decodedObject->getCarboy_id());
        }
        else if($decodedObject->getOther_waste_container_id() != null){
            $container = $this->getOtherWasteContainerBiId($decodedObject->getOther_waste_container_id());
        }
        else if($decodedObject->getScint_vial_collection_id() != null){
            $container = $this->getScintVialCollectionById($decodedObject->getScint_vial_collection_id());
        }

        if( !$container ){
            // Couldn't find container!
            $msg = "Could not find container for parcel use amount";
            $LOG->warn($msg);

            if( $LOG->isTraceEnabled() ){
                $LOG->trace( $decodedObject );
            }

            return new ActionError($msg, 404);
        }

        $amount = $dao->save();
        $LOG->debug("Saved parcel use amount: $amount");

        return $container;
    }

    function getScintVialCollectionById($id = NULL) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        if($id == null)$id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new ScintVialCollection());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    function saveParcelUse($parcel = NULL) {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
            // If is_active is not passed, assume it is true
            $decodedObject->activateIfNotSet();

            $dao = $this->getDao(new ParcelUse());

            if($decodedObject->getDate_transferred() != null){
                $LOG->debug("Saving transfer");
                //this is a use for a transfer
                if($decodedObject->getDestinationParcel() != null){
                    $LOG->debug("Save transfer destination parcel");
                    $p =  $decodedObject->getDestinationParcel();
                    $p->activateIfNotSet();
                    $p->setArrival_date($p->getTransfer_in_date());
                    $authDao = $this->getDao(new Authorization());
                    $piAuthDao = $this->getDao(new PIAuthorization());
                    $auth = $authDao->getById($p->getAuthorization_id());
                    if($auth != null){
                        $piAuth = $piAuthDao->getById($auth->getPi_authorization_id());
                        $LOG->debug("Destination authorization: $piAuth");
                        if($piAuth != null){
                            $p->setPrincipal_investigator_id($piAuth->getPrincipal_investigator_id());
                        }
                    }

                    $parcelDao = new GenericDAO(new Parcel());
                    $p->setQuantity($decodedObject->getQuantity());

                    // Set status as Delivered
                    // TODO: Do we need to allow transitions?
                    $p->setStatus('Delivered');

                    $dParcel = $parcelDao->save($p);
                    $LOG->debug("Saved destination parcel for transfer: $dParcel");
                    $decodedObject->setDestination_parcel_id($dParcel->getKey_id());
                }

                $use = $dao->save($decodedObject);
                $LOG->debug("Saved parcel use as transfer: $use");

                if($dParcel != null){
                    $dParcel->setTransfer_amount_id($use->getKey_id());
                    $dParcel = $parcelDao->save($dParcel);
                    $LOG->debug("Applied transferred amount to destination parcel: $dParcel");
                }

                //do we already have a parcelUseAmount for this parcel?
                $amountDao = new GenericDAO(new ParcelUseAmount());
                if($decodedObject->getParcelUseAmounts() != null){
                    $LOG->debug("Read use-amount from incoming data");
                    $amts = $decodedObject->getParcelUseAmounts();
                    $amount = end($amts);
                    if(is_array($amount)) $amount = JsonManager::assembleObjectFromDecodedArray($amount);
                }else{
                    $LOG->debug("Add new use-amount to destination parcel");
                    $amount = new ParcelUseAmount();
                }

                $amount->activateIfNotSet();
                $LOG->debug("Use-amount: $amount");

                $amount->setParcel_use_id($use->getKey_id());
                $amount->setCurie_level($use->getQuantity());
                $amount = $amountDao->save($amount);

                $LOG->debug("Saved transfer use-amount; $amount");
            }else{
                $LOG->debug("Save parcel use");
                $use = $dao->save($decodedObject);

                $amounts = $decodedObject->getParcelUseAmounts();
                foreach($amounts as $amount){
                    $amountDao = $this->getDao(new ParcelUseAmount());
                    $newAmount = new ParcelUseAmount();
                    if($amount['Curie_level'] != NULL && $amount['Curie_level'] > 0){
                        $newAmount->setParcel_use_id($use->getKey_id());
                        $newAmount->setCurie_level($amount['Curie_level']);
                        $newAmount->setIs_active($amount['Is_active']);

                        if($amount['Key_id'] != NULL)$newAmount->setKey_id($amount['Key_id']);

                        if($amount['Waste_bag_id'] != NULL){
                            $newAmount->setWaste_bag_id($amount['Waste_bag_id']);
                            $entityMaps = array();
                            $entityMaps[] = new EntityMap("lazy", "getWaste_type");
                            $entityMaps[] = new EntityMap("lazy", "getContainer_name");
                            $newAmount->setEntityMaps($entityMaps);
                        }
                        if($amount['Carboy_id'] != NULL){
                            $newAmount->setCarboy_id($amount['Carboy_id']);
                            $entityMaps = array();
                            $entityMaps[] = new EntityMap("eager", "getCarboy");
                            $entityMaps[] = new EntityMap("lazy", "getWaste_type");
                            $entityMaps[] = new EntityMap("lazy", "getContainer_name");
                            $newAmount->setEntityMaps($entityMaps);
                        }
                        if($amount['Other_waste_container_id'] != NULL){
                            $newAmount->setOther_waste_container_id($amount['Other_waste_container_id']);
                            $newAmount->setOther_waste_type_id($amount['Other_waste_type_id']);
                            $entityMaps = array();
                            $entityMaps[] = new EntityMap("eager", "getWasteTypeName");
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
                                $svcs = $pi->getCurrentScintVialCollections();
                                $id = end($svcs)->getKey_id();
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

    function savePickupNotes(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        // Read DTO from requets
        // We want this raw because we are not expecting a formal DTO
        $dto = $this->readRawInputJson();
        if( $dto === NULL ) {
            return new ActionError('Error converting input stream to Pickup', 202);
        }
        else if( $dto instanceof ActionError) {
            return $dto;
        }

        $LOG->debug("Saving Pickup Notes...");
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($dto);
        }

        // Extract details from DTO
        $pickup_id = $dto['pickup_id'];
        $pickup_notes = $dto['notes'];

        $pickupDao = new GenericDAO(new Pickup());
        $LOG->debug("Read existing Pickup $pickup_id");
        $pickup = $pickupDao->getById($pickup_id);

        $LOG->debug("Begin DB transaction...");
        DBConnection::get()->beginTransaction();
        $pickup->setNotes( $pickup_notes );

        $pickup = $pickupDao->save($pickup);

        DBConnection::get()->commit();
        $LOG->debug("...Committed transaction");

        // Saved
        return true;
    }

    function savePickup(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        // Read DTO from requets
        // We want this raw because we are not expecting a formal DTO
        $dto = $this->readRawInputJson();
        if( $dto === NULL ) {
            return new ActionError('Error converting input stream to Pickup', 202);
        }
        else if( $dto instanceof ActionError) {
            return $dto;
        }

        $LOG->debug("Saving Pickup...");
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($dto);
        }

        // Extract details from DTO
        $pickup_id = $dto['pickup']['id'];

        $pickupDao = new GenericDAO(new Pickup());
        $LOG->debug("Read existing Pickup $pickup_id");
        $pickup = $pickupDao->getById($dto['pickup']['id']);

        $LOG->debug("Begin DB transaction...");
        DBConnection::get()->beginTransaction();

        // Update Pickup details
        // TODO: VALIDATE
        $newStatus = $dto['pickup']['status'];

        if( $newStatus != $pickup->getStatus() ){
            $LOG->info('Transistion pickup ' . $pickup . ' ' . $pickup->getStatus() . " => $newStatus");
        }

        $pickup->setStatus( $newStatus );
        $pickup->setPickup_date( $dto['pickup']['date']);

        $LOG->debug("Update Containers");

        $savedContainers = array();
        foreach($dto['containers'] as $containerDto){
            $savedContainers[] = $this->savePickup_container($containerDto, $newStatus);
        }

        $pickup = $pickupDao->save($pickup);

        // Handle partial Pickups - ensure no new pickup is required
        $this->handlePickup($pickup->getPrincipal_investigator_id());

        // Override pickup entitymaps to eagerly retrieve containers
        $pickup->setEntityMaps( array(
            new EntityMap(EntityMap::$TYPE_EAGER, "getCarboy_use_cycles"),
            new EntityMap(EntityMap::$TYPE_EAGER, "getWaste_bags"),
            new EntityMap(EntityMap::$TYPE_EAGER, "getScint_vial_collections"),
            new EntityMap(EntityMap::$TYPE_EAGER, "getPrincipalInvestigator"),
        ));

        DBConnection::get()->commit();
        $LOG->debug("...Committed transaction");

        // Composite DTO
        $savedDto = new SavedPickupDetailsDto($pickup, $savedContainers);

        if($LOG->isTraceEnabled()){
            $LOG->trace($savedDto);
        }

        return $savedDto;
    }

    function savePickup_container($dto, $pickupStatus){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->debug("Update (" . $dto['type'] . ') #' . $dto['id']);

        // TODO: validate DTO

        $dao = $this->getDaoForWasteContainer($dto['type']);

        // Look up the container by ID
        $container = $dao->getById($dto['id']);

        if( $container == null ){
            // Container must exist...
            throw new Exception("Unknown container of type " . $dto['type'] . ' with key_id ' . $dto['id'] );
        }

        // Add To or Remove From Pickup
        $includeInPickup = $dto['pickup_id'] != null;

        if( $includeInPickup ){
            $LOG->debug('Add container to Pickup ' . $dto['pickup_id']);
            $container->setPickup_id( $dto['pickup_id']);
        }
        else{
            $LOG->debug('Remove container from Pickup ' . $container->getPickup_id());
            $container->setPickup_id( null );
        }

        // Special cases...
        if( $container instanceof CarboyUseCycle ){
            // Set status
            if( $includeInPickup ){
                // Included in Pickup; either Picked Up or At RSO, based on Pickup status
                switch( $pickupStatus ){
                    case 'PICKED UP':
                        $container->setStatus('Picked Up');
                        break;
                    case 'AT RSO':
                        $container->setStatus('AT RSO');

                        // Carboy is At RSO; set special timestamp
                        $container->setRso_date( date('Y-m-d H:i:s') );
                        $LOG->info("Set CarboyUseCycle RSO date: " . $container->getRso_date());
                        break;

                    default: $LOG->error("Unabled to identify CarboyUseCycle status; Pickup status: $pickupStatus");
                }
            }
            else{
                // Removed from pickup; put back In Use
                $container->setStatus("In Use");
            }

            $LOG->info("Transition " . $container . " status to " . $container->getStatus());
        }

        // Add/Update Comments
        $container->setComments( $dto['comments'] );

        // Save
        $container = $dao->save($container);

        return $container;
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
            $decodedObject = $this->onWasteContainerUpdated($decodedObject);
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
        $decodedObject =  $this->convertInputJson();
        $LOG->debug($decodedObject);
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to WasteBag', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }
        else {
            $dao = $this->getDao(new WasteBag());
            $lots = $decodedObject->getPickupLots();
            $decodedObject = $this->onWasteContainerUpdated($decodedObject);
            $decodedObject = $dao->save($decodedObject);

            $lotDao = new GenericDAO(new PickupLot());
            foreach($lots as $lot){
                if(is_array($lot))$lot = JsonManager::assembleObjectFromDecodedArray($lot);
                $lot = $lotDao->save($lot);
            }
            return $this->getWasteBagById($decodedObject->getKey_id());
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

    function getAllPIWipes(){
        $dao = $this->getDao(new PIWipe());
        return $dao->getAll();
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
        $cycles = $decodedObject->getCarboy_use_cycles();

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

        foreach($cycles as $c){
            $c->setPickup_id($pickupKeyId);
            $this->saveCarboyUseCycle($c);
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
            return new ActionError("Could not delete Pickup #$id");
        }

        return true;

    }

    function getInventoriesByDateRanges(){

    }

    /**
     * Creates, saves, and returns a collection of QuarterlyInventories for all PIs who have Rad Auths
     *
     */

    function createQuarterlyInventories( $startDate = NULL, $endDate = null ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $startDate = $this->getValueFromRequest('startDate', $startDate);
        $endDate = $this->getValueFromRequest('endDate', $endDate);



        if( $startDate == NULL && $endDate == NULL ) {
            return new ActionError("Request parameters 'dueDate' and 'endDate' were not provided");
        }
        if( $endDate == NULL ) {
            return new ActionError("No request parameter 'endDate' was provided");
        }

        $LOG->info("Create/update quarterly inventory $startDate - $endDate");

        $LOG->debug("Retrieve all RAD PIs...");
        $pis = $this->getAllRadPis();
        $LOG->debug("...Retrieved " . count($pis) . " PIs");

        //create a master inventory, since all pis will have one with the same dates
        $inventoryDao = $this->getDao(new QuarterlyInventory());

        //make sure we only have one inventory for the given due and end dates
        $whereClauseGroup = new WhereClauseGroup();
        $clauses = array(
                new WhereClause('start_date','=', $startDate ),
                new WhereClause('end_date', '=', $endDate)
        );
        $whereClauseGroup->setClauses($clauses);
        $inventories = $inventoryDao->getAllWhere($whereClauseGroup);

        //do we already have a master inventory for this period?
        if($inventories != NULL){
            $LOG->info("Updating existing quarterly inventory");
            $inventory = $inventories[0];
        }
        //we don't have one, so make one
        else{
            $LOG->info("Create new quarterly inventory");
            $inventory = new QuarterlyInventory();
            //get the last inventory so we can set the start date, if there is one
            $inventoryDao = $this->getDao(new QuarterlyInventory());

            $inventory->setStart_date($startDate);
            $inventory->setEnd_date($endDate);
            $inventory->setIs_active(true);
            $inventory = $inventoryDao->save($inventory);
        }

        $piInventories = array();

        foreach($pis as $pi){
            $auth = $pi->getCurrentPi_authorization();
            // Omit PIs with no Authorization, or Authorizations which are terminated
            if($auth == null){
                $LOG->debug("Omit null auth");
                continue;
            }
            else if($auth->getTermination_date() != null){
                $LOG->debug("Omit terminated auth: $auth");
                continue;
            }

            $piInventory = $this->getPiInventory( $pi->getKey_id(), $inventory->getKey_id() );

            if($piInventory != NULL){
                $piInventories[] = $piInventory;
            }
            else{
                $LOG->debug("Omit null inventory for PI $pi");
            }
        }

        $LOG->debug("Added " . count($piInventories) . " of " . count($pis) . " PI inventories");

        $inventory->setPi_quarterly_inventories($piInventories);
        $entityMaps = array();
    	$entityMaps[] = new EntityMap("eager", "getQuarterly_isotope_amounts");
    	$entityMaps[] = new EntityMap("eager", "getPi_quarterly_inventories");
        $inventory->setEntityMaps($entityMaps);

        if( $LOG->isTraceEnabled()) {
            $LOG->trace($inventory);
        }

        return $inventory;
    }

    public function getPiInventory( $piId = NULL, $inventoryId = NULL ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        if($piId == null)$inventoryId = $this->getValueFromRequest('inventoryId', $inventoryId);
        if($inventoryId == null)$piId = $this->getValueFromRequest('piId', $piId);

        if( $inventoryId == NULL && $piId == NULL ) {
            return new ActionError("Request parameters 'piId' and 'inventoryId' were not provided");
        }
        elseif( $inventoryId == NULL ) {
            return new ActionError("No request parameter 'inventoryId' was provided");
        }
        elseif( $piId == NULL ) {
            return new ActionError("No request parameter 'piId' was provided");
        }

        $LOG->info("Get quarterly inventory #$inventoryId for PI #$piId" );

        $inventoryDao = $this->getDao(new QuarterlyInventory());
        $inventory = $inventoryDao->getById($inventoryId);
        $startDate = $inventory->getStart_date();
        $endDate = $inventory->getEnd_date();
        $pi = $this->getPIById($piId, false);

        if($pi->getPi_authorization() == NULL) {
            $LOG->info("No authorization exists for $pi");
            return NULL;
        }

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
        $pi_qinvs = $pi->getQuarterly_inventories();
        $mostRecentIntentory = end($pi_qinvs);



        //build the QuarterlyIsotopeAmounts for each isotope the PI could have
        $amounts = array();
        //foreach($pi->getPi_authorization() as $piAuth){
        $piAuth = $pi->getCurrentPi_authorization();
        //$LOG->fatal($piAuth);
        if($piAuth == null) {
            $LOG->info("No Current authorization exists for $pi");
            return null;
        }

        $LOG->debug("Buildling inventories for $pi");
        foreach($piAuth->getAuthorizations() as $authorization){
            $LOG->debug("Build inventory for $authorization");

            $quarterlyAmountDao = $this->getDao(new QuarterlyIsotopeAmount());

            //do we already have a QuarterlyIsotopeAmount?
            $whereClauseGroup = new WhereClauseGroup();
            $clauses = array(
                    new WhereClause('authorization_id','=', $authorization->getKey_id() ),
                    new WhereClause('quarterly_inventory_id','=', $piInventory->getKey_id() ),
            );

            $whereClauseGroup->setClauses($clauses);
            $oldAmounts = $quarterlyAmountDao->getAllWhere($whereClauseGroup);

            if($oldAmounts != NULL){
                $newAmount = $oldAmounts[0];
                $LOG->debug("Updating inventory amount entity $newAmount");
            }else{
                $newAmount = new QuarterlyIsotopeAmount();
                $newAmount->setIs_active(true);
                $newAmount->setAuthorization_id($authorization->getKey_id());
                $newAmount->setAuthorization($authorization);
                $newAmount->setQuarterly_inventory_id($piInventory->getKey_id());

                $LOG->debug("Creating new amount entity: $newAmount");
            }

            //if we have a previous inventory, find the matching isotope in the previous inventory, so we can get its amount at that time
            $oldAmount = null;
            if($mostRecentIntentory != null){
                foreach($mostRecentIntentory->getQuarterly_isotope_amounts() as $amount){
                    if($amount->getAuthorization_id() == $authorization->getIsotope_id()){
                        $LOG->debug("Found previous quarter amount $amount");
                        $oldAmount = $amount;
                        break;
                    }
                }
            }

            //calculate the decorator properties (use amounts, amounts received by PI as parcels and transfers, amount left on hand)
            $this->calculateQuarterlyAmount($newAmount, $oldAmount, $piId, $authorization->getIsotope_id(), $startDate, $endDate );
            $newAmount = $quarterlyAmountDao->save($newAmount);

            $amounts[] = $newAmount;

        }
        //}
        $piInventory->setQuarterly_isotope_amounts($amounts);
        return $piInventory;
    }

    /**
     * * calculate the values for the decorator properties of a QuarterlyInventoryAmount
     * @param QuarterlyIsotopeAmount $amount
     * @param string $startDate
     * @param string $endDate
     */
    private function calculateQuarterlyAmount(&$amount, $oldAmount, $piId, $isotopeId, $startDate, $endDate){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $qDao = new QuarterlyIsotopeAmountDAO();

        $LOG->debug("Calculate PI #$piId inventory of isotope #$isotopeId between [$startDate and $endDate]");
        /**
            starting_amount     -> 'Last Quarter Amount'        -> previous quarter's amount on_hand
            total_ordered       -> 'Total Ordered'              -> total amount which was received during this quarter
            transfer_in         -> 'Transfer In'                -> total amount transfered [to me?]
            transfer_out        -> 'Transfer out'               -> total amount transfered [from me?]
            solid_waste         -> 'Solid Waste'                -> total amount disposed of via solids
            liquid_waste        -> 'Liquid Waste'               -> total amount disposed of via Carboys
            scint_vial_waste    -> 'Scintillation vial wasste   -> total amount disposed of via Scint vials
            other_waste         -> 'Other Waste'                -> total amount disposed of via 'other' containers
            on_hand             -> 'Actual amount on hand'      -> total amount which has not been disposed (previous + current - disposed)
        */

        // Note: starting_amount is not calculated here; it's obtained from previous inventory
        if( $oldAmount != null ){
            $LOG->debug("Applying previous quarter amount of " . $oldAmount->getEnding_amount());
            $amount->setStarting_amount($oldAmount->getEnding_amount());
        }

        //get the total ordered since the previous inventory

        //get parcels for this QuarterlyIsotopsAmount's authorization that have an RS ID for the given dates
        $amount->setTotal_ordered($qDao->getTransferAmounts($piId, $isotopeId, $startDate, $endDate, false));

        //get the total transfered in since the previous inventory

        //get parcels for this QuarterlyIsotopsAmount's authorization that DON'T have an RSID for the given dates
        $amount->setTransfer_in($qDao->getTransferAmounts($piId, $isotopeId, $startDate, $endDate, true));


        //get the total transfered out since the previous inventory
        //??what is a tranfer out?
        $amount->setTransfer_out($qDao->getTransferOutAmounts($piId, $isotopeId, $startDate, $endDate));

        //subtract this quarters parcel uses, going by parcel use amount, maintaining a count of each kind of disposal (liquid, solid or scintvial)

        // FIXME: Replace magic numbers with entity references

        //get liquid amounts
        $amount->setLiquid_waste($qDao->getUsageAmounts( $piId, $isotopeId, $startDate, $endDate, 1));

        //get scint vial amounts
        $amount->setScint_vial_waste($qDao->getUsageAmounts( $piId, $isotopeId, $startDate, $endDate, 3));

        //get solid amounts
        $amount->setSolid_waste($qDao->getUsageAmounts( $piId, $isotopeId, $startDate, $endDate, 5));

        //get other amounts
        $amount->setOther_waste($qDao->getUsageAmounts( $piId, $isotopeId, $startDate, $endDate, 4));

        //calculate the amount currently on hand
        $totalIn = $amount->getStarting_amount() + $amount->getTransfer_in() + $amount->getTotal_ordered();
        $totalOut = $amount->getTransfer_out() + $amount->getSolid_waste() + $amount->getLiquid_waste() + $amount->getOther_waste() + $amount->getScint_vial_waste();

        $amount->setOn_hand($totalIn - $totalOut);
        $LOG->trace($amount);
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

        $LOG->info("Get current inventory of $pi");
        //get the most recent inventory for this PI so we can use the quantities of its QuarterlyIsotopeAmounts to set new ones
        //$pi->getQuarterly_inventories()'s query is ordered by date_modified column, so the last in the array will be the most recent
        $quarterlyInventories = $pi->getQuarterly_inventories();
        $qisize = count($quarterlyInventories);

        if( empty($quarterlyInventories) ){
            $LOG->info("$pi has no quarterly inventory");
            return array();
        }

        $mostRecentIntentory = end($quarterlyInventories);
        $LOG->debug("PI $piId most recent inventory (of $qisize): $mostRecentIntentory");

        $pi_inventory = $this->getPiInventory($piId,$mostRecentIntentory->getQuarterly_inventory_id());
        $this->eagerLoadInventoryAuthorization($pi_inventory);
        return $pi_inventory;
    }

    public function getMostRecentInventory(){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

        $inventoryDao = $this->getDao(new QuarterlyInventory());
        $invs = $inventoryDao->getAll("end_date");
        $mostRecentInv = end($invs);
        $LOG->debug($mostRecentInv);

        if( $mostRecentInv )
            return $mostRecentInv;

        return new ActionError('No inventory exists', 404);
    }


    public function getInventoriesByPiId( $piId = NULL ){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
        $piId = $this->getValueFromRequest("piId", $piId);
        $LOG->debug("Get inventories for PI #$piId");

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
        $invcount = count($inventories);
        $LOG->debug("Inventories for PI #$piId: $invcount");
        return $inventories;
    }

    public function getPIInventoryById( $piId = NULL ){
        $LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
        $piId = $this->getValueFromRequest("piId", $piId);

        $inventoriesDao = $this->getDao(new PIQuarterlyInventory());
        $inv = $inventoriesDao->getById($piId);

        // force the Authorizations to load...
        $this->eagerLoadInventoryAuthorization($inv);

        return $inv;
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
        $invs = $inventoriesDao->getAllWhere($whereClauseGroup);
        $auth =  reset($invs);
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
            $rooms = $decodedObject->getRooms();
            $users = $decodedObject->getUsers();
            $conditions = $decodedObject->getConditions();
            $departments = $decodedObject->getDepartments();

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

                foreach($origAuth->getUsers() as $user){
    				$origDao->removeRelatedItems($user->getKey_id(),$origAuth->getKey_id(),DataRelationship::fromArray(PIAuthorization::$USERS_RELATIONSHIP));
    			}

                foreach($origAuth->getConditions() as $condition){
    				$origDao->removeRelatedItems($condition->getKey_id(),$origAuth->getKey_id(),DataRelationship::fromArray(PIAuthorization::$CONDITIONS_RELATIONSHIP));
    			}
    		}


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


    		foreach($users as $user){
    			$dao->addRelatedItems($user["Key_id"],$decodedObject->getKey_id(),DataRelationship::fromArray(PIAuthorization::$USERS_RELATIONSHIP));
    		}
            $l= Logger::getLogger(__FUNCTION__);

    		foreach($conditions as $condition){
                $l->fatal($condition);
    			$dao->addRelatedItems($condition["Key_id"],$decodedObject->getKey_id(),DataRelationship::fromArray(PIAuthorization::$CONDITIONS_RELATIONSHIP), $condition["Order_index"]);
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
					$newAuth->setIs_active($decodedObject->getTermination_date() == null);
                    //if the PiAuthorization has a key_id, we know we are editing one that already exists.
                    //In that case, we should save it's old authorizations, rather than creating new ones, so we set the key_id for each of them
                    if($id != null){
                        $newAuth->setKey_id($auth["Key_id"]);
                        $newAuth->setDate_created($auth["Date_created"]);
                    }
                    $newAuth->makeOriginal_pi_auth_id();
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

    function eagerLoadInventoryAuthorization($inventory){
        foreach($inventory->getQuarterly_isotope_amounts() as $amt){
            $entityMaps = array();
            $entityMaps[] = new EntityMap("eager", "getAuthorization");
            $amt->setEntityMaps($entityMaps);
        }
    }

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
    	//$dto->setUser($this->getAllRadUsers());
    	//$dto->setAuthorization($this->getAllAuthorizations());
    	//$dto->setPIAuthorization($this->getAllPIAuthorizations());
    	//$dto->setCarboy($this->getAllCarboys());
    	//$dto->setCarboyUseCycle($this->getAllCarboyUseCycles());
        //$dto->setCarboyReadingAmount($this->getAllCarboyReadingAmounts());
    	//$dto->setDrum($this->getAllDrums());
    	//$dto->setDepartment($this->getAllDepartments());
    	//$dto->setInspectionWipe($this->getAllInspectionWipes());
    	//$dto->setInspectionWipeTest($this->getAllInspectionWipeTests());
    	$dto->setIsotope($this->getAllIsotopes());
    	//$dto->setParcelUseAmount($this->getAllParcelUseAmounts());
    	//$dto->setParcelUse($this->getAllParcelUses());
    	//$dto->setParcelWipe($this->getAllParcelWipes());
    	//$dto->setParcelWipeTest($this->getAllParcelWipeTests());
    	//$dto->setParcel($this->getAllParcels());
    	//$dto->setPickup($this->getAllPickups());
        $dto->setPurchaseOrder($this->getAllPurchaseOrders());
    	//$dto->getQuarterlyIsotopeAmount($this->getAllQuarterlyInventories());
    	//$dto->setQuarterlInventory($this->getMostRecentInventory());
    	//$dto->setPIQuarterlyInventory($this->getAllPIQuarterlyInventories());
    //	$dto->setScintVialCollection($this->getAllScintVialCollections());
      //  $dto->setMiscellaneousWaste($this->getAllMiscellaneousWaste());
    	//$dto->setWasteBag($this->getAllWasteBags());
    	//$dto->setSolidsContainer($this->getAllSolidsContainers());
    	$dto->setWasteType($this->getAllWasteTypes());
    	$dto->setRoom($this->getAllRooms(true));
    	$dto->setPrincipalInvestigator($this->getAllRadPis());
        //$dto->setDrumWipe($this->getAllDrumWipes());
       // $dto->setDrumWipeTest($this->getAllDrumWipeTests());
        $dto->setOtherWasteType($this->getAllOtherWasteTypes());
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

    public function removeParcelUseAmountFromPickup(ParcelUseAmount $decodedObject){
        if($decodedObject == null)$decodedObject = $this->convertInputJson();
        if($decodedObject == null)return new ActionError("No data read from input stream");
        //find whatever pickup-able container this ParcelUseAmount is currently in
        $l = Logger::getLogger(_FUNCTION__);
        if($decodedObject->getWaste_bag_id() != null){
            //persevere current bag's id
            $bag = $this->getWasteBagById($decodedObject->getWaste_bag_id());
            $l->fatal($bag);
            $pickup = $this->getPickupById($bag->getPickup_id());
            $pickupAndBag = array($pickup);
            $l->fatal($pickup);

            //$currentBag = new WasteBag();
            $piId = $bag->getPrincipal_investigator_id();
            $pi = $this->getPIById($piId);
            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy", "getContainer");
            $entityMaps[] = new EntityMap("lazy", "getPickup");
            $entityMaps[] = new EntityMap("lazy", "getDrum");
            $entityMaps[] = new EntityMap("eager", "getParcelUseAmounts");
            if($pi->getCurrentWasteBag() != null){
                $decodedObject->setWaste_bag_id($pi->getCurrentWasteBag()->getKey_id());
                $bag = $pi->getCurrentWasteBag();
                $bag->setEntityMaps($entityMaps);
                array_push($pickupAndBag, $bag);
            }
            //pi doesn't have a wastebag not already selected for pickup, so make one
            else{
                $newBag = new WasteBag();
                $newBag->setPrincipal_investigator_id($piId);
                $newBag = $this->saveWasteBag($newBag);
                $newBag->setEntityMaps($entityMaps);

                $decodedObject->setWaste_bag_id($newBag->getKey_id());
                array_push($pickupAndBag, $newBag);
            }
            $amountDao = new GenericDAO($decodedObject);
            $decodedObject = $amountDao->save();

            return $pickupAndBag;
        }
        return new ActionError("The provided bag was not scheduled for pickup");
    }

    public function getAllOtherWasteTypes(){
        $d = new GenericDAO(new OtherWasteType());
        $owts = $d->getAll();
        return $owts;
    }

    function getOtherWasteTypeById($id = NULL) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        if($id == null)$id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new OtherWasteType());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    /**
     * Summary of saveOtherWasteType
     * @param OtherWasteType $decodedObject
     * @return ActionError | OtherWasteType
     */
    public function saveOtherWasteType($decodedObject = null){
        if($decodedObject == null) $decodedObject = $this->convertInputJson();
        if($decodedObject == null)return new ActionError("No data read from input stream");

        // Force Other Waste Types to always require clearing by RSO
        $decodedObject->setClearable(true);

        $d = new GenericDAO($decodedObject);
        return $d->save();

    }

    public function assignOtherWasteType( $owtId = null, $piId = null, $remove = null ){
        if($owtId == null)$owtId = $this->getValueFromRequest('owtId', $owtId);
        if($piId == null)$piId = $this->getValueFromRequest('piId', $piId);
        if($remove == null)$remove = $this->getValueFromRequest('remove', $remove);
        if($owtId == null || $piId == null)return new ActionError("Missing params");

        if($remove == null)$remove = false;
        $dao = new GenericDAO(new PrincipalInvestigator());
        if(!$remove){
            if($dao->addRelatedItems($owtId,$piId,DataRelationship::fromArray(PrincipalInvestigator::$OTHER_WASTE_TYPES_ALLOWED))){
                return array($this->getOtherWasteTypeById($owtId));
            }
        }else{
            if($dao->removeRelatedItems($owtId,$piId,DataRelationship::fromArray(PrincipalInvestigator::$OTHER_WASTE_TYPES_ALLOWED))){
                return array($this->getOtherWasteTypeById($owtId));
            }
        }
        return false;
    }

    /**
     * @param $decodedObject ParcelUseAmount
     */
    public function clearOtherWaste($decodedObject){
        //to do add clearanceDate to parcel_use_amount and class
    }
    /*
     * "getAllOtherWasteContainers"	=> new ActionMapping("getAllOtherWasteContainers","","", $this::$ROLE_GROUPS["RSO"]),
                "getOtherWasteContainerBiId"	=> new ActionMapping("getOtherWasteContainerBiId","","", $this::$ROLE_GROUPS["RSO"]),
                "saveOtherWasteContainer"	    => new ActionMapping("saveOtherWasteContainer","","", $this::$ROLE_GROUPS["RSO"]),
     *
     * */
    public function getAllOtherWasteContainers(){
        $d = new GenericDAO(new OtherWasteContainer());
        $owts = $d->getAll();
        return $owts;
    }

    public function saveOtherWasteContainer($decodedObject = null){
        if($decodedObject == null) $decodedObject = $this->convertInputJson();
        if($decodedObject == null)return new ActionError("No data read from input stream");
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $LOG->fatal($decodedObject);
        $d = new GenericDAO($decodedObject);
        $decodedObject = $this->onWasteContainerUpdated($decodedObject);
        return $d->save($decodedObject);
    }

    public function getOtherWasteContainerBiId($id = NULL) {
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );

        if($id == null)$id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ) {
            $dao = $this->getDao(new OtherWasteContainer());
            return $dao->getById($id);
        }
        else {
            return new ActionError("No request parameter 'id' was provided", 201);
        }
    }

    public function getTotalInventories(){
        $dao = new GenericDAO(new Isotope());
        return $dao->getIsotopeTotalsReport();
    }

    public function getRadInventoryReport(){
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $LOG->info("Get total inventory report");
        $reports = $this->getTotalInventories();
        return $reports;
    }

    public function resetRadData(){
        $LOG = Logger::getLogger( 'Action' . __FUNCTION__ );
        $LOG->warn("User requested reset of Rad data");
        $dao = new GenericDAO(new User());
        if($dao->deleteRadData()){
            return true;
        }
        return new ActionError('Failed to delete data');
    }

    public function getAllRadConditions() {
        $dao = $this->getDao(new RadCondition());
        $conditions = $dao->getAll();
        if(is_array($conditions))return $conditions;
        return new ActionError("No request parameter 'id' was provided", 201);
    }

    /**
     * Retrieves all Waste Containers (of any type) which have been Closed
     * and not yet picked up
     */
    public function getAllWasteContainersReadyForPickup( $piId ) {
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Get PI filter from request, if any
        $piId = $this->getValueFromRequest('piId', $piId);

        $LOG->info('Get all pickup-ready waste containers' . ($piId ? " for PI $piId" : ''));

        $container_types = array(
            "CarboyUseCycle",
            "ScintVialCollection",
            "WasteBag"
        );

        // Get containers which are closed and are not assigned to a pickup
        $clauses = array(
            new WhereClause("pickup_id", "IS", "NULL"),
            new WhereClause("close_date", "IS NOT", "NULL")
        );

        if( $piId ){
            $LOG->debug("Add clause to limit to PI $piId");
            $clauses[] = new WhereClause('principal_investigator_id', '=', $piId);
        }

        $whereContainerIsPickupReady = new WhereClauseGroup($clauses);

        $pickupReady = array();
        foreach($container_types as $type){
            $LOG->debug("Get all pickup-ready containers of type $type");

            $dao = new GenericDAO( new $type());
            $pickupReadyOfType = $dao->getAllWhere($whereContainerIsPickupReady);

            $LOG->debug('Found ' . count($pickupReadyOfType) . " $type containers ready for pickup");

            $pickupReady = array_merge($pickupReady, $pickupReadyOfType);
        }

        $LOG->info('Found ' . count($pickupReady) . " containers ready for pickup");

        // TODO: eager/lazy overrides?
        return $pickupReady;
    }

    /**
     * @param RadCondition $condition
     * */
    public function saveRadCondition(RadCondition $decodedObject = null){
        if($decodedObject == null)$decodedObject = $this->convertInputJson();
        if($decodedObject == null)return new ActionError("No data read from input stream");
        $dao = $this->getDao($decodedObject);
        return $dao->save();
    }

    public function closeWasteContainer($containerId = null, $containerType = null){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $closeDate = new DateTime();

        if( !$containerId && !$containerType ){
            $dto = $this->readRawInputJson();
            if( $dto === NULL ) {
                return new ActionError('Error converting input stream to Pickup', 202);
            }
            else if( $dto instanceof ActionError) {
                return $dto;
            }

            $containerId = $dto['id'];
            $containerType = $dto['type'];
            $closeDate = $dto['date'];
        }

        // We need both params
        if( !$containerId || !$containerType ){
            return new ActionError("Invalid request");
        }

        $LOG->debug("Close $containerType $containerId...");

        // Get appropriate DAO
        $dao = $this->getDaoForWasteContainer( $containerType );

        // Lookup container
        $container = $dao->getById($containerId);

        if( ! $container ){
            return new ActionError("No such $containerType $containerId");
        }

        if( $container->getClose_date() == NULL ){
            $LOG->info("Closing container " . $container . ' at ' . $closeDate);

            // Close the container
            $container->setClose_date($closeDate);

            // TODO: type-specific special cases?

            // Save changes
            $container = $dao->save($container);

            $this->onWasteContainerUpdated($container);
        }
        else {
            // else nothing to do
            $LOG->info($container . ' is already closed');
        }

        return $container;
    }

    /**
     * Actions performed when a Waste container is updated
     */
    private function onWasteContainerUpdated($container){
        $l = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Handle requesting Pickup
        if($container->getClose_date() == null){
            $l->debug("Container is not closed; no pickup necessary");
        }
        else{
            $l->info("Handle pickup for container " . $container);
            if($l->isTraceEnabled()){
                $l->trace($container);
            }

            // Handle pickup
            $this->handlePickup($container->getPrincipal_investigator_id());
        }

        return $container;
    }

    private function handlePickup($piId){
        $l = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        if( $piId == NULL ){
            $l->warn('No PI ID provided to ' . __FUNCTION__);
            return;
        }

        // Are there any containers ready for pickup?
        $readyContainers = $this->getAllWasteContainersReadyForPickup($piId);
        $requirePickup = count($readyContainers) > 0;

        // Find existing REQUESTED pickups
        $dao = new GenericDAO(new Pickup());
        $group = new WhereClauseGroup(
            array(
                new WhereClause("principal_investigator_id", "=", $piId),
                new WhereClause("status", "=", "REQUESTED")
            )
        );

        $existingPickups = $dao->getAllWhere($group);
        $pickupExists = count($existingPickups) > 0;

        $l->debug('Found ' . count($existingPickups) . ' existing pickup(s) for PI ' . $piId);

        if( $requirePickup && !$pickupExists ){
            // A new pickup is required
            $l->info("Requesting new pickup for PI " . $piId);
            $pickup = new Pickup();
            $pickup->setPrincipal_investigator_id($piId);
            $pickup->setStatus("REQUESTED");
            $pickup->setRequested_date(date("Y-m-d H:i:s"));
            $pickup = $dao->save($pickup);

            $l->info("Saved pickup " . $pickup);
        }
        else if ( !$requirePickup && $pickupExists){
            // No pickups are required; trim the orphans
            $l->info("PI $piId has requested Pickups but no ready containers; trimming orphan Pickups...");
            foreach( $existingPickups as $orphan ){
                $this->deletePickupById($orphan->getKey_id());
            }
        }
        else{
            // else nothing required
            $l->warn("PI $piId requires no new Pickup. " . count($existingPickups) . ' Existing Pickup(s), ' . count($readyContainers) . ' ready Waste Containers');
        }
    }

    public function removeFromPickup(){

        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ) {
            return new ActionError('Error converting input stream to Authorization', 202);
        }
        else if( $decodedObject instanceof ActionError) {
            return $decodedObject;
        }

        $l = Logger::getLogger(__FUNCTION__);
        $l->info("Removig $decodedObject from Pickup " . $decodedObject->getPickup_id());

        if( $l->isTraceEnabled() ){
            $l->trace('Before Pickup unlink:');
            $l->trace($decodedObject);
        }

        $decodedObject->setPickup_id(null);
        $decodedObject->setPickup_date(null);

        if( $l->isTraceEnabled() ){
            $l->trace('After Pickup unlink:');
            $l->trace($decodedObject);
        }

        $dao = new GenericDAO($decodedObject);
        $decodedObject = $dao->save($decodedObject);
        return $decodedObject;
    }

    private function getDaoForWasteContainer($type){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        switch( $type ){
            case 'WasteBag':            return new GenericDAO(new WasteBag());
            case 'ScintVialCollection': return new GenericDAO(new ScintVialCollection());
            case 'CarboyUseCycle':      return new GenericDAO(new CarboyUseCycle());
            case 'OtherWasteContainer': return new GenericDAO(new OtherWasteContainer());
            default:
                $LOG->warn('Cannot update unkown waste container type: ' . $type);
                throw new Exception("Cannot determine DTO type for container");
        }
    }
}
?>

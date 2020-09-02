<?php

class ParcelValidationException extends Exception {
    public function __construct($msg){
		parent::__construct($msg);
	} 
}

class ParcelManager {
    private $parcelDAO;
    private $parcelAuthorizationDAO;
    private $authoriazationDAO;

    public function __construct(){
        $this->parcelDAO = new GenericDAO(new Parcel());
        $this->parcelAuthorizationDAO = new GenericDAO(new ParcelAuthorization());
        $this->authorizationDAO = new GenericDAO(new Authorization());
    }

    public function saveParcel( Parcel $parcel ){

        // Split up Active and Inactive parcel auths
        // Do this here so we can reference this lists for saving after validation
        $parcelAuths = $parcel->getParcelAuthorizations() ?? [];
        $active_auths = array_filter( $parcelAuths, function($a){ return $a->getIs_active(); });
        $inactive_auths = array_filter( $parcelAuths, function($a){ return !$a->getIs_active(); });

        //////////////////////////
        // Validate Parcel
        $validation = $this->validateParcel($parcel, $active_auths, $inactive_auths);

        if( is_string($validation) ){
            throw new ParcelValidationException($validation);
        }

        // Parcel is valid
        $savedParcel = $this->parcelDAO->save($parcel);

        //////////////////////////////////////
        // Ok, active parcel-auths are valid
        // Now we can delete inactive and save active ones
        foreach($inactive_auths as $oldAuth){
            // Delete $oldAuth
            $this->parcelAuthorizationDAO->deleteById($oldAuth->getKey_id());
        }

        // Save/Update parcel auths
        foreach($parcelAuths as $parcelAuth){
            // Ensure parcel auth always references parcel
            $parcelAuth->setParcel_id( $savedParcel->getKey_id() );
            $this->parcelAuthorizationDAO->save($parcelAuth);
        }

        return $savedParcel;
    }

    protected function validateParcel( Parcel $parcel, Array $active_auths, Array $inactive_auths ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // TODO: Validate high-level Parcel details

        //////////////////////////
        // Validate Parcel Auths

        //   At least one active auth
        if( empty($active_auths) ){
            return 'At least one active Authorization is required';
        }

        //   Active auths percentage between (0,100]
        $active_percentages = array_map(function($a){ return $a->getPercentage(); }, $active_auths);
        $invalid_percentages = array_filter(
            $active_percentages,
            function($percent){ return $percent <= 0 || $percent > 100; });
        if( !empty($invalid_percentages) ){
            return 'Authorization percentages must be greater than zero and less than or equal to 100 (invalid: )' . implode(',', $invalid_percentages);
        }

        //   Active auths percentages equal 100%
        $sum = array_sum( $active_percentages );
        if( $sum != 100 ){
            return 'Authorization percentages must sum to 100 (sum=' . $sum . ')';
        }

        foreach($active_auths as $pauth){
            //   Active auths reference this parcel (if parcel_id is specified)
            if( $pauth->getParcel_id() != $parcel->getKey_id() ){
                return 'Authorizations must reference the same Parcel';
            }

            //   Active auths reference Authorization
            $authorization = null;
            if( $pauth->getAuthorization_id() ){
                $authorization = $this->authorizationDAO->getById($pauth->getAuthorization_id());
            }

            if( !$authorization ) {
                return 'Invalid Authorization specified';
            }
            else {
                // Nuclide quantities must not exceed authorized amount
                $nuclideQuantity = $parcel->getQuantity() * ($pauth->getPercentage() / 100);
                if( $nuclideQuantity > $authorization->getMax_quantity() ){
                    return $authorization->getIsotopeName() . ' quantity may not exceed ' . $authorization->getMax_quantity();
                }
            }

        }

        return true;
    }
}
?>

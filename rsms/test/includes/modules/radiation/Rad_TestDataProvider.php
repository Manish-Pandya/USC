<?php
class Rad_TestDataProvider {

    public static function create_isotope( Rad_ActionManager $radManager, string $name, string $emitter_type, float $auth_limit = null, bool $is_mass = true, $license_line_item = null ){
        $isotope = new Isotope();
        $isotope->setIs_active(true);
        $isotope->setName($name);
        $isotope->setEmitter_type($emitter_type);
        $isotope->setAuth_limit($auth_limit);
        $isotope->setLicense_line_item($license_line_item);
        $isotope->setIs_mass($is_mass);

        return $radManager->saveIsotope($isotope);
    }

    public static function create_pi_authorization( Rad_ActionManager $radManager, PrincipalInvestigator $pi ){
        $pi_auth = new PIAuthorization();
        $pi_auth->setPrincipal_investigator_id($pi->getKey_id());
        $pi_auth->setUsers( [$pi->getUser()] );
        $pi_auth->setAuthorization_number('TEST-AUTH-PI' . $pi->getKey_id());
        $pi_auth->setApproval_date(date('Y-m-d H:i:s'));

        return $radManager->savePIAuthorization($pi_auth);
    }

    public static function create_isotope_authorization( Rad_ActionManager $radManager, PIAuthorization $pi_auth, Isotope $isotope, $max = 100 ){
        $auth = new Authorization();
        $auth->setIs_active(true);
        $auth->setPi_authorization_id($pi_auth->getKey_id());
        $auth->setIsotope_id($isotope->getKey_id());
        $auth->setMax_quantity( (int) $max );
        $auth->setForm('TEST');

        return $radManager->saveAuthorization($auth);
    }

    public static function create_parcel( Rad_ActionManager $radManager, PrincipalInvestigator $pi, Authorization $auth, $quantity = 10 ){
        $parcel = new Parcel();
        $parcel->setIs_active(true);
        $parcel->setAuthorization_id($auth->getKey_id());
        $parcel->setPrincipal_investigator_id($pi->getKey_id());
        $parcel->setStatus('Delivered');
        $parcel->setRs_number('TEST-' . $auth->getKey_id());
        $parcel->setQuantity( (int) $quantity );

        return $radManager->saveParcel($parcel);
    }

}
?>

<?php

class VendorAPIModule implements RSMS_Module {
    public const API_NAME_LOCATION = 'location';

    public function getModuleName(){
        return 'VendorApi';
    }

    public function getUiRoot(){
        return '/v1';
    }

    public function isEnabled() {
        return basename($_SERVER['SCRIPT_FILENAME']) === 'api_request_handler.php';
    }

    public function getActionManager(){
        // multiple services
        //  Based on API path parameter
        $api = $this->getCurrentAPI();
        switch( $api ){
            case VendorAPIModule::API_NAME_LOCATION: return new LocationApiService();
            default:
                throw new Exception("Invalid API '$api'");
        }
    }

    public function getActionConfig(){
        $api_restricted_roles = [];

        $mappings = [];

        // FIXME: This only supports one API; consider namespacing or splitting into submodules for additional
        if( VendorAPIModule::API_NAME_LOCATION === $this->getCurrentAPI() ){
            $mappings['getAll'] = new SecuredActionMapping('getAll', $api_restricted_roles);
            $mappings['search'] = new SecuredActionMapping('search', $api_restricted_roles);
            $mappings['getInfo'] = new SecuredActionMapping('getInfo', $api_restricted_roles);
            $mappings['getDetail'] = new SecuredActionMapping('getDetail', $api_restricted_roles);
        }

        return $mappings;
    }

    private function getCurrentAPI(){
        return $_REQUEST['api'] ?? self::API_NAME_LOCATION;
    }
}
?>
<?php

class VendorAPIModule implements RSMS_Module {
    public function getModuleName(){
        return 'VendorApi';
    }

    public function getUiRoot(){
        return '/v1';
    }

    public function isEnabled() {
        return basename($_SERVER['SCRIPT_FILENAME']) === 'api.php';
    }

    public function getActionManager(){
        return new VendorApi_ActionManager();
    }

    public function getActionConfig(){
        return array_merge(
            VendorApi_ActionMappingFactory::readActionConfig()
        );
    }
}
?>
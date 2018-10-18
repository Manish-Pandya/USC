<?php

class CoreModule implements RSMS_Module {
    public function getUiRoot(){
        return '/';
    }

    public function isEnabled() {
        return true;
    }

    public function getActionManager(){
        return new ActionManager();
    }

    public function getActionConfig(){
        return ActionMappingFactory::readActionConfig();
    }
}
?>
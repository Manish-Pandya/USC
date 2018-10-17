<?php

interface RSMS_Module {

    public function isEnabled();
    public function getActionManager();

    public function registerActionMappings();
}
?>
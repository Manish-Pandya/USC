<?php

interface RSMS_Module {

    public function getModuleName();
    public function isEnabled();
    public function getActionManager();
    public function getActionConfig();
}
?>
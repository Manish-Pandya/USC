<?php

class RadiationModule implements RSMS_Module, MyLabWidgetProvider {
    public function getModuleName(){
        return 'Radiation';
    }

    public function getUiRoot(){
        return '/rad';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && strstr($_SERVER["HTTP_REFERER"], '/rad/' ) ) || isset($_GET['rad']) )
            return true;

        return false;
    }

    public function getActionManager(){
        return new Rad_ActionManager();
    }

    public function getActionConfig(){
        return Rad_ActionMappingFactory::readActionConfig();
    }

    public function getMyLabWidgets( User $user ){
        $widgets = array();

        // Only display verification widget to labs with Rad authorizations
        $manager = $this->getActionManager();

        // Get relevant PI for lab
        $principalInvestigator = $manager->getPIByUserId( $user->getKey_id() );
        $auth = $principalInvestigator->getCurrentPi_authorization();

        if( !empty($auth) ){
            $radWidget = new MyLabWidgetDto();
            $radWidget->title = "Radioactive Materials";
            $radWidget->image = "radiation-large-icon.png";
            $radWidget->template = "radiation-lab";
            $radWidget->data = new GenericDto(array(
                "id" => $principalInvestigator->getKey_id()
            ));

            $widgets[] = $radWidget;
        }

        return $widgets;
    }
}
?>
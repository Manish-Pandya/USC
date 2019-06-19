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

        // Show this widget for users that have the Radiation User role
        if( CoreSecurity::userHasAnyRole($user, array('Radiation User')) ){

            $radWidget = new MyLabWidgetDto();
            $radWidget->title = "Radioactive Materials";
            $radWidget->image = "radiation-large-icon.png";
            $radWidget->template = "radiation-lab";

            // Get all PIs which have Active Authorizations which list this user
            $dao = new PIAuthorizationDAO();
            $userAuthorizations = $dao->getUserAuthorizations( $user->getKey_id() );

            if( isset($userAuthorizations) && !empty($userAuthorizations) ){

                // Prepare Data for each Lab this user has auth's for
                //    Note that there may be multiple authorizations listed for a single PI;
                //    we only need one reference to each Lab they have access to
                $pi_ids = array_unique(
                    array_map(
                        function($a){
                            // Map authorizatino to PI ID
                            return $a->getPrincipal_investigator_id();
                        },
                        $userAuthorizations
                    )
                );

                $piDao = new PrincipalInvestigatorDAO();
                $pis = array();
                foreach($pi_ids as $id){
                    $pi = $piDao->getById($id);
                    $pis[] = DtoFactory::piToDto($pi);
                }

                // Pass the PI details as widget data
                $radWidget->data = new GenericDto(array(
                    "AuthorizedPIs" => $pis
                ));
            }

            $widgets[] = $radWidget;
        }

        return $widgets;
    }
}
?>

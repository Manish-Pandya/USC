angular
    .module('VerificationApp')
    .controller('PersonnelCtrl', function ($scope, $rootScope, applicationControllerFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;

        $scope.contactOptions  = ["In another PI's lab", "No longer at the university", "Still in this lab, but no longer a contact"];
        $scope.personnelOtions = ["In another PI's lab", "No longer at the university", "Still in this lab, but now a lab contact"];
        var id = 1;

        $rootScope.loading = getVerification(id)
                                .then(getPI);

        function getVerification(id){
            return ac.getVerification(id)
                    .then(
                        function(){
                            $scope.verification = dataStoreManager.getById("Verification",id);
                            return $scope.verification.Principal_investigator_id;
                        }
                    )
        }

        function getPI(id){
           return ac.getPI(id)
                    .then(
                        function(){
                            $scope.PI = dataStoreManager.getById("PrincipalInvestigator",id);
                        }
                    )
        }

    });

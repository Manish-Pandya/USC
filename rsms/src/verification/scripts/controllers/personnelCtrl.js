angular
    .module('VerificationApp')
    .controller('PersonnelCtrl', function ($scope, applicationControllerFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;
        var id = 1;

        getVerification(id)
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

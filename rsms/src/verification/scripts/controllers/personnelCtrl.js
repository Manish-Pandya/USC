angular
    .module('VerificationApp')
    .controller('PersonnelCtrl', function ($scope, applicationControllerFactory) {
        var ac = applicationControllerFactory;
        $scope.ac = ac;

         ac.getPI(1)
            .then(
                function(){
                    $scope.PI = dataStoreManager.getById("PrincipalInvestigator",1);
                }
            )
    });

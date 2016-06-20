angular
    .module('VerificationApp')
    .controller('AdminListCtrl', function ($scope, $rootScope, $modal, applicationControllerFactory, modelInflatorFactory, userHubFactory, locationHubFactory) {
        var ac = $scope.ac = applicationControllerFactory;
        $scope.dataStoreManager = dataStoreManager;
        $scope.constants = Constants;

        var currentYear = new Date().getFullYear();
        $rootScope.loading = getVerificationsByYear(currentYear);

        function getVerificationsByYear(year) {
            console.log(year);
            return ac.getVerificationsByYear(year)
                    .then(
                        function (years) {
                            $scope.years = years;
                            $scope.selectedYear = year.toString();
                            console.log(dataStore);
                            $scope.verifications = dataStoreManager.get("Verification");
                            return years;
                        }
                    )
        }

        $scope.selectYear = function (year) {
            getVerificationsByYear(year);
        }

    });

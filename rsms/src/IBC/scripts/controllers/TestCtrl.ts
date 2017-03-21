angular
    .module('ng-IBC') 
    .controller('TestCtrl', function ($rootScope, $scope, $q, $stateParams) {
        //register classes with app
        console.log("approved classNames:", InstanceFactory.getClassNames(ibc));
        console.log("TestCtrl running");

        var getProtocol = function (): Promise<any> {
            $scope.protocols = [];
            $scope.protocol = [];
            /*return $q.all([DataStoreManager.getAll("IBCProtocol", $scope.protocols, false)]).then((p) => {
                $scope.protocol = $scope.protocols[0];*/
            return $q.all([DataStoreManager.getById("IBCProtocol", $stateParams.id, $scope.protocol, false)]).then((p) => {
                //$scope.protocol = p[0];
                console.log($scope.protocol[0]);
                $scope.protocol[0].Project_title = $scope.protocol[0].Project_title + " updated in controller";
                console.log(DataStoreManager._actualModel['IBCProtocol']['Data'][0]);
            });
        }

        $scope.loading = $rootScope.getCurrentRoles().then(getProtocol);

    });
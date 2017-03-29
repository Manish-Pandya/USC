angular
    .module('ng-IBC')
    .controller('TestCtrl', function ($rootScope, $scope, $q, $stateParams) {
    //register classes with app
    console.log("approved classNames:", InstanceFactory.getClassNames(ibc));
    console.log("TestCtrl running");
    var getProtocol = function () {
        $scope.protocols = new ViewModelInstance();
        $scope.protocol = new ViewModelInstance();
        /*return $q.all([DataStoreManager.getAll("IBCProtocol", $scope.protocols, false)]).then((p) => {
            $scope.protocol = $scope.protocols[0];*/
        return $q.all([DataStoreManager.getById("IBCProtocol", $stateParams.id, $scope.protocol, [ibc.IBCProtocol.RevisionMap])]).then(function (p) {
            //$scope.protocol.data.Project_title = $scope.protocol.data.Project_title + " updated in controller";
            console.log($scope.protocol.data == DataStoreManager._actualModel['IBCProtocol']['Data'][0].viewModelWatcher);
            console.log($scope.protocol.data.IBCProtocolRevisions[0] == DataStoreManager._actualModel['IBCProtocolRevision']['Data'][0].viewModelWatcher);
            DataStoreManager._actualModel['IBCProtocolRevision']['Data'][0].viewModelWatcher.Protocol_type += "... YUP!";
            console.log($scope.protocol.data);
            console.log(DataStoreManager._actualModel);
        });
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getProtocol);
});

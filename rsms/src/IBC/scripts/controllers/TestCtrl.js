angular
    .module('ng-IBC')
    .controller('TestCtrl', function ($rootScope, $scope, $q, $stateParams) {
    console.log("TestCtrl running");
    var getProtocol = function () {
        $scope.protocols = new ViewModelHolder();
        $scope.protocol = new ViewModelHolder();
        return $q.all([DataStoreManager.getAll("IBCProtocol", $scope.protocols, [ibc.IBCProtocol.RevisionMap])]).then(function (p) {
            //return $q.all([DataStoreManager.getById("IBCProtocol", $stateParams.id, $scope.protocol, [ibc.IBCProtocol.RevisionMap])]).then((p) => {
            $scope.protocol.data = $scope.protocols.data[0];
            //$scope.protocol.data.Project_title = $scope.protocol.data.Project_title + " updated in controller";
            console.log($scope.protocol.data == DataStoreManager._actualModel['IBCProtocol']['Data'][0].viewModelWatcher);
            console.log($scope.protocol.data.IBCProtocolRevisions[0] == DataStoreManager._actualModel['IBCProtocolRevision']['Data'][0].viewModelWatcher);
            DataStoreManager._actualModel['IBCProtocolRevision']['Data'][0].viewModelWatcher.Protocol_type += "... YUP!";
            console.log($scope.protocol.data);
            console.log(DataStoreManager._actualModel);
            var newProtocol = new ibc.IBCProtocol();
            InstanceFactory.copyProperties(newProtocol, $scope.protocol.data);
            newProtocol.Key_id = newProtocol.UID = "777";
            newProtocol.Project_title = "I'm brand new!";
            DataStoreManager.commitToActualModel(newProtocol); // test saving to actual model
            console.log($scope.protocols.data); // should update with newProtocol added
            console.log(DataStoreManager._actualModel); // double-check all data is sound
            var newRevision = new ibc.IBCProtocolRevision();
            InstanceFactory.copyProperties(newRevision, $scope.protocol.data.IBCProtocolRevisions[0]);
            newRevision.Key_id = newRevision.UID = "42";
            newRevision.Protocol_id = "1";
            newRevision.Protocol_type = "I'm so new!";
            DataStoreManager.commitToActualModel(newRevision, true); // test saving to actual model
            console.log($scope.protocol.data); // should update with newRevision added to IBCProtocolRevisions collection!
            console.log(DataStoreManager._actualModel); // double-check all data is sound
        });
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getProtocol);
});

'use strict';
/**
 * @ngdoc function
 * @name IBCCtrl.controller:IBCEmailMgmtCtrl
 * @description
 * # IBCEmailMgmtCtrl
 * Controller of the IBC protocal Email Management view
 */
angular.module('ng-IBC')
    .controller('IBCEmailMgmtCtrl', function ($rootScope, $scope, $modal, $location, $q) {
    console.log("IBCEmailMgmtCtrl running");
    var getRecipients = function () {
        $scope.recipients = [];
        for (var n = 0; n < 7; n++) {
            $scope.recipients.push("test" + n + "@domain.fun");
        }
    };
    var getEmailData = function () {
        $scope.emails = new ViewModelHolder();
        $scope.protocol = new ViewModelHolder();
        // TODO: Remove test protocol fetching
        return $q.all([DataStoreManager.getAll("IBCEmailGen", $scope.emails), DataStoreManager.getById("IBCProtocol", 2, $scope.protocol, [ibc.IBCProtocol.RevisionMap, ibc.IBCProtocol.PIMap])])
            .then(function (whateverGotReturned) {
            console.log($scope.emails.data);
            console.log($scope.protocol.data);
            console.log(DataStoreManager._actualModel);
        })
            .catch(function (reason) {
            console.log("bad Promise.all:", reason);
        });
    };
    $scope.save = function (copy) {
        $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getRecipients).then(getEmailData);
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new ibc.IBCEmailGen;
        }
        // TODO: Remove test revision setting
        object.Revision = $scope.protocol.data.IBCProtocolRevisions[$scope.protocol.data.IBCProtocolRevisions.length - 1];
        modalData[object.thisClass['name']] = object;
        DataStoreManager.ModalData = modalData;
        var modalInstance = $modal.open({
            templateUrl: 'views/modals/email-gen-parsed-modal.html',
            controller: 'IBCEmailMgmtModalCtrl'
        });
    };
})
    .controller('IBCEmailMgmtModalCtrl', function ($scope, $rootScope, $modalInstance, $modal, convenienceMethods, $q) {
    $scope.constants = Constants;
    $scope.modalData = DataStoreManager.ModalData;
    //TODO: David add param to force DataStoreManager to fetch from server
    $rootScope.loading = $q.all([XHR.POST("getPreviewCorpus", $scope.modalData.IBCEmailGen)]).then(function (r) {
        console.log($scope.modalData.IBCEmailGen, r);
        $scope.modalData.IBCEmailGen.ParsedCorpus = r;
    });
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});

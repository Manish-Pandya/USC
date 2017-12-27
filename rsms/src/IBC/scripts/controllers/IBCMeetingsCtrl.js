'use strict';
/**
 * @ngdoc function
 * @name IBCCtrl.controller:IBCMeetingsCtrl
 * @description
 * # IBCMeetingsCtrl
 * Controller of the IBC protocal Email Management view
 */
angular.module('ng-IBC')
    .controller('IBCMeetingsCtrl', function ($rootScope, $scope, $modal, $location, $q) {
    console.log("IBCMeetingsCtrl running");
    $scope.save = function (copy) {
        $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
    };
    $scope.loading = $rootScope.getCurrentRoles();
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new ibc.IBCMeeting;
        }
        modalData[object.thisClass['name']] = object;
        DataStoreManager.ModalData = modalData;
        var modalInstance = $modal.open({
            templateUrl: 'views/modals/scheduler-modal.html',
            controller: 'IBCMeetingsModalCtrl'
        });
    };
})
    .controller('IBCMeetingsModalCtrl', function ($scope, $rootScope, $modalInstance, $modal, convenienceMethods, $q) {
    $scope.constants = Constants;
    $scope.modalData = DataStoreManager.ModalData;
    $rootScope.loading = $q.all([XHR.POST("getSchedulerData", $scope.modalData.IBCMeeting)]).then(function (r) {
        console.log($scope.modalData.IBCMeeting, r);
    });
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});

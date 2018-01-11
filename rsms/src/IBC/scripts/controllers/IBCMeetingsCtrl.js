'use strict';
/**
 * @ngdoc function
 * @name IBCCtrl.controller:IBCMeetingsCtrl
 * @description
 * # IBCMeetingsCtrl
 * Controller of the IBC protocal Meetings view
 */
angular.module('ng-IBC')
    .controller('IBCMeetingsCtrl', function ($rootScope, $scope, $modal, $location, $q) {
    console.log("IBCMeetingsCtrl running");
    function getMeetings() {
        $scope.meetings = new ViewModelHolder();
        return $q.all([DataStoreManager.getAll("IBCMeeting", $scope.meetings, true)])
            .then(function (whateverGotReturned) {
            console.log(DataStoreManager._actualModel);
        })
            .catch(function (reason) {
            console.log("bad Promise.all:", reason);
        });
    }
    $scope.save = function (copy) {
        console.log("saving:", copy);
        $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getMeetings);
    $scope.openModal = function (object) {
        var modalData = {};
        if (!object) {
            object = new ibc.IBCMeeting;
            object['Is_active'] = 1;
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
    $scope.save = function (copy) {
        copy.Meeting_date = convenienceMethods.setMysqlTime(copy.Meeting_date);
        $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
    };
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});

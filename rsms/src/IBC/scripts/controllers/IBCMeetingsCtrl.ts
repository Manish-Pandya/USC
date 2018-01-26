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

        function getMeetings(): void {
            $scope.meetings = new ViewModelHolder();
            return $q.all([DataStoreManager.getAll("IBCMeeting", $scope.meetings, true)])
                .then(
                    function (whateverGotReturned) {
                        console.log($scope.meetings.data);
                        console.log(DataStoreManager._actualModel);
                    }
                )
                .catch(
                    function (reason) {
                        console.log("bad Promise.all:", reason);
                    }
                )
        }

        $scope.save = function (copy) {
            console.log("saving:", copy);
            $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
        }

        $scope.loading = $rootScope.getCurrentRoles().then(getMeetings);

        $scope.openModal = function (object: FluxCompositerBase) {
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
        }
    })
    .controller('IBCMeetingsModalCtrl', function ($scope, $rootScope, $modalInstance, $modal, convenienceMethods, $q) {
        $scope.constants = Constants;
        $scope.modalData = DataStoreManager.ModalData;

        $rootScope.loading = $q.all([XHR.POST("getIBCPossibleMeetingAttendees", $scope.modalData.possibleAttendees)]).then((r) => {
            $scope.modalData.possibleAttendees = r[0];
            //console.log($scope.modalData.possibleAttendees);
        })

        $scope.save = function (copy: ibc.IBCMeeting) {
            copy.Meeting_date = convenienceMethods.setMysqlTime(copy.Meeting_date);
            // gather all attendees
            for (var n in $scope.modalData.possibleAttendees) {
                var attendee: ibc.User = $scope.modalData.possibleAttendees[n];
                if (attendee["isChecked"]) copy.Attendees.push(attendee);
            }
            $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
        }
        
        $scope.close = function () {
            $modalInstance.dismiss();
        }
    })
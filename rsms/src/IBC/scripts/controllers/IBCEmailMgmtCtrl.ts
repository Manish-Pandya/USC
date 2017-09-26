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

        var getRecipients = function (): void {
            $scope.recipients = [];
            for (var n = 0; n < 7; n++) {
                $scope.recipients.push("test" + n + "@domain.fun");
            }
        }

        var getEmailData = function (): void {
            $scope.emails = new ViewModelHolder();
            return $q.all([DataStoreManager.getAll("IBCEmailGen", $scope.emails)])
                .then(
                    function (whateverGotReturned) {
                        console.log($scope.emails.data);
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
            $scope.saving = $q.all([DataStoreManager.save(copy)]).then($scope.close);
        }

        $scope.loading = $rootScope.getCurrentRoles().then(getRecipients).then(getEmailData);
    })
    .controller('IBCEmailMgmtModalCtrl', function ($scope, $rootScope, $modalInstance, $modal, convenienceMethods, roleBasedFactory) {
        $scope.constants = Constants;
        var rbf = roleBasedFactory;

        $scope.close = function () {
            $modalInstance.dismiss();
        }
    })
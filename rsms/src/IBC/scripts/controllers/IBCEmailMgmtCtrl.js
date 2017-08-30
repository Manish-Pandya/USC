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
        for (var n = 0; n < 10; n++) {
            $scope.recipients.push("test" + n + "@domain.fun");
        }
    };
    var getEmailData = function () {
        $scope.subject = "Protocol Approved (Review prior to sending):";
        $scope.corpus = "Hello World";
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getRecipients).then(getEmailData);
})
    .controller('IBCEmailMgmtModalCtrl', function ($scope, $rootScope, $modalInstance, $modal, convenienceMethods, roleBasedFactory) {
    $scope.constants = Constants;
    var rbf = roleBasedFactory;
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});

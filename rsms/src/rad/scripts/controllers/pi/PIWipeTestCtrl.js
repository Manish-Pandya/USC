'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # WipeTestController
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('PIWipeTestController', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
        //do we have access to action functions?
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.constants = Constants;

        var getPI = function (id) {
            return af.getRadPIById(id)
                .then(
                    function (pi) {
                        console.log(pi);
                        $scope.pi = pi;
                    },
                    function () { }
                )
        }

        $rootScope.piPromise = getPI($stateParams.pi)       

        $scope.editPIWipe = function (test, wipe) {
            $rootScope.PIWipeCopy = {}
            if (!test.PIWipes) test.PIWipes = [];
            var i = test.PIWipes.length;
            while (i--) {
                test.PIWipes[i].edit = false;
            }

            if (!wipe) {
                $rootScope.PIWipeCopy = new window.PIWipe();
                $rootScope.PIWipeCopy.Class = "PIWipe";
                $rootScope.PIWipeCopy.Is_active = true;
                $rootScope.PIWipeCopy.PI_wipe_test_id = test.Key_id
                $rootScope.PIWipeCopy.edit = true;
                test.PIWipes.unshift($rootScope.PIWipeCopy);
            } else {
                wipe.edit = true;
                af.createCopy(wipe);
            }

        }

        $scope.addPIWipe = function (test) {
            
            if (!test.PIWipes) test.PIWipes = [];
            //all wipe tests must have a background wipe
            if (!test.PIWipes[0] || !test.PIWipes[0].Location || test.PIWipes[0].Location != "Background") {
                var bgWipe = new window.PIWipe();
                bgWipe.PI_wipe_test_id = test.Key_id;
                bgWipe.Class = "PiWipe";
                bgWipe.edit = false;
                bgWipe.Location = "Background";
                test.PIWipes.unshift(bgWipe);
            }

            var piWipe = new window.PIWipe();
            piWipe.PI_wipe_test_id = test.Key_id;
            piWipe.Class = "PiWipe";
            piWipe.edit = true;
            test.PIWipes.push(piWipe);
            test.showWipes = true;
            test.adding = true;
        }

        $scope.cancelPIWipes = function (test) {
            console.log(test);
            for (var x = 0; x < test.PIWipes.length; x++) {
                if (!test.PIWipes[x].Key_id) {
                    test.PIWipes.splice(x, 1);
                }
            }
            test.adding = false;
        }

        $scope.openModal = function (object) {
     
            var modalData = {};
            if (object) modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/pi/pi-modals/pi-wipe-modal.html',
                controller: 'PIWipeTestModalCtrl'
            });
        }

    })
    .controller('PIWipeTestModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);

        if (!$scope.modalData.PIWipeTest) {
            $scope.modalData.PIWipeTest = new window.PIWipeTest();
            $scope.modalData.PIWipeTest.Class = "PIWipeTest";
            $scope.modalData.PIWipeTest.Is_active = true;
        }

        $scope.save = function (test) {
            af.savePIWipeTest(test)
                .then($scope.close);
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }

    }])
    

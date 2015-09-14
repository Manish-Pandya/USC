'use strict';

/**
 * @ngdoc function
 * @name 00RsmsAngularOrmApp.controller:PiDetailCtrl
 * @description
 * # WipeTestController
 * Controller of the 00RsmsAngularOrmApp Radmin PI dashboard
 */
angular.module('00RsmsAngularOrmApp')
    .controller('WipeTestController', function ($scope, actionFunctionsFactory, convenienceMethods, $stateParams, $rootScope, $modal) {
        //do we have access to action functions?
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        var getParcels = function () {
            return af.getAllParcels()
                .then(
                    function (parcels) {
                        var i = parcels.length;
                        while (i--) {
                            parcels[i].loadPrincipalInvestigator();
                        }
                        $rootScope.parcels = dataStore.Parcel;
                        return parcels;
                    }
                );
        }

        var getMiscTests = function () {
            return af.getAllMiscellaneousWipeTests()
                .then(
                    function (tests) {
                        if (!dataStore.MiscellaneousWipeTest) dataStore.MiscellaneousWipeTest = [];
                        $rootScope.miscellaneousWipeTests = dataStore.MiscellaneousWipeTest;
                    }
                )
        }

        getParcels()
            .then(getMiscTests);

        $scope.editWipeParcelWipeTest = function (parcel, test) {
            $rootScope.ParcelWipeTestCopy = {}

            if (!test) {
                $rootScope.ParcelWipeTestCopy = new window.ParcelWipeTest();
                $rootScope.ParcelWipeTestCopy.Parcel_id = parcel.Key_id
                $rootScope.ParcelWipeTestCopy.Class = "ParcelWipeTest";
                $rootScope.ParcelWipeTestCopy.Is_active = true;
            } else {
                af.createCopy(test);
            }

            var i = $scope.wipeTestParcels.length
            while (i--) {
                $scope.wipeTestParcels[i].Creating_wipe = false;
            }
            parcel.Creating_wipe = true;

        }

        $scope.cancelParcelWipeTestEdit = function (parcel) {
            parcel.Creating_wipe = false;
            $rootScope.ParcelWipeTestCopy = {}
        }

        $scope.editWipeParcelWipe = function (wipeTest, wipe) {
            $rootScope.ParcelWipeCopy = {}
            if (!wipeTest.Parcel_wipes) wipeTest.Parcel_wipes = [];
            var i = wipeTest.Parcel_wipes.length;
            while (i--) {
                wipeTest.Parcel_wipes[i].edit = false;
            }

            if (!wipe) {
                $rootScope.ParcelWipeCopy = new window.ParcelWipe();
                $rootScope.ParcelWipeCopy.Parcel_wipe_test_id = wipeTest.Key_id
                $rootScope.ParcelWipeCopy.Class = "ParcelWipe";
                $rootScope.ParcelWipeCopy.edit = true;
                $rootScope.ParcelWipeCopy.Is_active = true;
                wipeTest.Parcel_wipes.unshift($rootScope.ParcelWipeCopy);
            } else {
                wipe.edit = true;
                af.createCopy(wipe);
            }

        }

        $scope.addMiscWipes = function (test) {
            //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
            if (!test.Miscellaneous_wipes) test.Miscellaneous_wipes = [];
            var i = 10
            while (i--) {
                var miscellaneousWipe = new window.MiscellaneousWipe();
                miscellaneousWipe.Miscellaneous_wipe_test_id = test.Key_id;
                miscellaneousWipe.Class = "MiscellaneousWipe";
                miscellaneousWipe.edit = true;
                test.Miscellaneous_wipes.push(miscellaneousWipe);
            }
            test.adding = true;
        }

        $scope.cancelParcelWipeEdit = function (wipe, test) {
            wipe.edit = false;
            $rootScope.ParcelWipeCopy = {};
            var i = test.Parcel_wipes.length;
            while (i--) {
                if (!test.Parcel_wipes[i].Key_id) {
                    test.Parcel_wipes.splice(i, 1);
                }
            }
        }

        $scope.clouseOutMWT = function (test) {
            af.createCopy(test);
            $rootScope.MiscellaneousWipeTestCopy.Closeout_date = convenienceMethods.setMysqlTime(new Date());
            af.saveMiscellaneousWipeTest($rootScope.MiscellaneousWipeTestCopy);
        }

        $scope.cancelMiscWipeTestEdit = function (test) {
            $scope.Creating_wipe = false;
            $rootScope.ParcelWipeTestCopy = {}
        }

        $scope.editMiscWipe = function (test, wipe) {
            $rootScope.MiscellaneousWipeCopy = {}
            if (!test.Miscellaneous_wipes) test.Miscellaneous_wipes = [];
            var i = test.Miscellaneous_wipes.length;
            while (i--) {
                test.Miscellaneous_wipes[i].edit = false;
            }

            if (!wipe) {
                $rootScope.MiscellaneousWipeCopy = new window.MiscellaneousWipe();
                $rootScope.MiscellaneousWipeCopy.Class = "MiscellaneousWipe";
                $rootScope.MiscellaneousWipeCopy.Is_active = true;
                $rootScope.MiscellaneousWipeCopy.miscellaneous_wipe_test_id = test.Key_id
                $rootScope.MiscellaneousWipeCopy.edit = true;
                test.Miscellaneous_wipes.unshift($rootScope.MiscellaneousWipeCopy);
            } else {
                wipe.edit = true;
                af.createCopy(wipe);
            }

        }

        $scope.cancelMiscWipeEdit = function (test, wipe) {
            wipe.edit = false;
            $rootScope.MiscellaneousWipeCopy = {};
            var i = test.Miscellaneous_wipes.length;
            while (i--) {
                if (!test.Miscellaneous_wipes[i].Key_id) {
                    console.log()
                    test.Miscellaneous_wipes.splice(i, 1);
                }
            }
        }

        //Suggested/common locations for performing parcel wipes
        $scope.parcelWipeLocations = ['Background', 'Outside', 'Inside', 'Bag', 'Styrofoam', 'Cylinder', 'Vial', 'Lead Pig'];

        $scope.openModal = function (object) {
            console.log(object);
            var modalData = {};
            if (object) modalData[object.Class] = object;
            af.setModalData(modalData);
            var modalInstance = $modal.open({
                templateUrl: 'views/admin/admin-modals/misc-wipe-modal.html',
                controller: 'MiscellaneousWipeTestCtrl'
            });
        }

    })
    .controller('MiscellaneousWipeTestCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods) {
        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        console.log($scope.modalData);

        if (!$scope.modalData.MiscellaneousWipeTest) {
            $scope.modalData.MiscellaneousWipeTest = new window.MiscellaneousWipeTest();
            $scope.modalData.MiscellaneousWipeTest.Class = "MiscellaneousWipeTest";
            $scope.modalData.MiscellaneousWipeTest.Is_active = true;
        }

        $scope.save = function (test) {
            af.saveMiscellaneousWipeTest(test)
                .then($scope.close);
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }

  }])
    .controller('WipeTestModalCtrl', ['$scope', '$rootScope', '$modalInstance', 'actionFunctionsFactory', 'convenienceMethods', 'modelInflatorFactory', function ($scope, $rootScope, $modalInstance, actionFunctionsFactory, convenienceMethods, modelInflatorFactory) {

        //TODO:  if af.getModalData() doesn't have wipeTest, create and save one for it
        //       creating wipe test message while loading
        //
        //

        var af = actionFunctionsFactory;
        $scope.af = af;
        $scope.modalData = af.getModalData();
        editWipeParcelWipeTest($scope.modalData.ParcelCopy, $scope.modalData.Parcel);

        function editWipeParcelWipeTest(parcel, originalParcel) {
            if (!parcel.Wipe_test || !parcel.Wipe_test.length) {
                parcel.Wipe_test = [modelInflatorFactory.instantiateObjectFromJson(new window.ParcelWipeTest())];
                parcel.Wipe_test[0].parcel_id = parcel.Key_id
                parcel.Wipe_test[0].Class = "ParcelWipeTest";
                parcel.Wipe_test[0].edit = true;
                parcel.Wipe_test[0].Parcel_wipes = [];
                for (var i = 0; i < 7; i++) {
                    var wipe = new window.ParcelWipe();
                    wipe.Parcel_wipe_test_id = parcel.Key_id ? parcel.Key : null;
                    wipe.Rading_type = "LSC";
                    wipe.edit = true;
                    wipe.Class = 'ParcelWipe';
                    if (i == 0) wipe.Location = "Background";
                    parcel.Wipe_test[0].Parcel_wipes.push(wipe);
                }
                console.log(parcel.Wipe_test[0]);
            } else {
                console.log(parcel);
                af.createCopy(parcel.Wipe_test[0]);
            }

            originalParcel.Creating_wipe = true;

        }

        $scope.cancelParcelWipeTestEdit = function (parcel) {
            parcel.Creating_wipe = false;
            $rootScope.ParcelWipeTestCopy = {}
        }

        $scope.editWipeParcelWipe = function (wipeTest, wipe) {
            $rootScope.ParcelWipeCopy = {}
            if (!wipeTest.Parcel_wipes) wipeTest.Parcel_wipes = [];
            var i = wipeTest.Parcel_wipes.length;
            while (i--) {
                wipeTest.Parcel_wipes[i].edit = false;
            }

            if (!wipe) {
                af.getModalData().Wipe_test = new window.ParcelWipe();
                af.getModalData().Wipe_test.Parcel_wipe_test_id = wipeTest.Key_id
                af.getModalData().Wipe_test.Class = "ParcelWipe";
                af.getModalData().Wipe_test.edit = true;
                af.getModalData().Wipe_test.Is_active = true;
            } else {
                wipe.edit = true;
                af.createCopy(wipe);
            }

        }

        $scope.addMiscWipes = function (test) {
            //by default, MiscellaneousWipeTests have a collection of 10 MiscellaneousWipes, hence the magic number
            if (!test.Miscellaneous_wipes) test.Miscellaneous_wipes = [];
            var i = 10
            while (i--) {
                var miscellaneousWipe = new window.MiscellaneousWipe();
                miscellaneousWipe.Miscellaneous_wipe_test_id = test.Key_id;
                miscellaneousWipe.Class = "MiscellaneousWipe";
                miscellaneousWipe.edit = true;
                test.Miscellaneous_wipes.push(miscellaneousWipe);
            }
            test.adding = true;
        }

        $scope.cancelParcelWipeEdit = function (wipe, test) {
            wipe.edit = false;
            $rootScope.ParcelWipeCopy = {};
            var i = test.Parcel_wipes.length;
            while (i--) {
                if (!test.Parcel_wipes[i].Key_id) {
                    test.Parcel_wipes.splice(i, 1);
                }
            }
        }

        $scope.onClick = function () {
            alert('wrong ctrl')
        }

        //Suggested/common locations for performing parcel wipes
        $scope.parcelWipeLocations = [
            {
                Name: "Background"
            },
            {
                Name: "Outside"
            },
            {
                Name: "Inside"
            },
            {
                Name: "Bag"
            },
            {
                Name: "Styrofoam"
            },
            {
                Name: "Cylinder"
            },
            {
                Name: "Vial"
            },
            {
                Name: "Lead Pig"
            }
        ]
        $scope.setLocation = function (wipe) {
            if (wipe.Location) {
                var i = $scope.parcelWipeLocations.length;
                while (i--) {
                    if (wipe.Location == $scope.parcelWipeLocations.Name) {
                        wipe.DropLocation = $scope.parcelWipeLocations[i];
                        $scope.$apply();
                        break;
                    }
                }
            }
            console.log(wipe);

        }

        $scope.save = function (test) {
            af.saveParcelWipeTest(test)
                .then($scope.close);
        }

        $scope.close = function () {
            af.deleteModalData();
            $modalInstance.dismiss();
        }
 }])
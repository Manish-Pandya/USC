angular.module('00RsmsAngularOrmApp')
    .directive('containers', function () {
    return {
        restrict: 'E',
        scope: {
            containers: "=",
            close: "=?",
            buttonText: "@?",
            buttonClass: "@?",
            filterFunction: "=?",
            hasComments: "@?",
            suppressCloseDate: "@?",
            additionalParam: "=?",
            addWaste: "=?"
        },
        templateUrl: "./scripts/directives/container-template.html",
        replace: true,
        transclude: false,
        link: function (scope, element, attrs, controller) {
            console.log("attrs", attrs);
            if (!('close' in attrs)) {
                scope.close = null;
            }
            else {
                scope.canClose = true;
            }
            if (!('addWaste' in attrs)) {
                scope.addWaste = null;
            }
            else {
                scope.canAddWaste = true;
            }
            scope.getClassByContainerType = function (container) {
                var classList = "";
                if (container.Class == "WasteBag")
                    classList = "icon-remove-2 solids-containers";
                if (container.Class == "ScintVialCollection")
                    classList = "icon-sv scint-vials";
                if (container.Class == "CarboyUseCycle")
                    classList = "icon-carboy carboys";
                if (container.Class == "OtherWasteContainer")
                    classList = "other icon-beaker-alt red";
                return classList;
            };
        },
        controller: function ($scope, actionFunctionsFactory, roleBasedFactory, $rootScope) {
            console.log($rootScope.R);
            $scope.roleBasedFactory = roleBasedFactory;
            $scope.Constants = Constants;
            $scope.closeIt = function (param) {
                var additionalParam = $scope.additionalParam || null;
                $scope.close(param, additionalParam).then(function (container) {
                    console.log(container);
                    angular.extend(param, container);
                });
            };
            if (!$scope.filterFunction) {
                $scope.filterFunction = function (cabinets) {
                    return function (cabinets) { return cabinets; };
                };
            }
            $scope.isAdmin = function () {
                return roleBasedFactory.getHasPermission([$rootScope.R[Constants.ROLE.NAME.RADIATION_ADMIN], $rootScope.R[Constants.ROLE.NAME.ADMIN]]);
            };
            $scope.af = actionFunctionsFactory;
        }
    };
});

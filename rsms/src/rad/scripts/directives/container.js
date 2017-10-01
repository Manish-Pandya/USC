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
            additionalParam: "=?"
        },
        template: "<ul ng-repeat=\"group in containers | groupBy:'ClassLabel'\" class=\"containers-list\" ng-show=\"containersOfType.length\">\n                            <li class=\"group-header\">\n                                <h3><i class=\"{{getClassByContainerType(group[0])}}\"></i>{{group[0].ClassLabel}}</h3>\n                            </li>\n                            <li ng-repeat=\"c in containersOfType = (group | filter:filterFunction)\" class=\"container-parent\">\n                                <ul>\n                                    <li class=\"labels\">\n                                        <div class=\"container-label\">\n                                            {{c.ViewLabel}}\n                                            <p ng-if=\"c.Description\">{{c.Description}}</p>\n                                        </div>\n                                        <div>\n                                            <button ng-if=\"canClose && (!c.Clearable || roleBasedFactory.getHasPermission([R[Constants.ROLE.NAME.RADIATION_ADMIN]]))\" ng-click=\"close(c, additionalParam)\" class=\"btn {{buttonClass || 'btn-danger'}}\">\n                                                {{buttonText || 'Close Container'}} {{}}\n                                            </button>\n                                            <span ng-if=\"c.Close_date\">Closed {{c.Close_date | dateToIso}}</span>\n                                        </div>\n                                    </li>\n                                    <li>\n                                        <div class=\"contents\"><span ng-if=\"!c.Contents.length\">No</span> Contents</div>\n                                        <div></div>\n                                    </li>\n                                    <li ng-if=\"c.Contents.length\">\n                                        <div class=\"column-header\">Isotope</div>\n                                        <div class=\"column-header\">Quantity</div>\n                                    </li>\n                                    <li ng-repeat=\"amt in c.Contents\" ng-if=\"c.Contents.length\">\n                                        <div>{{amt.Isotope_name}}</div>\n                                        <div>{{amt.Curie_level}}mCi</div>\n                                    </li>\n                                </ul>\n                                <div class=\"pickup-comments\" style=\"padding:0;margin-left:20px;margin-top:10px\" ng-if=\"hasComments\">\n                                    <span ng-if=\"c.Comments && !c.edit\">Comments: {{c.Comments}}<i ng-click=\"af.createCopy(c)\" class=\"icon-pencil primary\"></i></span>\n                                    <span ng-if=\"!c.Comments && !c.edit\">Add Comment:<a class=\"btn btn-success btn-mini\" ng-click=\"af.createCopy(c)\"><i class=\"icon-plus-2 success\"></i></a></span>\n                                    <div class=\"control-group\" ng-if=\"c.edit\">\n                                        <label class=\"control-label\">Comments</label>\n                                        <div class=\"controls\">\n                                            <textarea style=\"width:100%\" ng-model=\"c.Comments\" rows=\"2\" maxlength=\"255\"></textarea>\n                                        </div>\n                                        <button class=\"btn-success btn left\" ng-click=\"af.save(c);\"><i class=\"icon-checkmark\"></i></button>\n                                        <button class=\"btn-danger btn left\" ng-click=\"af.cancelEdit(c)\"><i class=\"icon-cancel-2\"></i></button>\n                                    </div>\n                                </div>\n                            </li>\n                        </ul>",
        replace: true,
        transclude: false,
        link: function (scope, element, attrs, controller) {
            if (!('close' in attrs)) {
                scope.close = null;
            }
            else {
                scope.canClose = true;
            }
            scope.getClassByContainerType = function (container) {
                var classList = "";
                if (container.Class == "WasteBag")
                    classList = "icon-remove-2 solids-containers";
                if (container.Class == "ScintVialCollection")
                    classList = "icon-clipboard scint-vials";
                if (container.Class == "CarboyUseCycle")
                    classList = "icon-carboy carboys";
                if (container.Class == "Other")
                    classList = "other";
                return classList;
            };
        },
        controller: function ($scope, actionFunctionsFactory, roleBasedFactory, $rootScope) {
            console.log($rootScope);
            $scope.closeIt = function (param) {
                var additionalParam = $scope.additionalParam || null;
                $scope.close(param, additionalParam).then(function (container) {
                    console.log(container);
                    angular.extend(param, container);
                });
            };
            $scope.rbf = roleBasedFactory;
            $scope.Constants = Constants;
            if (!$scope.filterFunction) {
                $scope.filterFunction = function (cabinets) {
                    return function (cabinets) { return cabinets; };
                };
            }
            $scope.af = actionFunctionsFactory;
        }
    };
});

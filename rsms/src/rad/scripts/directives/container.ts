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
                additionalParam:"=?"
            },
            template: `<ul ng-repeat="group in containers | groupBy:'ClassLabel'" class="containers-list" ng-show="containersOfType.length">
                            <li class="group-header">
                                <h3><i class="{{getClassByContainerType(group[0])}}"></i>{{group[0].ClassLabel}}</h3>
                            </li>
                            <li ng-repeat="c in containersOfType = (group | filter:filterFunction)" class="container-parent">
                                <ul>
                                    <li class="labels">
                                        <div class="container-label">{{c.ViewLabel}}</div>
                                        <div>
                                            <a ng-if="canClose" ng-click="close(c, additionalParam)" class="btn {{buttonClass || 'btn-danger'}}">
                                                {{buttonText || 'Close Container'}}
                                            </a>
                                            <span ng-if="c.Close_date">Closed {{c.Close_date | dateToIso}}</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="contents"><span ng-if="!c.Contents.length">No</span> Contents</div>
                                        <div></div>
                                    </li>
                                    <li ng-if="c.Contents.length">
                                        <div class="column-header">Isotope</div>
                                        <div class="column-header">Quantity</div>
                                    </li>
                                    <li ng-repeat="amt in c.Contents" ng-if="c.Contents.length">
                                        <div>{{amt.Isotope_name}}</div>
                                        <div>{{amt.Curie_level}}mCi</div>
                                    </li>
                                </ul>
                                <div class="pickup-comments" style="padding:0;margin-left:20px;margin-top:10px" ng-if="hasComments">
                                    <span ng-if="c.Comments && !c.edit">Comments: {{c.Comments}}<i ng-click="af.createCopy(c)" class="icon-pencil primary"></i></span>
                                    <span ng-if="!c.Comments && !c.edit">Add Comment:<a class="btn btn-success btn-mini" ng-click="af.createCopy(c)"><i class="icon-plus-2 success"></i></a></span>
                                    <div class="control-group" ng-if="c.edit">
                                        <label class="control-label">Comments</label>
                                        <div class="controls">
                                            <textarea style="width:100%" ng-model="c.Comments" rows="2" maxlength="255"></textarea>
                                        </div>
                                        <button class="btn-success btn left" ng-click="af.save(c);"><i class="icon-checkmark"></i></button>
                                        <button class="btn-danger btn left" ng-click="af.cancelEdit(c)"><i class="icon-cancel-2"></i></button>
                                    </div>
                                </div>
                            </li>
                        </ul>`,
            replace: true,
            transclude: false,
            link: (scope, element, attrs, controller) => {

                if (!('close' in attrs)) {
                    scope.close = null;
                } else {
                    scope.canClose = true;
                }

                scope.getClassByContainerType = (container: {
                    Class?: string
                }): string => {
                    var classList = "";
                    if (container.Class == "WasteBag") classList = "icon-remove-2 solids-containers";
                    if (container.Class == "ScintVialCollection") classList = "icon-clipboard scint-vials"
                    if (container.Class == "CarboyUseCycle") classList = "icon-carboy carboys"
                    if (container.Class == "Other") classList = "other"

                    return classList;
                }
                
            },
            controller: function ($scope, actionFunctionsFactory ) {            
                $scope.closeIt = (param): any => {
                    let additionalParam = $scope.additionalParam || null;
                    $scope.close(param, additionalParam).then((container) => {
                        console.log(container);
                        angular.extend(param, container);
                    });
                }

                if (!$scope.filterFunction) {
                    $scope.filterFunction = (cabinets) => {
                        return (cabinets) => { return cabinets };
                    }
                }

                $scope.af = actionFunctionsFactory;
                
            }
        }
    });

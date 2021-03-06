'use strict';
/**
 * @ngdoc function
 * @name IBCDetailCtrl.controller:IBCDetailCtrl
 * @description
 * # IBCDetailCtrl
 * Controller of the IBC protocal detail view
 */
angular.module('ng-IBC')
    .controller('IBCDetailCtrl', function ($rootScope, $scope, $modal, $location, $stateParams, $q) {
    console.log("IBCDetailCtrl running");
    var getProtocol = function () {
        $scope.protocol = new ViewModelHolder();
        $scope.revision = new ViewModelHolder();
        $scope.hasCommentsBySectionId = []; // boolean[] indexed by protocol's section's Key_ids. True if revision has comments for the given section.
        return $q.all([DataStoreManager.getById("IBCProtocol", $stateParams.id, $scope.protocol, [ibc.IBCProtocol.RevisionMap, ibc.IBCProtocol.SectionMap])])
            .then(function (p) {
            var pRevision = $scope.protocol.data.IBCProtocolRevisions[$scope.protocol.data.IBCProtocolRevisions.length - 1];
            $q.all([DataStoreManager.getById("IBCProtocolRevision", pRevision.UID, $scope.revision, true)]).then(function () {
                $scope.protocol.data.IBCSections.forEach(function (section, index) {
                    var commentsSectionMatch = $scope.revision.data.IBCPreliminaryComments.some(function (comment) {
                        return comment.Section_id == section.UID;
                    });
                    if (!commentsSectionMatch) {
                        commentsSectionMatch = $scope.revision.data.IBCPrimaryComments.some(function (comment) {
                            return comment.Section_id == section.UID;
                        });
                    }
                    $scope.hasCommentsBySectionId[section.UID] = commentsSectionMatch;
                });
                console.log("REVISION: ", $scope.revision);
                console.log($scope.hasCommentsBySectionId);
                console.log($scope.protocol);
                console.log(DataStoreManager._actualModel);
            });
        });
    };
    $scope.loading = $rootScope.getCurrentRoles().then(getProtocol);
})
    .controller('IBCDetailModalCtrl', function ($scope, $rootScope, $modalInstance, convenienceMethods, roleBasedFactory) {
    $scope.constants = Constants;
    var rbf = roleBasedFactory;
    $scope.close = function () {
        $modalInstance.dismiss();
    };
});

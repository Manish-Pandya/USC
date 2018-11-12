'use strict';

angular.module('ng-EmailHub')
    .controller('EmailHubQueueCtrl', function($rootScope, $scope, $q, $stateParams){
        console.debug("EmailHubQueueCtrl running");

        // Load emails
        $scope.loadPage = function loadPage( pageNum ){
            var segment = 'getEmails&page=' + pageNum + '&size=' + $scope.Pager.numPerPage;
            $scope.loading = $q.all([
                XHR.GET(segment).then( page => {
                    console.debug("Retrieved queued emails:", page);
                    $scope.QueuedEmailsPage = page;

                    // Update pager info
                    $scope.Pager.currentPage = page.PageNumber;
                    $scope.Pager.numPerPage = page.PageSize;

                    return $scope.QueuedEmails;
                })
            ]);
        }

        $scope.toggleMessageContent = function toggleMessageContent( item ){
            // Close any others
            $scope.QueuedEmailsPage.Results
                .filter(m => m != item)
                .forEach( m => m.ShowBody = false );

            // Open This one
            item.ShowBody = !item.ShowBody
        }

        $scope.Pager = {
            currentPage: 1,
            numPerPage: 20
        };

        $scope.loadPage( $scope.Pager.currentPage );
    });
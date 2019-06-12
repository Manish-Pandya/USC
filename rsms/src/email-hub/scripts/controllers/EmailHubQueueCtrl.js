'use strict';

angular.module('ng-EmailHub')
    .controller('EmailHubQueueCtrl', function($rootScope, $scope, $q, $stateParams){
        console.debug("EmailHubQueueCtrl running");

        // Load Templates (for filtering)
        async function loadFilterData(){
            if( $scope.Templates ){
                return $scope.Templates;
            }

            $scope.loading_filters = XHR.GET('getMessageTemplates').then( templates => {
                console.debug("Retrieved templates:", templates);

                $scope.Templates = templates;

                $scope.MessageTypes = templates.map(t => t.Message_type);

                // Reduce template module names to get Module list
                $scope.Modules = templates
                    .map( t => t.Module )
                    .reduce( (_arr, _name) => {
                        if( _arr.indexOf(_name) < 0 ){
                            _arr.push(_name);
                        }
                        return _arr;
                    }, []);

                return $scope.Templates;
            });

            return $scope.loading_filters;
        }

        // Load emails
        $scope.loadPage = function loadPage( pageNum ){
            var segment = 'getEmails&page=' + pageNum + '&size=' + $scope.Pager.numPerPage;

            // Append filter parameters
            if( $scope.filters.Module ){
                segment += "&module=" + $scope.filters.Module;
            }

            if( $scope.filters.Template ){
                segment += "&template=" + $scope.filters.Template;
            }

            if( $scope.filters.Search ){
                segment += "&search=" + $scope.filters.Search;
            }

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

        $scope.filters = {};

        loadFilterData();
        $scope.loadPage( $scope.Pager.currentPage );
    });
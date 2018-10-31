'use strict';

angular.module('ng-EmailHub')
    .controller('EmailHubTemplateCtrl', function($scope, $stateParams){
        console.debug("EmailHubTemplateCtrl running");

        XHR.GET('getAllMessageTypes')
            .then( mtypes => {
                console.debug("Retrieved message types:", mtypes);
                $scope.MessageTypes = mtypes;
                return mtypes;
            }
        )
        .then( () => XHR.GET('getMessageTemplates')
            .then( templates => {
                console.debug("Retrieved templates:", templates);

                $scope.Templates = templates;
                return templates;
            }
        ))
        .then(() => {
            $scope.$apply();
        });

        // Register functions
        $scope.loadTemplatesOfType = function loadTemplatesOfType( type ){
            return XHR.GET('getMessageTemplates&type=' + type.TypeName)
                .then(templates => {
                    type.Templates = templates;
                    $scope.$apply();
                });
        };

        $scope.getTypeForTemplate = function getTypeForTemplate( template ){
            return $scope.MessageTypes.filter(t => t.TypeName == template.Message_type && t.Module == template.Module)[0];
        };
    });
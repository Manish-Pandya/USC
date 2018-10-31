'use strict';

angular.module('ng-EmailHub')
    .controller('EmailHubTemplateCtrl', function($rootScope, $scope, $q, $stateParams){
        console.debug("EmailHubTemplateCtrl running");

        $scope.loading = $q.all([
            XHR.GET('getAllMessageTypes').then( mtypes => {
                console.debug("Retrieved message types:", mtypes);
                $scope.MessageTypes = mtypes;
                return mtypes;
            }),
            XHR.GET('getMessageTemplates').then( templates => {
                console.debug("Retrieved templates:", templates);

                $scope.Templates = templates;
                return templates;
            })
        ]);

        // Register functions
        $scope.toggleActive = function toggleTemplateActive( template ){
            console.debug("Request toggle status of template ", template);

            $scope.saving = $q.when(XHR.POST('toggleTemplateActive&templateId=' + template.Key_id))
                .then( newStatus => {
                    // Convert Is_active to a boolean; server returns int as string...
                    //  + coerces to int
                    //  !! coerces to boolean
                    template.Is_active = !!+newStatus;
                    console.debug("Template " + template.Key_id + " is active: " + template.Is_active);

                    return template;
                });
        };

        $scope.countTemplatesOfType = function countTemplatesOfType( type, activeState ){
            var count = $scope.Templates.filter(template => {
                return template.Module == type.Module
                    && template.Message_type == type.TypeName
                    && template.Is_active == activeState;
            }).length;

            if( activeState ){
                type.numActiveTemplates = count;
            }

            return count;
        };

        $scope.getTypeForTemplate = function getTypeForTemplate( template ){
            return $scope.MessageTypes.filter(t => t.TypeName == template.Message_type && t.Module == template.Module)[0];
        };

        $scope.createNewTemplate = function createNewTemplate( messageType ){
            if( !messageType ){
                console.warn("No message type selected");
                return false;
            }

            $scope.saving = $q.when(XHR.POST('createNewTemplate', messageType))
                .then(
                    newTemplate => {
                        $scope.Templates.push( newTemplate );
                    },
                    error => {
                        console.error("Error saving new template", error);
                    }
                );
        }
    });
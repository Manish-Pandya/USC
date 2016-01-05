'use strict';

angular
    .module('applicationControllerModule', ['rootApplicationController'])
    .factory('applicationControllerFactory', function applicationControllerFactory(modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods, rootApplicationControllerFactory) {
        var ac = rootApplicationControllerFactory;
        var store = dataStoreManager;
        //give us access to this factory in all views.  Because that's cool.
        store.$q = $q;

        ac.getAllPIs= function()
        {
            return this.getAllUsers()
                .then(
                    function(){
                        return dataSwitchFactory.getAllObjects('PrincipalInvestigator');
                    }
                )

        }

        ac.getAllUsers = function()
        {
            return dataSwitchFactory.getAllObjects('User');
        }

        ac.getAllHazards = function(){
            return dataSwitchFactory.getAllObjects('Hazard');
        }

        ac.getAllProtocols = function(){
            return dataSwitchFactory.getAllObjects('BiosafetyProtocol');
        }

        ac.getAllDepartments = function(){
            return dataSwitchFactory.getAllObjects('Department');
        }

        ac.saveBiosafetyProtocol = function(copy, protocol){
            copy.Approval_date = convenienceMethods.setMysqlTime(ac.getDate(copy.view_Approval_date));
            copy.Expiration_date = convenienceMethods.setMysqlTime(ac.getDate(copy.view_Expiration_date));

            var saveCopy = {
                Class: "BiosafetyProtocol",
                Department_id: copy.Department_id,
                Principal_investigator_id: copy.Principal_investigator_id,
                Expiration_date: copy.Expiration_date,
                Approval_date: copy.Approval_date,
                Project_title: copy.Project_title,
                Protocol_number: copy.Protocol_number,
                Is_active: copy.Is_active,
                Hazard_id: copy.Hazard_id,
                Key_id: copy.Key_id ? copy.Key_id : null
            }

            console.log(saveCopy);
            ac.clearError();
            return this.save(saveCopy)
                    .then(
                        function(returnedProtocol){
                            returnedProtocol = modelInflatorFactory.instateAllObjectsFromJson( returnedProtocol );
                            if(protocol.Key_id){
                                angular.extend(protocol, copy)
                            }else{
                                dataStoreManager.store(returnedProtocol);
                            }
                            return protocol;
                        },
                        ac.setError('The Protocol could not be saved')

                    )
        }

        ac.uploadBiosafteyProtocol = function(file, id){
            var xhr = new XMLHttpRequest;
            var url = '../ajaxaction.php?action=uploadProtocolDocument';
            if(id)url = url + "&id="+id;
            xhr.open('POST', url, true);
            xhr.send(file);
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== XMLHttpRequest.DONE) {
                    return;
                }
                if (xhr.status !== 200) {
                    return;
                }
            }
                console.log(xhr.responseText);
            };
            /*
            this.save(formData, false, segment)
                .then(function(){})
                */

        return ac;
    });

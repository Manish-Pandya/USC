'use strict';

angular
    .module('applicationControllerModule',['rootApplicationController'])
    .factory('applicationControllerFactory', function applicationControllerFactory( modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods, rootApplicationControllerFactory ){
        var ac = rootApplicationControllerFactory;
        var store = dataStoreManager;
        //give us access to this factory in all views.  Because that's cool.
        $rootScope.af = this;


        store.$q = $q;

        ac.setStep = function(int)
        {
            this.step = this.steps[int];
        }

        ac.stepDone = function(int)
        {
           this.steps[int].done = true;
        }

        ac.getVerification = function(id)
        {
            return dataSwitchFactory.getObjectById('Verification', id, true);
        }

        //This is how you write an interface in JavaScript
        //I do hereby swear that there is only ever one verification cached for this particular module, by its nature
        ac.getCachedVerification = function(){
            return dataStore.Verification[0];
        }

        ac.getPI = function(id)
        {
            return dataSwitchFactory.getObjectById('PrincipalInvestigator', id, true);
        }

        ac.createPendingChange = function(thingToBeChanged, verification_id, answer, save)
        {
            if(!thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"]){
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"] = new window["Pending"+thingToBeChanged.Class+"Change"]();
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Parent_id = thingToBeChanged.Key_id;
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].isNew = true;
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Parent_class = thingToBeChanged.Class;
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Verification_id = verification_id;
            }

            if(answer){
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].answer = answer;
                if(answer == "Yes"){
                    thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].New_status = null;
                }
                console.log(thingToBeChanged);
            }

            if(save){
                //save it if we need to
                if(thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Parent_class == "User"){
                    ac.savePendingUserChange(thingToBeChanged.PendingUserChange, thingToBeChanged, id);
                }

                if(thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Parent_class == "Hazard"){
                    ac.savePendingHazardChange(thingToBeChanged.PendingUserChange, thingToBeChanged, id);
                }

                if(thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Parent_class == "Room"){
                    ac.savePendingRoomChange(thingToBeChanged.PendingUserChange, thingToBeChanged, id);
                }
             }



             thingToBeChanged.edit = true;

        }

        ac.savePendingUserChange = function(pendingUserChange, contact, verificationId)
        {
            ac.clearError();
            if(!$rootScope.PendingUserChangeCopy)$rootScope.PendingUserChangeCopy = ac.createCopy(pendingUserChange);

            var copy = $rootScope.PendingUserChangeCopy;
            if(pendingUserChange.New_status_copy)$rootScope.PendingUserChangeCopy.New_status = pendingUserChange.New_status_copy;
            console.log(copy);
            return ac.save( copy )
                .then(
                    function(returnedChange){
                        returnedChange = modelInflatorFactory.instantiateObjectFromJson( returnedChange );
                        if(!pendingUserChange.Key_id){
                            if(pendingUserChange.answer)returnedChange.answer = pendingUserChange.answer;
                            dataStoreManager.addOnSave(returnedChange);
                        }
                        angular.extend(pendingUserChange, returnedChange);
                        if(pendingUserChange.New_status_copy)pendingUserChange.New_status_copy = returnedChange.New_status;
                        contact.edit = false;
                    },
                    function(){
                        ac.setError('The change could not be saved', contact);
                        if(pendingUserChange.New_status_copy)pendingUserChange.New_status_copy = pendingUserChange.New_status;
                    }
                )
        }

        return ac;
    });

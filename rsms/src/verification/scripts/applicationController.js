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
        
        ac.getAllUsers = function()
        {
            return dataSwitchFactory.getAllObjects('User');
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

        ac.savePendingUserChange = function(contact, verificationId)
        {
            ac.clearError();
            var copy = contact.PendingUserChangeCopy;
            copy.Verification_id = ac.getCachedVerification().Key_id;
            console.log(copy);
            return ac.save( copy )
                .then(
                    function(returnedChange){
                        returnedChange = modelInflatorFactory.instantiateObjectFromJson( returnedChange );
                        if(!copy.Key_id){
                            dataStoreManager.addOnSave(returnedChange);
                        }
                        angular.extend(copy, returnedChange);
                        contact.edit = false;
                    },
                    function(){
                        ac.setError('The change could not be saved', contact);
                        copy = null;
                        copy = dataStoreManager.createCopy(contact.PendingUserChange);
                    }
                )

       }

       ac.savePendingRoomChange = function(room, verificationId)
       {
            ac.clearError();
            var copy = room.PendingRoomChangeCopy;
            copy.Verification_id = ac.getCachedVerification().Key_id;
            console.log(room);
            return ac.save( copy )
                .then(
                    function(returnedChange){
                        returnedChange = modelInflatorFactory.instantiateObjectFromJson( returnedChange );
                        if(!copy.Key_id){
                            dataStoreManager.addOnSave(returnedChange);
                        }
                        angular.extend(copy, returnedChange);
                        room.edit = false;
                    },
                    function(){
                        ac.setError('The change could not be saved', contact);
                        copy = null;
                        copy = dataStoreManager.createCopy(room.PendingRoomChange);
                    }
                )

       }


        return ac;
    });

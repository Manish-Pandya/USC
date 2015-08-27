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

        ac.getPI = function(id)
        {
            return dataSwitchFactory.getObjectById('PrincipalInvestigator', id, true);
        }

        ac.createPendingChange = function(thingToBeChanged, id, answer)
        {
            console.log(thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"]);
            if(!thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"]){
                alert('new');
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"] = new window["Pending"+thingToBeChanged.Class+"Change"]();
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Parent_id = thingToBeChanged.Key_id;
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].isNew = true;
                thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Parent_class = thingToBeChanged.Class;
                if(answer)thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].answer = answer;
            }

            if(id){
                if(answer && answer == "Yes"){
                    thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].New_status = null;
                }
                //save it if we need to
                if(thingToBeChanged["Pending"+thingToBeChanged.Class+"Change"].Parent_class == "User"){
                    ac.savePendingUserChange(thingToBeChanged.PendingUserChange, thingToBeChanged, id);
                }
             }

        }
        ac.savePendingUserChange = function(pendingUserChange, contact, verificationId)
        {
            var copy = ac.createCopy(pendingUserChange);
            copy.Parent_class = "User";
            copy.Verification_id = verificationId;
            ac.clearError();
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
                    },
                    ac.setError('The change could not be saved')
                )
        }

        return ac;
    });

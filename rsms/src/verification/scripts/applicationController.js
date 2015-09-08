'use strict';

angular
    .module('applicationControllerModule', ['rootApplicationController'])
    .factory('applicationControllerFactory', function applicationControllerFactory(modelInflatorFactory, genericAPIFactory, $rootScope, $q, dataSwitchFactory, $modal, convenienceMethods, rootApplicationControllerFactory) {
        var ac = rootApplicationControllerFactory;
        var store = dataStoreManager;
        //give us access to this factory in all views.  Because that's cool.
        $rootScope.af = this;

        store.$q = $q;

        ac.setStep = function (int) {
            this.step = this.steps[int];
        }

        ac.stepDone = function (int) {
            this.steps[int].done = true;
        }

        ac.getVerification = function (id) {
            return dataSwitchFactory.getObjectById('Verification', id, true);
        }

        //This is how you write an interface in JavaScript
        //I do hereby swear that there is only ever one verification cached for this particular module, by its nature
        ac.getCachedVerification = function () {
            return dataStore.Verification[0];
        }

        ac.getPI = function (id) {
            return dataSwitchFactory.getObjectById('PrincipalInvestigator', id, true);
        }

        ac.getAllUsers = function () {
            return dataSwitchFactory.getAllObjects('User', null, true);
        }

        ac.getAllBuildings = function () {
            return dataSwitchFactory.getAllObjects('Building', true, true);
        }

        ac.saveVerification = function (verification, step) {
            var copy = verification;
            copy.Step = step;
            console.log(copy);
            return ac.save(copy)
                .then(
                    function (returnedVerification) {
                        returnedVerification = modelInflatorFactory.instantiateObjectFromJson(returnedVerification);
                        angular.extend(copy, returnedVerification);
                        
                        if ($rootScope.selectedView.Label == "Confirmation" && $rootScope.selectedView.Done) {
                            // Show all-done message
                            // TODO: Replace alert with styled modal window.
                            alert("Your Annual Verification is now complete. Thank You.");
                        }
                    },
                    function () {
                        ac.setError('The step could not be saved', contact);
                        copy = null;
                    }
                )

        }

        ac.createPendingChange = function (thingToBeChanged, verification_id, answer, save) {
            if (!thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"]) {
                thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"] = new window["Pending" + thingToBeChanged.Class + "Change"]();
                thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].Parent_id = thingToBeChanged.Key_id;
                thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].isNew = true;
                thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].Parent_class = thingToBeChanged.Class;
                thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].Verification_id = verification_id;
            }

            if (answer) {
                thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].answer = answer;
                if (answer == "Yes") {
                    thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].New_status = null;
                }
                console.log(thingToBeChanged);
            }

            if (save) {
                //save it if we need to
                if (thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].Parent_class == "User") {
                    ac.savePendingUserChange(thingToBeChanged.PendingUserChange, thingToBeChanged, id);
                }

                if (thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].Parent_class == "Hazard") {
                    ac.savePendingHazardChange(thingToBeChanged.PendingUserChange, thingToBeChanged, id);
                }

                if (thingToBeChanged["Pending" + thingToBeChanged.Class + "Change"].Parent_class == "Room") {
                    ac.savePendingRoomChange(thingToBeChanged.PendingUserChange, thingToBeChanged, id);
                }
            }

            thingToBeChanged.edit = true;

        }

        ac.savePendingUserChange = function (contact, verificationId, change) {
            ac.clearError();
            if (contact) {
                var copy = contact.PendingUserChangeCopy;
                if (!copy.Name) copy.Name = contact.Name;
                console.log(copy.Name);
                console.log(contact.Name);
            } else {
                copy = new window.PendingUserChange();
                angular.extend(copy, change);
                copy.Is_active = false;
            }
            copy.Verification_id = ac.getCachedVerification().Key_id;
            return ac.save(copy)
                .then(
                    function (returnedChange) {
                        returnedChange = modelInflatorFactory.instantiateObjectFromJson(returnedChange);
                        if (!copy.Key_id) {
                            dataStoreManager.pushIntoCollection(returnedChange);
                            ac.getCachedVerification().PendingUserChanges.push(dataStoreManager.getById("PendingUserChange", returnedChange.Key_id));
                            if (contact) contact.PendingUserChange = dataStoreManager.getById("PendingUserChange", returnedChange.Key_id);
                        }
                        angular.extend(copy, returnedChange);
                        if (contact) {
                            angular.extend(contact.PendingUserChange, returnedChange)
                            angular.extend(contact.PendingUserChangeCopy, returnedChange)
                            contact.edit = false;
                        } else {
                            angular.extend(change, returnedChange);
                        }
                    },
                    function () {
                        ac.setError('The change could not be saved', contact);
                        copy = null;
                        copy = dataStoreManager.createCopy(contact.PendingUserChange);
                    }
                )

        }

        ac.savePendingRoomChange = function (room, verificationId, building) {
            ac.clearError();
            var copy = room.PendingRoomChangeCopy;
            copy.Verification_id = ac.getCachedVerification().Key_id;
            if (building) copy.Building_name = building.Name;
            if (room.PendingRoomChangeCopy.Answer == "No") room.PendingRoomChangeCopy.New_status = "Removed";

            return ac.save(copy)
                .then(
                    function (returnedChange) {
                        returnedChange = modelInflatorFactory.instantiateObjectFromJson(returnedChange);
                        if (!copy.Key_id) {
                            dataStoreManager.pushIntoCollection(returnedChange);
                            ac.getCachedVerification().PendingRoomChanges.push(dataStoreManager.getById("PendingRoomChange", returnedChange.Key_id));
                            room.PendingRoomChange = dataStoreManager.getById("PendingRoomChange", returnedChange.Key_id);
                        }
                        room.PendingRoomChange.Is_active = returnedChange.Is_active;
                        angular.extend(copy, returnedChange);
                        angular.extend(room.PendingRoomChange, returnedChange)
                        room.edit = false;
                    },
                    function () {
                        ac.setError('The change could not be saved', contact);
                        copy = null;
                        copy = dataStoreManager.createCopy(room.PendingRoomChange);
                    }
                )

        }

        ac.confirmChange = function (change, phone) {
            var copy = dataStoreManager.createCopy(change);
            console.log(change);
            
            var urlFragment = "confirmPending"+change.Parent_class+"Change";
            if(phone)urlFragment += '&phone=true';
            return ac.save(change, false, urlFragment)
                .then(
                    function(returnedChange){
                        returnedChange = modelInflatorFactory.instantiateObjectFromJson(returnedChange);
                        angular.extend(change, returnedChange);
                    },
                    ac.setError('The changed could not be verified.')
                )
        }

        return ac;
    });
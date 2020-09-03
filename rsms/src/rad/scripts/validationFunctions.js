'use strict';
angular.module('radValidationFunctionsModule', [
    'convenienceMethodWithRoleBasedModule'
])
    .factory('pickupsValidationFactory', function pickupsValidationFactory(convenienceMethods){
        var pickupsValidationFactory = {};

        pickupsValidationFactory.validatePickup = function(pickup, containers){
            var validations = [
                pickupsValidationFactory.validatePickupDate(pickup),
                pickupsValidationFactory.validatePickupContainers(containers)
            ];

            return validations.filter(v => !v.isValid).map(v => v.error) || [];
        }

        pickupsValidationFactory.validatePickupContainers = function(containers){
            var validContainers = {
                isValid: true,
                error: null
            }

            // TODO: Validate each container?

            return validContainers;
        }

        pickupsValidationFactory.validatePickupDate = function(pickup){
            var validDate = {
                isValid: true,
                error:  null
            };

            // Require pickup date
            if( !pickup.Pickup_date ){
                validDate.isValid = false;
                validDate.error = "Pickup Date is required.";

                // return early; no need to check the rest
                return validDate;
            }

            // Pickup date cannot be after now
            var now = convenienceMethods.setMysqlTime(new Date());

            if( convenienceMethods.dateIsBefore(now, pickup.Pickup_date) ){
                validDate.error = "The date you entered is in the future.";
                validDate.isValid = false;
            }

            return validDate;
        };

        return pickupsValidationFactory;
    })

    .factory('parcelValidationFactory', function parcelValidationFactory(){
        var parcelValidationFactory = {};

        parcelValidationFactory.getParcelAuthorizationPercentage = function getParcelAuthorizationPercentage(parcel){
            if( !parcel || !parcel.ParcelAuthorizations ){
                return 0;
            }

            return parcel.ParcelAuthorizations
                .map(pauth => pauth.Percentage)
                .reduce( (sum, percentage) => sum += percentage, 0);
        };

        parcelValidationFactory.getParcelAuthorizationQuantity = function getParcelAuthorizationQuantity(parcel, parcelauth){
            return parseFloat(parcel.Quantity) * (parseFloat(parcelauth.Percentage) / 100);
        };

        parcelValidationFactory.validateParcel = function validateParcel( pi, parcel ){
            // TODO: Validate against inventories for PI

            var validations = [
                parcelValidationFactory.validateAtLeastOneParcelAuthorization(parcel),
                parcelValidationFactory.validateParcelAuthorizationIsotopes(parcel),
                parcelValidationFactory.validateParcelAuthorizationsPercentage(parcel),
                parcelValidationFactory.validateQuantityDoesNotExceedAuthorizedAmounts(parcel),
                // TODO: more?
            ];

            return validations.filter(v => !v.isValid).map(v => v.error) || [];
        };

        parcelValidationFactory.validateAtLeastOneParcelAuthorization = function validateAtLeastOneParcelAuthorization( parcel ){
            let validAuths = {
                isValid: true,
                error: null
            };

            // Require one auth
            if( !parcel.ParcelAuthorizations || !parcel.ParcelAuthorizations.length ){
                validAuths.isValid = false;
                validAuths.error = "One Authorization is required.";
            }

            return validAuths;
        }

        parcelValidationFactory.validateParcelAuthorizationIsotopes = function validateParcelAuthorizationIsotopes( parcel ){
            let validAuths = {
                isValid: true,
                error: null
            };

            // Require valid selections
            if( parcel.ParcelAuthorizations.filter(a => !a.Authorization_id).length ){
                validAuths.isValid = false;
                validAuths.error = "All Authorizations require a selection.";
            }

            return validAuths;
        }

        parcelValidationFactory.validateParcelAuthorizationsPercentage = function validateParcelAuthorizationsPercentage( parcel ){
            let validAuths = {
                isValid: true,
                error: null
            };

            // Ensure percentages are 100
            if( 100 != parcelValidationFactory.getParcelAuthorizationPercentage(parcel) ){
                validAuths.isValid = false;
                validAuths.error = "Percentage of nuclides must equal 100%.";
            }

            return validAuths;
        };

        parcelValidationFactory.validateQuantityDoesNotExceedAuthorizedAmounts = function validateQuantityDoesNotExceedAuthorizedAmounts(parcel){
            let validQuantities = {
                isValid: true,
                error: null
            };

            for( let i = 0; i < parcel.ParcelAuthorizations.length; i++ ){
                let auth = parcel.ParcelAuthorizations[i];

                // Validate that this ParcelAuthorization references its Authorization
                if( !auth.Authorization ){
                    console.warn("ParcelAuth must reference its Authorization to calculate quantity");
                    continue;
                }

                // Calculate quantity of this material
                let authQuantity = parcelValidationFactory.getParcelAuthorizationQuantity(parcel, auth);
                if( authQuantity > parseFloat(auth.Authorization.Max_quantity) ){
                    validQuantities.isValid = false;
                    validQuantities.error = "Quantity must not exceed authorized amounts of Authorization(s)";
                    return validQuantities;
                }
            }

            return validQuantities;
        }

        return parcelValidationFactory;
    })

    .factory('parcelUseValidationFactory', function parcelUseValidationFactory($rootScope, $modal, convenienceMethods, roleBasedFactory){
        var parcelUseValidationFactory = {};

        parcelUseValidationFactory.getAvailableQuantityForUseValidation = function(parcel, use){
            // Cast to number, as this may be a string...
            var amount = (+parcel.Remainder);
            if( use.Is_active && use.Key_id > 0){
                // Cast to number, as this may be a string...
                amount += (+use.Quantity);

                console.debug("Make calculations by first subtracting this use from Remainder.");
            }

            console.debug("Usable activity is ", amount);
            return amount;
        };

        parcelUseValidationFactory.validateUseLogEntry = function(parcel, use, originalUse){
            // Validate entered Date
            var validDate = parcelUseValidationFactory.validateUsageDate(use, parcel);

            // Validate total amount
            var validTotal = parcelUseValidationFactory.validateUsageTotalAmount(use, parcel, originalUse);

            // Validate usage amounts
            var validUsages = parcelUseValidationFactory.validateUseAmounts(use);

            if( validDate.isValid && validTotal.isValid && validUsages.isValid ){
                // All is OK
                return true;
            }
            else{
                console.debug("validDate:", validDate);
                console.debug("validTotal:", validTotal);
                console.debug("validUsages:", validUsages);

                // Mark the use as invalid and copy the error message(s)
                // TODO: Return an error object instead of embedding into the use
                use.isValid = false;
                use.DateError = validDate.error;
                use.TotalError = validTotal.error;
                use.error = validUsages.error;
            }

            return false;
        };

        parcelUseValidationFactory.validateUsageTotalAmount = function(use, parcel, originalUse){
            var validTotal = {
                isValid: false,
                error:  null
            };

            var availableQuantity = parcelUseValidationFactory.getAvailableQuantityForUseValidation(parcel, originalUse || use);

            if( !use.Quantity ){
                validTotal.error = "Amount is required.";
            }
            else if( use.Quantity > availableQuantity){
                validTotal.error = "Amount cannot exceed Usable Activity.";
            }
            else if( use.Quantity == 0){
                validTotal.error = "Amount cannot be zero.";
            }
            else if(use.Quantity < 0){
                validTotal.error = "Amount must be positive.";
            }
            else{
                validTotal.isValid = true;
            }

            return validTotal;
        };

        //this is here specifically because form validation seems like it belongs in the controller (VM) layer rather than the CONTROLLER(actionFunctions layer) of this application,
        //which if you think about it, has sort of become an MVCVM
        parcelUseValidationFactory.validateUseAmounts = function (use) {
            var validUsages = {
                isValid: true,
                error:  null
            };

            // Validate raw values
            var total = 0;
            use.ParcelUseAmounts.forEach(amt => {
                // validate value as a number
                var value = parseFloat(amt.Curie_level);

                // is number
                if( isNaN(value) ){
                    // Not a number...
                    validUsages.isValid = false;
                    validUsages.error = "Values must be numeric.";
                }
                else if( value < 0 ){
                    // Zero or negative
                    validUsages.isValid = false;
                    validUsages.error = "Values must be positive.";
                }
                else if( value > parseFloat(use.Quantity)){
                    validUsages.isValid = false;
                    validUsages.error = "Values cannot exceed the usage Amount.";
                }
                else{
                    total += value;
                }

                // validate that waste container is selected (by verifying selected waste type)
                var wasteTypeId = parseInt( amt.Waste_type_id );
                if( wasteTypeId < 1 ){
                    validUsages.isValid = false;
                    validUsages.error = "All Disposals must specify Container";
                }
            });

            // Validate total if each usage is individually valid
            if( validUsages.isValid ){
                total = Math.round(total * 100000) / 100000;
                if (parseFloat(use.Quantity) == total) {
                    validUsages.isValid = true;
                }
                else {
                    validUsages.error = 'Total disposal amount must equal use amount.';
                }
            }

            return validUsages;
        };

        
        parcelUseValidationFactory.validateUsageDate = function (use, parcel) {
            var validDate = {
                isValid: true,
                error:  null
            };

            if( !use.view_Date_used ){
                validDate.isValid = false;
                validDate.error = "Usage date is required";
                return validDate;
            }

            // Convert arrival, transfer, usage timestamps to dates
            var arrivalDate = convenienceMethods.getDateString(parcel.Arrival_date).formattedString;

            // Transfer may not be present
            var transferDate = null;
            if( parcel.Transfer_in_date ){
                transferDate = convenienceMethods.getDateString(parcel.Transfer_in_date).formattedString;
            }

            var usageDateString = convenienceMethods.setMysqlTime(use.view_Date_used);
            var usageDate = convenienceMethods.getDateString(usageDateString).formattedString;

            var today = convenienceMethods.getDateString(
                convenienceMethods.setMysqlTime(new Date())).formattedString;

            if( convenienceMethods.dateIsBefore(usageDate, arrivalDate) || convenienceMethods.dateIsBefore(usageDate, transferDate)){
                validDate.error = "The date you entered is before this package arrived.<br>";
                validDate.isValid = false;
            }

            // Verify usage date is not after today
            else if(convenienceMethods.dateIsBefore(today, usageDate)){
                validDate.error = "The date you entered is after today.<br>";
                validDate.isValid = false;
            }

            // RSMS-714: No longer require validation against most-recent picked-up Pickup

            return validDate;
        }

        return parcelUseValidationFactory;
    });

angular.module('ng-IBC')
    .filter('isNotSubmitted', function () {
        return function (protocols) {
            if (!protocols) return;
            unsubmittedProtocols = protocols.filter(function (p) {
                return !p.Date_submitted;
            })
            return unsubmittedProtocols;
        };
    })
	.filter('isSubmitted', function () {
	    return function (protocols) {
	        if (!protocols) return;	        
	        submittedProtocols = protocols.filter(function (p) {
	            return p.Date_submitted && !p.Date_approved;
	        })
	        return submittedProtocols;
	    };
	})
    .filter('isReturned', function () {
        return function (protocols) {
            if (!protocols) return;
            returnedProtocols = protocols.filter(function (p) {
                return p.Returned_for_review && !p.Date_approved;
            })
            return returnedProtocols;
        };
    })
    .filter('isApproved', function () {
        return function (protocols) {
            if (!protocols) return;
            approvedProtocols = protocols.filter(function (p) {
                return p.Date_approved;
            })
            return approvedProtocols;
        };
    })
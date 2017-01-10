angular.module('ng-IBC')
    .filter('isNotSubmitted', function () {
    return function (protocols) {
        if (!protocols)
            return;
        var unsubmittedProtocols = protocols.filter(function (p) {
            return p.IBCProtocolRevisions[p.IBCProtocolRevisions.length - 1].Status == Constants.IBC_PROTOCOL_REVISION.STATUS.NOT_SUBMITTED;
        });
        return unsubmittedProtocols;
    };
})
    .filter('isSubmitted', function () {
    return function (protocols) {
        if (!protocols)
            return;
        var submittedProtocols = protocols.filter(function (p) {
            return p.IBCProtocolRevisions[p.IBCProtocolRevisions.length - 1].Status == Constants.IBC_PROTOCOL_REVISION.STATUS.SUBMITTED;
        });
        return submittedProtocols;
    };
})
    .filter('isReturned', function () {
    return function (protocols) {
        if (!protocols)
            return;
        var returnedProtocols = protocols.filter(function (p) {
            return p.IBCProtocolRevisions[p.IBCProtocolRevisions.length - 1].Status == Constants.IBC_PROTOCOL_REVISION.STATUS.RETURNED_FOR_REVISION;
        });
        return returnedProtocols;
    };
})
    .filter('isApproved', function () {
    return function (protocols) {
        if (!protocols)
            return;
        var approvedProtocols = protocols.filter(function (p) {
            return p.IBCProtocolRevisions[p.IBCProtocolRevisions.length - 1].Status == Constants.IBC_PROTOCOL_REVISION.STATUS.APPROVED;
        });
        return approvedProtocols;
    };
});

angular.module('ng-IBC')
    .filter('isNotSubmitted', function () {
    return function (protocols) {
        if (!protocols)
            return;
        return protocols.filter(function (p) {
            return p.IBCProtocolRevisions[p.IBCProtocolRevisions.length - 1].Status == Constants.IBC_PROTOCOL_REVISION.STATUS.NOT_SUBMITTED;
        });
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
    .filter('isInReview', function () {
    return function (protocols) {
        if (!protocols)
            return;
        var returnedProtocols = protocols.filter(function (p) {
            return p.IBCProtocolRevisions[p.IBCProtocolRevisions.length - 1].Status == Constants.IBC_PROTOCOL_REVISION.STATUS.IN_REVIEW;
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
})
    .filter('isIBCMember', function () {
    return function (users) {
        if (!users)
            return;
        var approvedUsers = users.filter(function (u) {
            var hasCorrectRole = false;
            u.Roles.forEach(function (value, index, array) {
                if (value.Name == Constants.ROLE.NAME.IBC_MEMBER || value.Name == Constants.ROLE.NAME.IBC_CHAIR) {
                    hasCorrectRole = true;
                }
            });
            return hasCorrectRole;
        });
        return approvedUsers;
    };
})
    .filter('getMostRecentRevision', function () {
    return function (revisions) {
        if (!revisions || !revisions.length)
            return;
        return revisions.sort(function (a, b) { return a.Revision_number > b.Revision_number; })[0];
    };
})
    .filter("piSelected", function () {
    return function (pis, selectedPis) {
        if (!pis || !selectedPis)
            return;
        return pis.filter(function (pi) {
            return _.findIndex(selectedPis, function (p) { return pi.UID == p.UID; }) == -1;
        });
    };
});

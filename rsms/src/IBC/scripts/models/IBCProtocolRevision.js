var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var ibc;
(function (ibc) {
    var IBCProtocolRevision = (function (_super) {
        __extends(IBCProtocolRevision, _super);
        function IBCProtocolRevision() {
            return _super.call(this) || this;
        }
        IBCProtocolRevision.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return IBCProtocolRevision;
    }(FluxCompositerBase));
    IBCProtocolRevision.urlMapping = new UrlMapping("getAllProtocolRevisions", "getProtocolRevisionById&id=", "saveProtocolRevision");
    IBCProtocolRevision.PrimaryReviewersMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getAllUsers", "PimraryReviewers", "Revisions_id", "Reviewer_id", "IBCRevisionPrimaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRIMARY_REVIEWERS_RELATIONSHIP");
    IBCProtocolRevision.PreliminaryReviewersMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getAllUsers", "PreliminaryReviewers", "Revisions_id", "Reviewer_id", "IBCRevisionPreliminaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRELIMINARY_REVIEWERS_RELATIONSHIP");
    ibc.IBCProtocolRevision = IBCProtocolRevision;
})(ibc || (ibc = {}));

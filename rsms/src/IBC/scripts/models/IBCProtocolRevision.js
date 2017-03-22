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
            var _this = _super.call(this) || this;
            _this.responsesMapped = {};
            return _this;
        }
        IBCProtocolRevision.prototype.getResponsesMapped = function () {
            if (this.IBCResponses) {
                for (var n = 0; n < this.IBCResponses.length; n++) {
                    var response = this.IBCResponses[n];
                    console.log(response);
                    if (!this.responsesMapped[response.Answer_id])
                        this.responsesMapped[response.Answer_id] = [];
                    this.responsesMapped[response.Answer_id].push(response);
                }
            }
            return this.responsesMapped;
        };
        IBCProtocolRevision.prototype.onFulfill = function () {
            _super.prototype.onFulfill.call(this);
            this.getResponsesMapped();
        };
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
    IBCProtocolRevision.PrimaryReviewersMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getPropertyByName&id={{this.UID}}&property=PrimaryReviewers&type=IBCProtocolRevision", "PrimaryReviewers", "Revision_id", "Reviewer_id", "IBCRevisionPrimaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRIMARY_REVIEWERS_RELATIONSHIP");
    IBCProtocolRevision.PreliminaryReviewersMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "User", "getPropertyByName&id={{this.UID}}&property=PreliminaryReviewers&type=IBCProtocolRevision", "PreliminaryReviewers", "Revision_id", "Reviewer_id", "IBCRevisionPreliminaryReviewer", "getRelationships&class1=IBCProtocolRevision&class2=User&override=PRELIMINARY_REVIEWERS_RELATIONSHIP");
    IBCProtocolRevision.IBCReponseMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "IBCResponse", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=IBCResponses&id={{UID}}", "IBCResponses", "Revision_id");
    ibc.IBCProtocolRevision = IBCProtocolRevision;
})(ibc || (ibc = {}));

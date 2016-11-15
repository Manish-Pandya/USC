var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var IBCQuestion = (function (_super) {
    __extends(IBCQuestion, _super);
    function IBCQuestion() {
        _super.call(this);
    }
    IBCQuestion.prototype.hasGetAllPermission = function () {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            _super.prototype.hasGetAllPermission.call(this, _.intersection(currentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    };
    IBCQuestion.urlMapping = new UrlMapping("getAllQuestions", "getQuestionById&id=", "saveQuestion");
    return IBCQuestion;
}(FluxCompositerBase));

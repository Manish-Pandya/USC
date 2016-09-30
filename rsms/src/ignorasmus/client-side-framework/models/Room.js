var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var Room = (function (_super) {
    __extends(Room, _super);
    function Room() {
        _super.call(this);
    }
    Room.prototype.onFulfill = function (callback) {
        if (callback === void 0) { callback = null; }
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        this.hasGetAllPermission();
        // build compositionMapping
        this.PIMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getAllPIs", "PrincipalInvestigators", "Room_id", "Principal_investigator_id", "RoomPrincipalInvestigator", "getRelationships&class1=Room&class2=PrincipalInvestigator");
        return _super.prototype.onFulfill.apply(this, [callback].concat(args));
    };
    Room.prototype.hasGetAllPermission = function () {
        if (this._hasGetAllPermission == null) {
            var allowedRoles = [Constants.ROLE.NAME.ADMIN];
            _super.prototype.hasGetAllPermission.call(this, _.intersection(currentRoles, allowedRoles).length > 0);
        }
        return this._hasGetAllPermission;
    };
    Room.urlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");
    return Room;
}(FluxCompositerBase));

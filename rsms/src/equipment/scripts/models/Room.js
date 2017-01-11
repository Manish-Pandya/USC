var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var equipment;
(function (equipment) {
    var Room = (function (_super) {
        __extends(Room, _super);
        //static PIMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=PrincipalInvestigators&id={{UID}}", "PrincipalInvestigators", "Principal_investigator_id", "Room_id", "RoomPrincipalInvestigator", "getRelationships&class1=Room&class2=PrincipalInvestigator");
        function Room() {
            return _super.call(this) || this;
        }
        Room.prototype.hasGetAllPermission = function () {
            if (this._hasGetAllPermission == null) {
                var allowedRoles = [Constants.ROLE.NAME.SAFETY_INSPECTOR, Constants.ROLE.NAME.ADMIN];
                _super.prototype.hasGetAllPermission.call(this, _.intersection(DataStoreManager.CurrentRoles, allowedRoles).length > 0);
            }
            return this._hasGetAllPermission;
        };
        return Room;
    }(FluxCompositerBase));
    Room.urlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");
    equipment.Room = Room;
})(equipment || (equipment = {}));

var __extends = (this && this.__extends) || (function () {
    var extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
var ibc;
(function (ibc) {
    var Room = (function (_super) {
        __extends(Room, _super);
        //static PIMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getAllPIs", "PrincipalInvestigators", "Room_id", "Principal_investigator_id", "RoomPrincipalInvestigator", "getRelationships&class1=Room&class2=PrincipalInvestigator");
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
        Room.urlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");
        return Room;
    }(FluxCompositerBase));
    ibc.Room = Room;
})(ibc || (ibc = {}));

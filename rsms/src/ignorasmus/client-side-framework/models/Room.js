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
    Room.urlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");
    Room.PIMap = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getAllPIs", "PrincipalInvestigators", "Room_id", "Principal_investigator_id", "RoomPrincipalInvestigator", "getRelationships&class1=Room&class2=PrincipalInvestigator");
    return Room;
}(FluxCompositerBase));

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
var equipment;
(function (equipment) {
    var Building = (function (_super) {
        __extends(Building, _super);
        function Building() {
            return _super.call(this) || this;
        }
        Building.prototype.hasGetAllPermission = function () {
            //list of buildings is public info and can be gotten by anybody;
            return true;
        };
        Building.urlMapping = new UrlMapping("getAllBuildings", "getBuildingById&id=", "saveBuilding");
        Building.RoomMap = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "Room", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=Rooms&id={{UID}}", "Rooms", "Building_id");
        return Building;
    }(FluxCompositerBase));
    equipment.Building = Building;
})(equipment || (equipment = {}));

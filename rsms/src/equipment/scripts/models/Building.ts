namespace equipment {
    export class Building extends FluxCompositerBase {

        static urlMapping: UrlMapping = new UrlMapping("getAllBuildings", "getBuildingById&id=", "saveBuilding");

        constructor() {
            super();
        }

        Rooms: equipment.Room[];
        static RoomMap: CompositionMapping = new CompositionMapping(CompositionMapping.ONE_TO_MANY, "Room", "getPropertyByName&type={{DataStoreManager.classPropName}}&property=Rooms&id={{UID}}", "Rooms", "Building_id");

        hasGetAllPermission(): boolean {
            //list of buildings is public info and can be gotten by anybody;
            return true;
        }
    }
}
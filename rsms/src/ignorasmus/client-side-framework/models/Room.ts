class Room extends FluxCompositerBase {

    static urlMapping = new UrlMapping("getAllRooms", "getRoomById&id=", "saveRoom");

    PrincipalInvestigators: PrincipalInvestigator[];
    static PIMap: CompositionMapping = new CompositionMapping(CompositionMapping.MANY_TO_MANY, "PrincipalInvestigator", "getAllPIs", "PrincipalInvestigators", "Room_id", "Principal_investigator_id", "RoomPrincipalInvestigator", "getRelationships&class1=Room&class2=PrincipalInvestigator");

    constructor() {
        super();
    }
}
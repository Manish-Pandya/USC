<?php
class LocationResourceTransformer {
    public $include_detail = false;

    public function __invoke( $resource ){
        $detail = $this->include_detail;

        switch( get_class($resource) ){
            case PrincipalInvestigator::class:
                return $this->transform_principalinvestigator($resource, $detail);
            case Room::class:
                return $this->transform_room($resource, $detail);
            case Building::class:
                return $this->transform_building($resource, $detail);
            case Campus::class:
                return $this->transform_campus($resource, $detail);
            default:
                return null;
        }
    }

    private function describeClass( $obj, bool $detail){
        return get_class($obj) . ($detail ? 'Detail' : 'Info');
    }

    public function transform_principalinvestigator( PrincipalInvestigator $pi, bool $detail ){
        // Merge PI and their User

        $data = [
            'class' => (string) $this->describeClass($pi, $detail),
            'key_id' => (int) $pi->getKey_id(),
        ];

        $user = $pi->getUser();
        if( isset($user) ){
            $user_data = [
                'username' => (string) $user->getUsername(),
                'first_name' => (string) $user->getFirst_name(),
                'last_name' => (string) $user->getLast_name(),
                'email' => (string) $user->getEmail()
            ];

            $data = array_merge( $data, $user_data );
        }
        else {
            LogUtil::get_logger(__CLASS__, __FUNCTION__)->warn("$pi has no User data");
        }

        if( $detail === true ){
            // Map locations
            $locations = [];
            foreach($pi->getRooms() as $room){
                $locations[] = new GenericDto([
                    'room' => $this->transform_room($room, false),
                    'building' => $this->transform_building($room->getBuilding(), false),
                    'campus' => $this->transform_campus($room->getBuilding()->getCampus(), false),
                ]);
            }

            $data['locations'] = $locations;
        }

        return new GenericDto($data);
    }

    public function transform_room( Room $room, bool $detail ){
        $data = [
            'class' => (string) $this->describeClass($room, $detail),
            'key_id' => (int) $room->getKey_id(),
            'name' => (string) $room->getName(),
            'building_id' => (int) $room->getBuilding_id()
        ];

        if( $detail === true ){
            $pis = [];
            foreach($room->getPrincipalInvestigators() as $pi){
                // never include detailed PIs from here
                $pis[] = $this->transform_principalinvestigator($pi, false);
            }

            $data['principal_investigators'] = $pis;
        }

        return new GenericDto($data);
    }

    public function transform_building( Building $building, bool $detail ){
        $data = [
            'class' => (string) $this->describeClass($building, $detail),
            'key_id' => (int) $building->getKey_id(),
            'name' => (string) $building->getName(),
            'alias' => (string) $building->getAlias(),
            'physical_address' => (string) $building->getPhysical_address(),
            'campus_id' => (int) $building->getCampus_id()
        ];

        if( $detail === true ){
            $rooms = [];
            foreach($building->getRooms() as $room){
                $rooms[] = $this->transform_room($room, true);
            }

            $data['rooms'] = $rooms;
        }

        return new GenericDto($data);
    }

    public function transform_campus( Campus $campus, bool $detail ){
        $data = [
            'class' => (string) $this->describeClass($campus, $detail),
            'key_id' => (int) $campus->getKey_id(),
            'name' => (string) $campus->getName()
        ];

        if( $detail === true ){
            $buildings = [];
            foreach($campus->getBuildings() as $building){
                $buildings[] = $this->transform_building($building, true);
            }

            $data['buildings'] = $buildings;
        }

        return new GenericDto($data);
    }
}
?>

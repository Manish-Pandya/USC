<?php

class LocationApiService {
    
    private static function _get_resource_class( string &$resource ){
        switch( strtolower($resource) ){
            case 'pi':  // alias for principal investigator
            case 'principalinvestigator':
                return PrincipalInvestigator::class;
            case 'room': return Room::class;
            case 'building': return Building::class;
            case 'campus': return Campus::class;
            default:
                throw new InvalidApiPathException("Invalid resource '$resource'");
        }
    }

    public function __construct(){
        $this->campus_dao = new GenericDAO(new Campus());
        $this->building_dao = new GenericDAO(new Building());
        $this->room_dao = new RoomDAO();
        $this->pi_dao = new PrincipalInvestigatorDAO();
        $this->user_dao = new UserDAO();
        $this->transformer = new LocationResourceTransformer();
    }

    private function get_resource_dao( string &$resource ){
        switch( self::_get_resource_class($resource) ){
            case PrincipalInvestigator::class:
                return new PrincipalInvestigatorDAO();
            case Room::class:
                return new RoomDAO();
            default:
                return new GenericDAO(new $resource());
        }
    }

    private function transform( $resourceOrResources, bool $detail ){
        $this->transformer->include_detail = ($detail === true);

        if( is_array($resourceOrResources) ){
            return array_map( $this->transformer, $resourceOrResources );
        }
        else {
            return $this->transformer->__invoke($resourceOrResources);
        }
    }

    public function getAll( string $resource ){
        $dao = $this->get_resource_dao( $resource );

        // Get only Active items
        $results = $dao->getAll( null, false, true );

        // Special filter for PIs
        //   Ignore any which are not linked to their User
        if( self::_get_resource_class($resource) == PrincipalInvestigator::class ){
            $count_before = count($results);
            $results = array_filter($results, function($pi){
                return $pi->getUser() != NULL;
            });

            if( count($results) != $count_before ){
                LogUtil::get_logger(__CLASS__, __FUNCTION__)->warn("Filtered PrincipalInvestigator item(s) which have no User");
            }
        }

        return $this->transform($results, false);
    }

    public function search( string $resource ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
        $class = self::_get_resource_class($resource);

        // FIXME: This currently only supports searching PIs by user.username
        // TODO: Support additional searches by dynamically building queries
        if( $class !== PrincipalInvestigator::class ){
            $LOG->warn("Search requested for unsupported resource: $class");
            throw new InvalidApiPathException("Unsupported operation");
        }

        if( !isset( $_REQUEST['username']) ){
            $LOG->warn("'username' parameter missing or empty");
            throw new ResourceNotFoundException("Resource not found");
        }

        $username = $_REQUEST['username'];
        $user = $this->user_dao->getUserByUsername($username);

        if( !isset($user) || !$user || $user instanceof ActionError ){
            $LOG->warn("User with username='$username' does not exist");
            throw new ResourceNotFoundException("Resource not found");
        }

        // PI may be the user's supervisor, or the PI
        $pi = null;
        if( $user->hasSupervisor() ){
            $pi = $user->getSupervisor();
        }
        else {
            $pi = $user->getPrincipalInvestigator();
        }

        if( !isset($pi) || $pi instanceof ActionError ){
            $LOG->warn("$user is not mapped to a PrincipalInvestigator");
            throw new ResourceNotFoundException("Resource not found");
        }

        return $this->transform($pi, false);
    }

    public function getInfo( string $resource, int $id ){
        $dao = $this->get_resource_dao( $resource );

        $result = $dao->getById($id);

        if( !isset($result) || $result instanceof ActionError ){
            throw new ResourceNotFoundException("$resource #$id not found");
        }

        return $this->transform($result, false);
    }

    public function getDetail( string $resource, int $id ){
        $dao = $this->get_resource_dao( $resource );

        $result = $dao->getById($id);

        if( !isset($result) || $result instanceof ActionError ){
            throw new ResourceNotFoundException("$resource #$id not found");
        }

        return $this->transform($result, true);
    }
}
?>

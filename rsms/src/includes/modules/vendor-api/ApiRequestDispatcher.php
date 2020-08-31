<?php
class ApiRequestDispatcher extends ActionDispatcher {

    public function parse_api_request( string $endpoint, Array &$request_parameters){
        // Split up action name to extract path parameters
        /*
            /{resource}
            /{resource}/search
            /{resource}/{id}
            /{resource}/{id}/detail
        */

        // Trim leading slash
        if( $endpoint[0] == '/' ){
            $endpoint = substr($endpoint, 1);
        }

        // Split parts
        $endpointPath = explode( '/', $endpoint );
        $parts_count = count($endpointPath);

        // Ensure no empty parts
        if( in_array('', $endpointPath) ){
            throw new InvalidApiPathException('Invalid request path');
        }

        // Identify path
        $resource = $endpointPath[0];
        $search = false;
        $id = null;
        $detail = false;

        // Handle search or single-entry GET
        if( $parts_count > 1){
            if( $endpointPath[1] === 'search' ){
                $search = true;
            }
            else if( is_numeric($endpointPath[1])) {
                // If this isn't a search request, it's a by-id request
                $id = (int) $endpointPath[1];
            }
            else {
                throw new InvalidApiPathException("Invalid path '/$endpoint'");
            }
        }

        // Handle detail
        if( $parts_count > 2 ){
            $detail = $endpointPath[2] == 'detail';
        }
        /////////

        // Add path parameters to request params
        $request_parameters['resource'] = $resource;
        if( isset($id) ) {
            Logger::getLogger(__CLASS__)->debug("Injecting id='$id' into request parameter array");
            $request_parameters['id'] = $id;
        }

        // Determine function name
        $name = "getAll";
        if( $search === true ){
            $name = "search";
        }
        else if( $detail === true ){
            $name = "getDetail";
        }
        else if ( $id != null ){
            $name = "getInfo";
        }

        return $name;
    }
}
?>

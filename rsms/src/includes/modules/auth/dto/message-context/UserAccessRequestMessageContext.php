<?php

class UserAccessRequestMessageContext implements MessageContext {
    public $request_id;

    public function __construct( $id = NULL ){
        $this->request_id = $id;
    }

    public function getRequest_id(){ return $this->request_id; }
    public function setRequest_id($id){ $this->request_id = $id; }
}
?>

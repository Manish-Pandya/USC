<?php

class SavedPickupDetailsDto {

    private $pickup;
    private $containers;

    public function __construct($pickup, $containers){
        $this->pickup = $pickup;
        $this->containers = $containers;
    }

    public function getPickup(){
        return $this->pickup;
    }

    public function getContainers(){
        return $this->containers;
    }
}
?>
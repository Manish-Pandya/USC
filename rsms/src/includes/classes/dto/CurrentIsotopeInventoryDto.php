<?php

/**
 * Inventory of given isotope for an authorization
 *
 * @version 1.0
 * @author Matt Breeden
 */
class CurrentIsotopeInventoryDto
{
    private $principal_investigator_id;
    private $isotope_id;
    private $authorization_id;
    private $ordered;
    private $isotope_name;
    private $authorized_form;
    private $amount_picked_up;
    private $amount_on_hand;
    private $amount_disposed;
    private $amount_transferred;
    private $usable_amount;
    private $auth_limit;
    private $max_order;

    public function getPrincipal_investigator_id(){
		return $this->principal_investigator_id;
	}

	public function setPrincipal_investigator_id($principal_investigator_id){
		$this->principal_investigator_id = $principal_investigator_id;
	}

	public function getIsotope_id(){
		return $this->isotope_id;
	}

	public function setIsotope_id($isotope_id){
		$this->isotope_id = $isotope_id;
	}

	public function getAuthorization_id(){
		return $this->authorization_id;
	}

	public function setAuthorization_id($authorization_id){
		$this->authorization_id = $authorization_id;
	}

	public function getOrdered(){
		return (float) $this->ordered;
	}

	public function setOrdered($ordered){
		$this->ordered = $ordered;
	}

	public function getIsotope_name(){
		return $this->isotope_name;
	}

	public function setIsotope_name($isotope_name){
		$this->isotope_name = $isotope_name;
	}

	public function getAuthorized_form(){
		return $this->authorized_form;
	}

	public function setAuthorized_form($authorized_form){
		$this->authorized_form = $authorized_form;
	}

	public function getAmount_picked_up(){
		return (float) $this->amount_picked_up;
	}

	public function setAmount_picked_up($amount_picked_up){
		$this->amount_picked_up = $amount_picked_up;
	}

	public function getAmount_on_hand(){
        $this->amount_on_hand = $this->getOrdered()-$this->getAmount_picked_up()-$this->getAmount_transferred();
		return (float) $this->amount_on_hand;
	}

	public function setAmount_on_hand($amount_on_hand){
		$this->amount_on_hand = $amount_on_hand;
	}

	public function getAmount_disposed(){
		return (float) $this->amount_disposed;
	}

	public function setAmount_disposed($amount_disposed){
		$this->amount_disposed = $amount_disposed;
	}

	public function getUsable_amount(){
        $this->usable_amount = $this->getOrdered() - $this->getAmount_disposed() - $this->getAmount_transferred();
		return (float) $this->usable_amount;
	}

	public function setUsable_amount($usable_amount){
		$this->usable_amount = $usable_amount;
	}

    public function getAuth_limit(){
		return (float) $this->auth_limit;
	}

	public function setAuth_limit($auth_limit){
		$this->auth_limit = $auth_limit;
	}

    public function getMax_order(){
        $this->max_order = $this->auth_limit - $this->amount_on_hand;
		return (float) $this->max_order;
	}

	public function setMax_order($maxOrder){
		$this->max_order = $maxOrder;
	}

    public function getAmount_transferred(){
		return $this->amount_transferred;
	}

	public function setAmount_transferred($amount_transferred){
		$this->amount_transferred = $amount_transferred;
	}
}
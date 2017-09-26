<?php

/**
 * EmailGen short summary.
 *
 * EmailGen description.
 *
 * @version 1.0
 * @author intoxopox
 */

class IBCEmailGen extends EmailGen {

	/** Name of the module this email gen is for */
	public static $MODULE_NAME = "IBC";

	private $revision;
	private $protocol;

	public function __construct(IBCProtocolRevision $revision = null) {

		parent::__construct($revision);
		$this->revision = $revision;
		$this->module = self::$MODULE_NAME;
	}

	public function swapFish() {
		return "Flynn the really really fun Fish";
	}
	/*
	 * @return IBCProtocol
	 */
	protected function getProtocol(){
		if($this->protocol == null && $this->revision && $this->revision->getProtocol_id() != null){
			$protocolDao = new GenericDAO(new IBCProtocol());
			$this->protocol = $protocolDao->getById($this->revision->getProtocol_id());
		}
		return $this->protocol;
	}

	protected function getPis(){
		if($this->pis == null && $this->getProtocol() != null){
			$this->pis = $this->protocol->getPrincipalInvestigators();
		}
		return $this->pis;
	}

	/** Macro key/value replacement map */
	public function macroMap() {
		return array(
			"[PI]"							=>	"PI Name",
			"[Protocol Title]"				=>	"Protocol Title",
			"[Protocol Number]"				=>	"Protocol Number",
			"[Protocol Approval Date]"		=>	"Protocol Approval Date",
			"[Expiration Date]"				=>	"Expiration Date",
			"[Reference Number]"			=>	"Reference Number",
			"[Review Assignment Name]"		=>	"Review Assignment Name",
			"[Review Assignment Due Date]"	=>	"Review Assignment Due Date",
			"[Meeting Date]"				=>	"Meeting Date",
			"[Location]"					=>	"Location"
		);
	}

}
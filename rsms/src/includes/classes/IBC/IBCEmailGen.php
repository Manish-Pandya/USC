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

	/**
	 * Summary of $protocol
	 * @var IBCProtocol
	 */
	private $protocol;

	/**
	 * Summary of $revision
	 * @var IBCProtocolRevision
	 */
	private $revision;

	public function __construct(IBCProtocolRevision $revision = null) {
		if ($revision != null) $this->revision = $revision;
		parent::__construct($revision);
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

	public function setRevision($revision){
		$this->revision = $revision;
		if ($this->recipients == null) {
			$this->buildRecipients();
		}
	}

	/** Macro key/value replacement map */
	public function macroMap() {
		$l = Logger::getLogger(__FUNCTION__);
		$currentProtocol = $this->getProtocol();
		$piNames = array();
		if ($currentProtocol) {
			foreach($currentProtocol->getPrincipalInvestigators() as $pi){
				$piNames[] = $pi->getName();
			}
		}
		$listPIs = function($pis) {
			$title = count($pis) > 1 ? 'Drs. ' : 'Dr. ';
			$last  = array_slice($pis, -1);
			$first = join(', ', array_slice($pis, 0, -1));
			$both  = array_filter(array_merge(array($first), $last), 'strlen');
			return $title . join(count($pis) > 2 ? ', and ' : ' and ', $both);
		};
		$l->fatal($currentProtocol);
		return array(
			"[PI]"							=>	!$currentProtocol ? "'PI Name(s)'" : $listPIs($piNames), // comma-seperated PI names
			"[Protocol Title]"				=>	!$currentProtocol ? "'Protocol Title'" : $currentProtocol->getProject_title(),
			"[Protocol Number]"				=>	!$currentProtocol ? "'Protocol Approval Date'" : $currentProtocol->getProtocol_number(),
			"[Protocol Approval Date]"		=>	!$currentProtocol ? "'Protocol Title'" : $currentProtocol->getApproval_date(),
			"[Expiration Date]"				=>	!$currentProtocol ? "'Expiration Date'" : $currentProtocol->getExpiration_date(),
			"[Reference Number]"			=>	!$currentProtocol ? "'Reference Number'" : "poo", // ask Mark
			"[Review Assignment Name]"		=>	!$currentProtocol ? "'Review Assignment Name'" : "poo", // TODO: probably part of 'Meetings', which isn't done
			"[Review Assignment Due Date]"	=>	!$currentProtocol ? "'Review Assignment Due Date'" : "poo", // TODO: probably part of 'Meetings', which isn't done
			"[Meeting Date]"				=>	!$currentProtocol ? "'Meeting Date'" : "poo", // TODO: probably part of 'Meetings', which isn't done
			"[Location]"					=>	!$currentProtocol ? "'Location'" : "poo" // TODO: probably part of 'Meetings', which isn't done
		);
	}

	/**
	 * Summary of buildRecipients
	 */
	public function buildRecipients() {
		$l = Logger::getLogger(__FUNCTION__);
		if ($this->revision != null) {
			if($this->recipients == null) $this->recipients = array();
			switch ($this->key_id) {
				case 1: /*protocol approved*/
				case 2: /*protocol not approved*/
				case 6: /*protocol submitted for review*/
				case 7: /*protocol expired*/
				case 8: /*protocol expiration notice*/
					$pis = array();
					foreach($this->getProtocol()->getPrincipalInvestigators() as $pi){
						$pis[] = $pi->getUser();
					}
					$this->recipients = array_merge(
						$this->recipients,
						$this->revision->getProtocolFillOutUsers(),
						//$this->revision->getProtocolUsers(),
						$pis
					);
					$l->fatal('protocol notice');
					break;
				case 3: /*protocol returned for revision*/
					$this->recipients = $this->revision->getPrimaryReviewers();
					$l->fatal('protocol returned for revision');
					break;
				case 4: /*protocol pre-review assignment*/
					$this->recipients = $this->revision->getPreliminaryReviewers();
					$l->fatal('protocol pre-review assignment');
					break;
				case 5: /*committee meeting scheduled*/
					$l->fatal('committee meeting scheduled');
					break;
				default:
					$this->recipients = array();
			}
			/* assigning an array to another array in php always makes a copy, not a reference... so this is safe for our use */
			$this->remainingRecipients = $this->recipients;
		}

	}



}
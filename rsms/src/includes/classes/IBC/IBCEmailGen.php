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

	private $revision;
	private $protocol;

	public function __construct($corpus, IBCProtocolRevision $revision) {
		if ($corpus == null) {
			$corpus = "Dear Dr. [PI],
				The Institutional Biosafety Committee (IBC) has reviewed your IBC Protocol Application titled &quot;[Protocol Title].&quot;
				This review was conducted to assess the safety of this research project and to identify any
				potential risk to public health or the environment. The IBC voted during a convened meeting to approve
				this protocol application. The following information has been assigned to your protocol application
				approval:

				Protocol Number: [Protocol Number]
				Approval Date: [Protocol Approval Date]
				Expiration Date: [Expiration Date]

				All IBC protocols are approved for a period of three (3) years. If this project will be extended beyond
				three years, an IBC protocol renewal must be submitted prior to the expiration date. Any changes to
				this research project must be submitted as an amendment to your protocol application. All
				amendments must be approved prior to initiating the proposed changes. This protocol approval is
				granted contingent on your commitment to comply with the following responsibilities as stated in the
				NIH Guidelines for Research Involving Recombinant or Synthetic Nucleic Acid Molecules (NIH Guidelines):

				I. All Principal Investigator (PI) Responsibilities in the NIH Guidelines, Section IV-B- 7
				II. Report any significant problems, violations, or any significant research-related accidents and
				illnesses to the IBC at IBC@mailbox.sc.edu
				III. Correct any deficiencies identified during laboratory safety inspections conducted by
				Environmental Health and Safety (EH&amp;S)


				* Access your approved protocol at http://sam.research.sc.edu:8081/TopazEnterprise/
				* Open this approved protocol and select the Reports icon at the top of the form
				* Print the report titled &quot;Protocol Detail Report Answered Questions Only&quot;
				* Maintain a copy of this protocol report with your Biosafety Manual in the lab

				Thank you for supporting our efforts to maintain compliance and ensure a safe research environment
				for all of the University's faculty, staff, and students.

				Sincerely,

				Institutional Biosafety Committee
				University of South Carolina &amp; USC School of Medicine";
		}
		parent::__construct($corpus);
		$this->revision = $revision;
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
<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
abstract class RadCrud extends GenericCrud {

	/**
	 * TOTALS parcel use amounts in a container, grouped by isotope
	 * param:  $useAmounts Array of ParcelUseAmounts
	 * return array of douples (isotopename: currie level)
	 * 
	 */
	public function sumUsages($useAmounts){
		$LOG = Logger::getLogger(__CLASS__);
		$isotopeAmounts = array();
		
		foreach($useAmounts as $amount){				
			$isotopeName = $amount->getIsotope_name();
			$isotopeId   = $amount->getIsoptope_id();		
			if(!array_key_exists($isotopeName, $isotopeAmounts)){
				$isotopeAmount = new IsotopeAmountDTO();
				$isotopeAmount->setIsotope_name($isotopeName);
				$isotopeAmount->setIsotope_id($isotopeId);				
				$isotopeAmount->setCurie_level($amount->getCurie_level());
				$isotopeAmounts[$isotopeName] = $isotopeAmount;
			}else{				
				$isotopeAmounts[$isotopeName]->addCuries($amount->getCurie_level());
			}
		}
		return array_values($isotopeAmounts);
	}
	
}
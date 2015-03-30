<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class RadCrud extends GenericCrud {
	// Required for GenericCrud
	public function getTableName() {}
	public function getColumnData() {}
	
	/**
	 * TOTALS parcel use amounts in a container, grouped by isotope
	 * param:  $useAmounts Array of ParcelUseAmounts
	 * return array of douples (isotopename: currie level)
	 * 
	 */
	public function sumUsages($useAmounts){
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug('calling sumUsages');
		$isotopeAmounts = array();
		foreach($useAmounts as $amount){
			$isotopeName = $amount->getIsotopeName();			
			if(!array_key_exists($isotopeName, $isotopeAmounts)){
				$isotopeAmount = new IsotopeAmountDTO();
				$isotopeAmount->setIsotopeName($isotopeName);
				$isotopeAmount->setCurieLevel($amount->getCurie_level());
				$isotopeAmounts[$isotopeName] = $isotopeAmount;
			}else{				
				$isotopeAmounts[$isotopeName]->addCuries($amount->getCurie_level());
			}
			$LOG->debug(array_values($isotopeAmounts));
			return array_values($isotopeAmounts);
			
		}

	}
	
}
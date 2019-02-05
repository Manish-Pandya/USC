<?php

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
        //$LOG->fatal($useAmounts);
		$isotopeAmounts = array();

		foreach($useAmounts as $amount){
            if($amount->getIs_active() != true)continue;
            $isotopeDao = new GenericDAO(new Isotope());

            if($amount->getIsotope_id() == null){
                $useDao = new GenericDAO(new ParcelUse());
                $use = $useDao->getById($amount->getParcel_use_id());

                $parcelDao = new GenericDAO(new Parcel());
                $parcel = $parcelDao->getById($use->getParcel_id());

                $authDao = new GenericDAO(new Authorization());
                $auth = $authDao->getById(18);

                $isotope = $isotopeDao->getById($auth->getIsotope_id());
            }else{
                $isotope = $isotopeDao->getById($amount->getIsotope_id());
            }

			$isotopeName = $isotope->getName();
			$isotopeId   = $isotope->getKey_id();
			$isMass   = $isotope->getIs_mass();
			if(!array_key_exists($isotopeName, $isotopeAmounts)){
				$isotopeAmount = new IsotopeAmountDTO();
				$isotopeAmount->setIsotope_name($isotopeName);
				$isotopeAmount->setIsotope_name($isotopeName);
				$isotopeAmount->setIs_mass($isMass);
				$isotopeAmount->setCurie_level($amount->getCurie_level());
				$isotopeAmounts[$isotopeName] = $isotopeAmount;
			}else{
				$isotopeAmounts[$isotopeName]->addCuries($amount->getCurie_level());
			}
		}
		return array_values($isotopeAmounts);
	}

}
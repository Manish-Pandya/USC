<?php

/**
 * Container short summary.
 *
 * Container description.
 *
 * @version 1.0
 * @author Matt Breeden
 */
class Container extends RadCrud
{

    protected $open_date;
	protected $close_date;
    protected $pickup_id;
	protected $pickup_date;
    protected $added_amounts;

    // required for GenericCrud
	public function getTableName(){}

	public function getColumnData(){}


    public function getClose_date(){ return $this->close_date; }
	public function setClose_date($close_date){ $this->close_date = $close_date; }

	public function getOpen_date(){ return $this->open_date; }
	public function setOpen_date($open_date){ $this->open_date = $open_date; }

    public function getPickup_date(){
        if($this->hasPrimaryKeyValue() && $this->getPickup() != null){
            $p = $this->getPickup();
            if($p->getPickup_date() != null){
                $this->pickup_date = $p->getPickup_date();
            }
        }
		return $this->pickup_date;
	}
	public function setPickup_date($pickup_date){
		$this->pickup_date = $pickup_date;
	}

    public function getAddedAmounts(){
        if($this->getAddedAmounts == null){
            $this->added_amounts = array();
            $method = method_exists($this, "getParcelUseAmounts") ? "getParcelUseAmounts" : "getParcel_use_amounts";
            if(!$method)return $this->added_amounts;
            foreach($this->$method() as $amt){
                if($amt->getParcel_use_id () == null){
                    $this->added_amounts[] = $amt;
                }
            }
        }
        return $this->added_amounts;
    }
    public function setAddedAmounts($amts){$this->added_amounts = $amts;}
}
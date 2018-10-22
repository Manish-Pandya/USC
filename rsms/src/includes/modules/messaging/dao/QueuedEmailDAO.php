<?php

class QueuedEmailDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new QueuedEmail());
    }

    public function getAllUnsent(){
        $whereSentDateNull = new WhereClauseGroup(array(
            new WhereClause("sent_date", "IS", 'NULL')
        ));

        $unsent = $this->getAllWhere($whereSentDateNull);
        return $unsent;
    }
}
?>
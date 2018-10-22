<?php

class MessageDAO extends GenericDAO {

    public function __construct(){
        parent::__construct(new Message());
    }

    public function findByContext($module, $messageType, $descriptor){
        $whereMatchModuleTypeDescriptor = new WhereClauseGroup(array(
            new WhereClause("module", "=", $module),
            new WhereClause("message_type", "=", $messageType),
            new WhereClause("context_descriptor", '=', $descriptor)
        ));

        $matches = $this->getAllWhere($whereMatchModuleTypeDescriptor);

        return $matches;
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
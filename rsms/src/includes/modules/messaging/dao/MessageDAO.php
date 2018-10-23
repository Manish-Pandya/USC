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

    public function getAllReadyToSend(){
        $sql = "SELECT * FROM " . $this->modelObject->getTableName()
            . " WHERE sent_date IS NULL AND (send_on IS NULL OR send_on <= NOW())";

		$stmt = DBConnection::prepareStatement($sql);

		if( $stmt->execute() ){
            $readyToSend = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
        }
        else{
            // Error
            $error = $stmt->errorInfo();
			$readyToSend = new QueryError($error);
            $this->LOG->fatal('Returning QueryError with message: ' . $error[2]);

            if($this->LOG->isDebugEnabled()){
                $this->LOG->debug($stmt->debugDumpParams());
            }
        }

		// 'close' the statment
		$stmt = null;

		return $readyToSend;
    }
}
?>
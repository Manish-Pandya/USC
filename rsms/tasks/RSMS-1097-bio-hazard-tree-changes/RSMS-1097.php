<?php
    const TASK_NUM = "TASKS.RSMS-1097";
    require_once 'loader.php';

    $LOG = LogUtil::get_logger(TASK_NUM, __FILE__);

    $LOG->info("***START TRANSACTION***");
    DBConnection::get()->beginTransaction();

    try{
        $manager = new HazardChangeManager();
        $success = $manager->process_actions($KNOWN_ACTIONS);

        if( $success == true ){
            DBConnection::get()->commit();
            $LOG->info("***COMMIT TRANSACTION***");
        }
        else {
            DBConnection::get()->rollback();
            $LOG->warn("***ROLLBACK TRANSACTION DUE TO TASK FAILURES***");
        }
    }
    catch(Exception $e){
        DBConnection::get()->rollback();
        $LOG->warn("***ROLLBACK TRANSACTION DUE TO ERROR***");
    }

?>

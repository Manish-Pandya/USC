<?php
    const TASK_NUM = "TASKS.RSMS-927";

    // Set up RSMS application
    require_once '/var/www/html/rsms/Application.php';

    // Include task scripts
    require_once 'actions.php';
    require_once 'domain/HazardChangeManager.php';
    require_once 'domain/A_ActionProcessor.php';
    require_once 'domain/AddActionProcessor.php';
    require_once 'domain/MoveActionProcessor.php';
    require_once 'domain/InactivateActionProcessor.php';
    require_once 'domain/DeleteActionProcessor.php';
    require_once 'domain/RenameActionProcessor.php';

    $LOG = LogUtil::get_logger(TASK_NUM, __FILE__);

    $LOG->info("***START TRANSACTION***");
    DBConnection::get()->beginTransaction();

    try{
        $manager = new HazardChangeManager();
        $success = $manager->process_actions($KNOWN_ACTIONS);

        if( $success = true ){
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

<?php

class Scheduler {
    static $LOG;
    static $DISABLED_TASKS;

    static function run( $moduleNames = null ){
        self::$LOG = Logger::getLogger(__CLASS__);
        self::$LOG->info("RSMS Scheduler Running");

        // Read configuration
        self::$DISABLED_TASKS = ApplicationConfiguration::get('module.Scheduler.tasks.disabled', array());

        $_module_filters = $moduleNames;
        if( $_module_filters != null ){
            // ensure module filters is array of module names
            if( !is_array($_module_filters) ){
                $_module_filters = array($moduleNames);
            }

            self::$LOG->debug("Limit to modules: " . implode(', ', $_module_filters));
        }

        // Scheduler
        $all_tasks = self::getTasks($_module_filters);
        self::$LOG->debug("Found " . count($all_tasks) . " scheduled tasks");

        foreach($all_tasks as $task){
            self::runTask($task);
        }

        self::$LOG->info("Scheduler execution complete");
    }

    static function getTasks( $moduleFilters = null ){
        $all_tasks = array();

        // Check each module for Task definitions
        foreach( ModuleManager::getAllModules() as $module ){
            if( $moduleFilters != null && !in_array($module->getModuleName(), $moduleFilters) ){
                self::$LOG->debug("Exclude module " . $module->getModuleName());
                continue;
            }

            $moduleTaskClasses = ModuleManager::getModuleFeatureClasses($module, 'tasks', 'Task');
            foreach( $moduleTaskClasses as $class ){
                // Check if this is configured to be disabled
                if( in_array($class, self::$DISABLED_TASKS) ){
                    // Task is disabled; ignore it!
                    self::$LOG->debug("$class is Disabled in Scheduler configuration");
                    continue;
                }

                // Create task instance
                $task = new $class;

                // Type must be a ScheudledTask
                if( $task instanceof ScheduledTask ){
                    self::$LOG->debug("Scheduling " . $module->getModuleName() . " / $class");
                    $all_tasks[] = $task;
                }
                else{
                    self::$LOG->warn("$class does not implement ScheduledTask");
                }
            }
        }

        // Sort tasks by their Priority
        usort($all_tasks, function($a, $b){
            $ap = $a->getPriority();
            $bp = $b->getPriority();

            if( $ap == $bp )
                return 0;

            return ($bp < $ap) ? -1 : 1;
        });

        return $all_tasks;
    }

    static function runTask($task){
        $name = get_class($task);
        try{
            self::$LOG->info("Running $name");
            $taskResult = $task->run();
            self::$LOG->info("Completed $name: $taskResult");
        }
        catch(Exception $err){
            self::$LOG->error("Error in scheduled task execution of $name: " . $err->getMessage());
        }
    }
}

?>
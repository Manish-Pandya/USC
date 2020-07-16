<?php
// Inject Notification Status into script for active modules

// Get all Active Modules which declare notification stats
$stats = [];
foreach(ModuleManager::getActiveModules() as $module){
    if( $module instanceof NotificationStatsProvider ){
        $stats[ get_class($module) ] = $module->getNotificationStats();
    }
}

// Dump stats into after-load exec
echo '<script type="text/javascript">(function(){';
echo 'window.NotificationStats = ' . json_encode($stats) . ';';
echo '})()</script>';

?>

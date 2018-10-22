<?php

interface ScheduledTask {
    public function getPriority();
    public function run();
}

?>
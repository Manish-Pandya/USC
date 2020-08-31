<?php
/**
 * A NotificationStatsProvider module provides a NotificationStatsDto
 * which contains items which warrant notifying to the user
 */
interface NotificationStatsProvider {
    public function getNotificationStats() : NotificationStatsDto;
}
?>

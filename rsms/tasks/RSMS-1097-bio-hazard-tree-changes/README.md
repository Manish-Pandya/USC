# [RSMS-1097](https://uscehs.atlassian.net/browse/RSMS-1097): Restructure Biological Hazards Tree

This Task performs a list of actions on the Biological Hazards subtree of the RSMS hazards. The actions are defined in ```actions.php```, and are applied by executing the ```RSMS-1097.php``` script:

```
$ php ./RSMS-1097.php
```

This script executes within a database transaction, and will rollback if an error is encountered or any actions are unable to be verified after execution.

```hazard_actions_report.php``` will perform and rollback all actions regardless of result, and will output HTML which represents a before-and-after snapshot of the Biological Hazards subtree.

_Note that this task includes copied and updated code from ```/rsms/tasks/RSMS-927-hazard-tree-changes```, which applies a similar routine to the Chemical Hazards subtree._
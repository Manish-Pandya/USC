-- Core module has been updated; update the templates and queues to reference new name
UPDATE message_template SET module='Lab Inspection' WHERE module='Core';
UPDATE message_queue SET module='Lab Inspection' WHERE module='Core';

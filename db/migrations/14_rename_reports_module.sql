-- Reports module has been updated; update the templates and queues to reference new name
UPDATE message_template SET module='Chair Report' WHERE module='Reports';
UPDATE message_queue SET module='Chair Report' WHERE module='Reports';

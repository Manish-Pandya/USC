-- Auth module has been updated; update the templates and queues to reference new name
UPDATE message_template SET module='Authorization' WHERE module='Auth';
UPDATE message_queue SET module='Authorization' WHERE module='Auth';

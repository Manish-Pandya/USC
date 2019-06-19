-- Get ANY active PIAuthorization + PI + User
CREATE OR REPLACE VIEW `rad_authorized_users` AS
SELECT
    pi_auth_user.user_id,
    pi_auth.key_id as pi_authorization_id,
    pi_auth.principal_investigator_id,
    pi_auth.approval_date
FROM pi_authorization pi_auth
JOIN pi_authorization_user pi_auth_user
    ON pi_auth_user.pi_authorization_id = pi_auth.key_id
WHERE pi_auth.termination_date IS NULL;

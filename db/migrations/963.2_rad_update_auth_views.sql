-- Create new view to list the status of each PI's CURRENT authorization
CREATE OR REPLACE VIEW `rad_current_pi_authorization_status` AS
SELECT
    pi_auth.key_id as pi_authorization_id,
    auth_status.*
FROM
    ( SELECT
            principal_investigator_id,
            MAX(termination_date) AS termination_date,
            MAX(approval_date) AS approval_date
        FROM pi_authorization
        GROUP BY principal_investigator_id
    ) AS auth_status
INNER JOIN pi_authorization pi_auth
    ON pi_auth.principal_investigator_id = auth_status.principal_investigator_id
    AND pi_auth.approval_date = auth_status.approval_date;

-- Update auth-user view to list users from only non-terminated current auths
-- Get ANY active PIAuthorization + PI + User
CREATE OR REPLACE VIEW `rad_authorized_users` AS
SELECT
    pi_auth_user.user_id,
    pi_auth.key_id as pi_authorization_id,
    pi_auth.principal_investigator_id,
    pi_auth.approval_date
FROM pi_authorization pi_auth
JOIN rad_current_pi_authorization_status current_auth
    ON current_auth.pi_authorization_id = pi_auth.key_id
JOIN pi_authorization_user pi_auth_user
    ON pi_auth_user.pi_authorization_id = pi_auth.key_id
WHERE pi_auth.termination_date IS NULL;

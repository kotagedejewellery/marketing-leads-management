UPDATE leads AS lead
SET source_branch_id = user_account.branch_id
FROM users AS user_account
WHERE lead.source_branch_id IS NULL
  AND lead.assigned_user_id = user_account.id
  AND user_account.branch_id IS NOT NULL;

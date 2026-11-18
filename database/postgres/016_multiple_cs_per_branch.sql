DROP INDEX IF EXISTS users_one_active_cs_per_branch;

ALTER TABLE branches
  ADD COLUMN IF NOT EXISTS default_import_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL;

UPDATE branches AS branch
SET default_import_user_id = (
  SELECT user_account.id
  FROM users AS user_account
  WHERE user_account.branch_id = branch.id
    AND user_account.role = 'cs'
    AND user_account.active = TRUE
  ORDER BY user_account.created_at, user_account.id
  LIMIT 1
)
WHERE branch.default_import_user_id IS NULL;

CREATE INDEX IF NOT EXISTS users_active_cs_by_branch_idx
  ON users (branch_id)
  WHERE role = 'cs' AND active = TRUE;

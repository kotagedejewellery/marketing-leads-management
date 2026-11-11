ALTER TABLE users ADD COLUMN IF NOT EXISTS active BOOLEAN NOT NULL DEFAULT TRUE;
ALTER TABLE leads ADD COLUMN IF NOT EXISTS archived_at TIMESTAMPTZ;

DO $$
BEGIN
  IF to_regclass('public.users_one_cs_per_branch') IS NOT NULL THEN
    DROP INDEX users_one_cs_per_branch;
  END IF;
END $$;
CREATE UNIQUE INDEX IF NOT EXISTS users_one_active_cs_per_branch ON users (branch_id) WHERE role = 'cs' AND active = TRUE;
CREATE INDEX IF NOT EXISTS leads_archived_at_idx ON leads (archived_at);

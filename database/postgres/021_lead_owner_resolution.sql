ALTER TABLE leads
  ADD COLUMN IF NOT EXISTS owner_resolution VARCHAR(32) NOT NULL DEFAULT 'manual';

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'leads_owner_resolution_check') THEN
    ALTER TABLE leads
      ADD CONSTRAINT leads_owner_resolution_check
      CHECK (owner_resolution IN ('manual', 'source_matched', 'fallback', 'awaiting_account', 'unassigned'));
  END IF;
END $$;

CREATE INDEX IF NOT EXISTS leads_awaiting_account_idx
  ON leads (source_branch_id, imported_cs_name)
  WHERE owner_resolution = 'awaiting_account' AND assigned_user_id IS NULL AND archived_at IS NULL;

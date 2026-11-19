ALTER TABLE lead_sync_runs
  ADD COLUMN IF NOT EXISTS branch_id BIGINT REFERENCES branches(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS lead_sync_runs_branch_completed_idx
  ON lead_sync_runs (branch_id, completed_at DESC)
  WHERE branch_id IS NOT NULL;

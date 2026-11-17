ALTER TABLE leads ADD COLUMN IF NOT EXISTS source_conflict_at TIMESTAMPTZ;
ALTER TABLE leads ADD COLUMN IF NOT EXISTS source_conflict_branch_id BIGINT REFERENCES branches(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS leads_source_conflict_idx ON leads (source_conflict_at DESC) WHERE source_conflict_at IS NOT NULL;

ALTER TABLE lead_sync_runs DROP CONSTRAINT IF EXISTS lead_sync_runs_status_check;
ALTER TABLE lead_sync_runs ADD CONSTRAINT lead_sync_runs_status_check CHECK (status IN ('success', 'partial', 'failed'));

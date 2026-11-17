ALTER TABLE branches ADD COLUMN IF NOT EXISTS google_spreadsheet_id VARCHAR(128);
ALTER TABLE branches ADD COLUMN IF NOT EXISTS google_sheets_synced_at TIMESTAMPTZ;
ALTER TABLE branches ADD COLUMN IF NOT EXISTS google_sheets_sync_error TEXT;

CREATE UNIQUE INDEX IF NOT EXISTS branches_google_spreadsheet_id_unique
  ON branches (google_spreadsheet_id)
  WHERE google_spreadsheet_id IS NOT NULL;

ALTER TABLE leads ADD COLUMN IF NOT EXISTS source_branch_id BIGINT REFERENCES branches(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS leads_source_branch_idx ON leads (source_branch_id);

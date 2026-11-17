CREATE TABLE IF NOT EXISTS lead_source_occurrences (
  id BIGSERIAL PRIMARY KEY,
  lead_id BIGINT NOT NULL REFERENCES leads(id) ON DELETE CASCADE,
  spreadsheet_id VARCHAR(128) NOT NULL,
  source_tab VARCHAR(60) NOT NULL,
  source_row_no VARCHAR(50),
  source_date_raw VARCHAR(60),
  source_hash CHAR(64) NOT NULL UNIQUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS lead_source_occurrences_lead_idx
  ON lead_source_occurrences (lead_id, created_at DESC);

CREATE TABLE IF NOT EXISTS lead_sync_runs (
  id BIGSERIAL PRIMARY KEY,
  source VARCHAR(40) NOT NULL,
  status VARCHAR(20) NOT NULL CHECK (status IN ('success', 'failed')),
  summary JSONB,
  error TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  completed_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

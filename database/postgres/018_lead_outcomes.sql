ALTER TABLE leads
  ADD COLUMN IF NOT EXISTS outcome VARCHAR(20),
  ADD COLUMN IF NOT EXISTS outcome_factor VARCHAR(80),
  ADD COLUMN IF NOT EXISTS outcome_note TEXT,
  ADD COLUMN IF NOT EXISTS outcome_recorded_at TIMESTAMPTZ;

ALTER TABLE leads
  DROP CONSTRAINT IF EXISTS leads_outcome_check;

ALTER TABLE leads
  ADD CONSTRAINT leads_outcome_check CHECK (outcome IN ('closing', 'loss') OR outcome IS NULL);

CREATE INDEX IF NOT EXISTS leads_outcome_idx ON leads (outcome, outcome_factor) WHERE archived_at IS NULL;

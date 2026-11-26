ALTER TABLE lead_source_occurrences
  ADD COLUMN IF NOT EXISTS source_branch_id BIGINT REFERENCES branches(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS lead_source_occurrences_spreadsheet_branch_idx
  ON lead_source_occurrences (spreadsheet_id, source_branch_id)
  WHERE source_branch_id IS NOT NULL;

UPDATE lead_source_occurrences AS occurrence
SET source_branch_id = branch.id
FROM branches AS branch
WHERE occurrence.source_branch_id IS NULL
  AND branch.google_spreadsheet_id = occurrence.spreadsheet_id;

CREATE TABLE IF NOT EXISTS branch_source_events (
  id BIGSERIAL PRIMARY KEY,
  branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
  actor_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
  action VARCHAR(32) NOT NULL CHECK (action IN ('connected', 'replaced', 'disconnected', 'replacement_failed')),
  previous_spreadsheet_id VARCHAR(128),
  spreadsheet_id VARCHAR(128),
  detail TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS branch_source_events_branch_created_idx
  ON branch_source_events (branch_id, created_at DESC);

CREATE INDEX IF NOT EXISTS branch_source_events_spreadsheet_idx
  ON branch_source_events (spreadsheet_id)
  WHERE spreadsheet_id IS NOT NULL;

INSERT INTO branch_source_events (branch_id, action, spreadsheet_id, detail)
SELECT id, 'connected', google_spreadsheet_id, 'Konfigurasi sumber aktif sebelum audit konfigurasi tersedia.'
FROM branches
WHERE google_spreadsheet_id IS NOT NULL
  AND NOT EXISTS (
    SELECT 1
    FROM branch_source_events AS event
    WHERE event.branch_id = branches.id
      AND event.spreadsheet_id = branches.google_spreadsheet_id
      AND event.action IN ('connected', 'replaced')
  );

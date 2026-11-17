ALTER TABLE leads ADD COLUMN IF NOT EXISTS crm_taken_over_at TIMESTAMPTZ;

CREATE INDEX IF NOT EXISTS leads_crm_taken_over_idx
  ON leads (crm_taken_over_at DESC)
  WHERE crm_taken_over_at IS NOT NULL;

CREATE TABLE IF NOT EXISTS lead_collaborators (
  id BIGSERIAL PRIMARY KEY,
  lead_id BIGINT NOT NULL REFERENCES leads(id) ON DELETE CASCADE,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  added_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
  added_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  removed_at TIMESTAMPTZ,
  UNIQUE (lead_id, user_id)
);

CREATE INDEX IF NOT EXISTS lead_collaborators_active_lead_idx
  ON lead_collaborators (lead_id, user_id)
  WHERE removed_at IS NULL;

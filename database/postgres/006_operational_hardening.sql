ALTER TABLE leads ADD COLUMN IF NOT EXISTS current_stage VARCHAR(40);
ALTER TABLE leads ADD COLUMN IF NOT EXISTS next_follow_up_at TIMESTAMPTZ;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint
    WHERE conname = 'leads_current_stage_check'
      AND conrelid = 'leads'::regclass
  ) THEN
    ALTER TABLE leads ADD CONSTRAINT leads_current_stage_check
      CHECK (current_stage IN ('clicked', 'asked', 'price_list', 'offline', 'online', 'closing_pending_transfer', 'closing', 'repeat_order') OR current_stage IS NULL);
  END IF;
END $$;

WITH ranked_milestones AS (
  SELECT DISTINCT ON (lead_id) lead_id, milestone
  FROM lead_milestones
  WHERE is_active = TRUE
  ORDER BY lead_id,
    CASE milestone
      WHEN 'repeat_order' THEN 8
      WHEN 'closing' THEN 7
      WHEN 'closing_pending_transfer' THEN 6
      WHEN 'online' THEN 5
      WHEN 'offline' THEN 4
      WHEN 'price_list' THEN 3
      WHEN 'asked' THEN 2
      WHEN 'clicked' THEN 1
    END DESC
)
UPDATE leads
SET current_stage = ranked_milestones.milestone
FROM ranked_milestones
WHERE leads.id = ranked_milestones.lead_id
  AND leads.current_stage IS NULL;

CREATE INDEX IF NOT EXISTS leads_active_stage_updated_idx
  ON leads (current_stage, updated_at DESC)
  WHERE archived_at IS NULL;
CREATE INDEX IF NOT EXISTS leads_follow_up_idx
  ON leads (next_follow_up_at)
  WHERE archived_at IS NULL AND next_follow_up_at IS NOT NULL;

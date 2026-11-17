ALTER TABLE leads DROP CONSTRAINT IF EXISTS leads_current_stage_check;
ALTER TABLE lead_milestones DROP CONSTRAINT IF EXISTS lead_milestones_milestone_check;

INSERT INTO lead_milestones (lead_id, milestone, is_active, marked_at, marked_by)
SELECT DISTINCT ON (lead_id)
  lead_id,
  'closing_repeat',
  TRUE,
  marked_at,
  marked_by
FROM lead_milestones
WHERE is_active = TRUE
  AND milestone IN ('closing_pending_transfer', 'closing', 'repeat_order')
ORDER BY lead_id, marked_at DESC NULLS LAST
ON CONFLICT (lead_id, milestone) DO UPDATE
SET is_active = TRUE,
    marked_at = EXCLUDED.marked_at,
    marked_by = EXCLUDED.marked_by;

UPDATE lead_milestones
SET is_active = FALSE
WHERE is_active = TRUE
  AND milestone IN ('closing_pending_transfer', 'closing', 'repeat_order');

UPDATE leads
SET current_stage = 'closing_repeat'
WHERE current_stage IN ('closing_pending_transfer', 'closing', 'repeat_order');

ALTER TABLE leads
  ADD CONSTRAINT leads_current_stage_check
  CHECK (current_stage IN ('clicked', 'asked', 'price_list', 'offline', 'online', 'closing_repeat') OR current_stage IS NULL);

ALTER TABLE lead_milestones
  ADD CONSTRAINT lead_milestones_milestone_check
  CHECK (
    milestone IN ('clicked', 'asked', 'price_list', 'offline', 'online', 'closing_repeat')
    OR (milestone IN ('closing_pending_transfer', 'closing', 'repeat_order') AND is_active = FALSE)
  );

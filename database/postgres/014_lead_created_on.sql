ALTER TABLE leads ADD COLUMN IF NOT EXISTS lead_created_on DATE;

UPDATE leads
SET lead_created_on = CASE
  WHEN source_date_raw ~ '^\d{4}-\d{2}-\d{2}$' THEN source_date_raw::date
  WHEN source_date_raw ~ '^\d{1,2}/\d{1,2}/\d{4}$' THEN to_date(source_date_raw, 'FMDD/FMMM/YYYY')
  WHEN source_date_raw ~ '^\d{1,2} (January|February|March|April|May|June|July|August|September|October|November|December) \d{4}$' THEN to_date(source_date_raw, 'FMDD FMMonth YYYY')
END
WHERE lead_created_on IS NULL
  AND source_date_raw IS NOT NULL;

CREATE INDEX IF NOT EXISTS leads_active_created_on_idx
  ON leads (lead_created_on DESC)
  WHERE archived_at IS NULL;

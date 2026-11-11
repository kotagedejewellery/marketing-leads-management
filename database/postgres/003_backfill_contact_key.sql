DO $$
BEGIN
  IF EXISTS (
    SELECT 1
    FROM (
      SELECT CASE
        WHEN LENGTH(REGEXP_REPLACE(contact_raw, '\D', '', 'g')) >= 8
          THEN 'phone:' || REGEXP_REPLACE(contact_raw, '\D', '', 'g')
        ELSE 'contact:' || LOWER(BTRIM(contact_raw))
      END AS derived_key
      FROM leads
      WHERE contact_key IS NULL AND contact_raw IS NOT NULL AND BTRIM(contact_raw) <> ''
      GROUP BY derived_key
      HAVING COUNT(*) > 1
    ) duplicates
  ) THEN
    RAISE EXCEPTION 'Cannot backfill contact_key: duplicate legacy contacts require manual reconciliation first.';
  END IF;
END $$;

UPDATE leads
SET contact_key = CASE
  WHEN LENGTH(REGEXP_REPLACE(contact_raw, '\D', '', 'g')) >= 8
    THEN 'phone:' || REGEXP_REPLACE(contact_raw, '\D', '', 'g')
  ELSE 'contact:' || LOWER(BTRIM(contact_raw))
END
WHERE contact_key IS NULL AND contact_raw IS NOT NULL AND BTRIM(contact_raw) <> '';

WITH normalized_dates AS (
  SELECT
    id,
    replace(
      replace(
        replace(
          replace(
            replace(
              replace(
                replace(
                  replace(
                    replace(source_date_raw, 'Januari', 'January'),
                  'Februari', 'February'),
                'Maret', 'March'),
              'Mei', 'May'),
            'Juni', 'June'),
          'Juli', 'July'),
        'Agustus', 'August'),
      'Oktober', 'October'),
    'Desember', 'December') AS value
  FROM leads
  WHERE lead_created_on IS NULL
    AND source_date_raw IS NOT NULL
)
UPDATE leads AS l
SET lead_created_on = to_date(normalized_dates.value, 'FMDD FMMonth YYYY')
FROM normalized_dates
WHERE l.id = normalized_dates.id
  AND normalized_dates.value ~ '^\d{1,2} (January|February|March|April|May|June|July|August|September|October|November|December) \d{4}$';

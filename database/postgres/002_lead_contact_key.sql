ALTER TABLE leads ADD COLUMN IF NOT EXISTS contact_key VARCHAR(255);

CREATE UNIQUE INDEX IF NOT EXISTS leads_contact_key_unique
ON leads (contact_key)
WHERE contact_key IS NOT NULL;

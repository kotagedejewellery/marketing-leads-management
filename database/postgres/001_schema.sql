CREATE TABLE IF NOT EXISTS branches (
  id BIGSERIAL PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS users (
  id BIGSERIAL PRIMARY KEY,
  branch_id BIGINT REFERENCES branches(id) ON DELETE RESTRICT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(10) NOT NULL CHECK (role IN ('admin', 'cs')),
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  CHECK ((role = 'admin' AND branch_id IS NULL) OR (role = 'cs' AND branch_id IS NOT NULL))
);

ALTER TABLE users ADD COLUMN IF NOT EXISTS branch_id BIGINT;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(10);
ALTER TABLE users ADD COLUMN IF NOT EXISTS active BOOLEAN;

UPDATE users SET password_hash = password WHERE password_hash IS NULL AND password IS NOT NULL;
UPDATE users SET role = 'admin' WHERE role IS NULL;
UPDATE users SET active = TRUE WHERE active IS NULL;

ALTER TABLE users ALTER COLUMN password_hash SET NOT NULL;
ALTER TABLE users ALTER COLUMN role SET NOT NULL;
ALTER TABLE users ALTER COLUMN role SET DEFAULT 'admin';
ALTER TABLE users ALTER COLUMN active SET NOT NULL;
ALTER TABLE users ALTER COLUMN active SET DEFAULT TRUE;

DO $$
BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_branch_id_fkey') THEN
    ALTER TABLE users ADD CONSTRAINT users_branch_id_fkey FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE RESTRICT;
  END IF;

  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_role_check') THEN
    ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'cs'));
  END IF;

  IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'users_role_branch_check') THEN
    ALTER TABLE users ADD CONSTRAINT users_role_branch_check CHECK ((role = 'admin' AND branch_id IS NULL) OR (role = 'cs' AND branch_id IS NOT NULL));
  END IF;
END $$;

ALTER TABLE branches ADD COLUMN default_import_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS users_active_cs_by_branch_idx ON users (branch_id) WHERE role = 'cs' AND active = TRUE;

CREATE TABLE IF NOT EXISTS leads (
  id BIGSERIAL PRIMARY KEY,
  assigned_user_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
  import_fingerprint CHAR(64) UNIQUE,
  source_row_no VARCHAR(50),
  source_date_raw VARCHAR(60),
  contact_raw TEXT,
  name VARCHAR(160),
  phone VARCHAR(60),
  respond_time VARCHAR(60),
  imported_cs_name VARCHAR(120),
  imported_keterangan TEXT,
  raw_kebutuhan TEXT,
  greeting TEXT,
  chat_time TEXT,
  source_description VARCHAR(160),
  campaign_description VARCHAR(160),
  imported_definition VARCHAR(30),
  intent_buying VARCHAR(30) CHECK (intent_buying IN ('Tunangan', 'Lamaran', 'Nikahan', 'Anniversary', 'Lainnya') OR intent_buying IS NULL),
  usage_date DATE,
  budget VARCHAR(120),
  keterangan TEXT,
  outcome VARCHAR(20) CHECK (outcome IN ('closing', 'loss') OR outcome IS NULL),
  outcome_factor VARCHAR(80),
  outcome_note TEXT,
  outcome_recorded_at TIMESTAMPTZ,
  current_stage VARCHAR(40) CHECK (current_stage IN ('clicked', 'asked', 'price_list', 'offline', 'online', 'closing_pending_transfer', 'closing', 'repeat_order') OR current_stage IS NULL),
  next_follow_up_at TIMESTAMPTZ,
  archived_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS leads_assigned_user_idx ON leads (assigned_user_id);
CREATE INDEX IF NOT EXISTS leads_updated_at_idx ON leads (updated_at DESC);
CREATE INDEX IF NOT EXISTS leads_outcome_idx ON leads (outcome, outcome_factor) WHERE archived_at IS NULL;

CREATE TABLE IF NOT EXISTS lead_milestones (
  lead_id BIGINT NOT NULL REFERENCES leads(id) ON DELETE CASCADE,
  milestone VARCHAR(40) NOT NULL CHECK (milestone IN ('clicked', 'asked', 'price_list', 'offline', 'online', 'closing_pending_transfer', 'closing', 'repeat_order')),
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  marked_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  marked_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
  PRIMARY KEY (lead_id, milestone)
);

CREATE TABLE IF NOT EXISTS lead_activity (
  id BIGSERIAL PRIMARY KEY,
  lead_id BIGINT NOT NULL REFERENCES leads(id) ON DELETE CASCADE,
  actor_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
  action VARCHAR(80) NOT NULL,
  detail TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS lead_activity_lead_created_idx ON lead_activity (lead_id, created_at DESC);

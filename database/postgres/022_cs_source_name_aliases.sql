CREATE TABLE IF NOT EXISTS cs_source_name_aliases (
  id BIGSERIAL PRIMARY KEY,
  branch_id BIGINT NOT NULL REFERENCES branches(id) ON DELETE RESTRICT,
  user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE RESTRICT,
  source_name VARCHAR(120) NOT NULL,
  source_name_key VARCHAR(120) NOT NULL,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  UNIQUE (branch_id, source_name_key)
);

CREATE INDEX IF NOT EXISTS cs_source_name_aliases_user_idx
  ON cs_source_name_aliases (user_id);

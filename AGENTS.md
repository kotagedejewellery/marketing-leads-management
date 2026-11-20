# Project agent rules

## Required working order

1. Before every project task—including a new session, continued session, or context reset—read the full current contents of every applicable `AGENTS.md` before taking action.
2. Select the narrowest relevant installed Matt Pocock skill, read its full `SKILL.md`, and follow its workflow.
3. For code work, use Ponytail to assess dependencies and blast radius. Use full level for behaviour, business logic, data flow, permissions, shared code, bug fixes, and refactors; scale trivial-change analysis proportionally.
4. Before understanding or changing non-trivial code or architecture, use Graphify when it is available. Read source directly only when the graph lacks the needed context. Update Graphify after each logical code change. If Graphify is unavailable or fails, report the command and relevant error; do not claim graph validation.
5. For UI/UX work, read `DESIGN.md`, inspect the affected existing pattern, and run Impeccable context once per session. Use Hallmark only when its trigger applies. Validate the rendered result before handoff.

Use installed supporting skills only when their triggers apply. Combine overlapping skill steps so investigation and validation happen once. State assumptions that materially affect the outcome, choose the simplest complete solution, keep changes scoped, and use verifiable acceptance criteria.

## Scope and compatibility

- Deliver the smallest complete change. Preserve existing API contracts, URLs, schema, permissions, UX flows, defaults, and unrelated behaviour.
- Reuse established architecture, conventions, components, utilities, and dependencies before introducing anything new. Do not add a dependency when the existing stack is enough.
- Do not perform unrequested refactors, cleanup, optimisation, or follow-on improvements. Stop when the acceptance criteria are met.
- For bugs, find the root cause; trace callers, shared components, handlers, utilities, queries, tests, and equivalent flows. Fix all cases sharing that root cause, report similar-but-different cases, and prove the regression is covered.

## Data and database safety

Existing data, including local databases, is non-disposable. Without explicit user approval, never reset or recreate a database; run `migrate:fresh`; use `DROP DATABASE`, `DROP SCHEMA`, `TRUNCATE`, mass deletion, destructive seed, destructive migration, or delete Docker database volumes. Never alter a migration already used in shared, staging, or production environments.

Use forward-only, non-destructive migrations. Before any risky migration, seed, or data mutation, verify the environment and target database, assess existing-data impact, and choose a non-destructive approach. If data loss is unavoidable, stop and request explicit approval. Deploy, build, startup, CI/CD, Dockerfile, entrypoint, and post-deploy logic must not reset a database.

## Validation and tools

- Test proportionally to risk, prioritising business logic, permissions, calculations, data mutation, migrations, shared behaviour, and regressions. Report only checks actually run.
- Optimise only for the task or an evidenced bottleneck. Prefer removing unnecessary work, then query/payload efficiency, pagination, caching with clear invalidation, and indexes backed by real access patterns. Preserve simplicity.
- Use Agent Browser for browser-level flow validation when available: snapshot, act, then take a fresh snapshot before reusing element references. Use Agent Desktop for desktop/OS or cross-application flows when available, after checking its status and reading its desktop and platform guidance. Report scenario, expected result, actual result, and blockers.
- For new UI, redesigns, or an unclear visual direction, use available reference-search tooling and briefly name the source and rationale. Adapt it to the KGJ brand, design system, components, and stack. Small changes follow the existing design.
- If a required Graphify, UI, test, build, or browser/desktop tool is unavailable or fails, report the command and relevant error. Do not fabricate a substitute result or install a replacement without approval.

## Project context

- Read `PRODUCT.md` for product or business-rule changes and `DESIGN.md` for UI/UX work.
- For architecture, integration, data, or deployment, read the relevant schema, migrations, deployment documentation, and any available `docs/TECH-STACK.md` or `docs/ARCHITECTURE.md`.
- Read `docs/PRD.md` when it exists and the task changes product requirements. Do not load documentation unrelated to the task.
- Ask for clarification only when ambiguity materially changes business rules, permissions, destructive operations, data interpretation, scope, or behaviour.

When rules conflict, use this order: data safety; security and permission; correctness; scope and acceptance criteria; existing behaviour; existing architecture; regression prevention; testing; performance; cleanup.

**Understand → Trace → Change minimally → Protect data → Test → Validate → Stop.**

## Docker runtime verification

After a change to runtime code or configuration (`app/`, `resources/`, `routes/`, `config/`, `database/`, `Dockerfile`, Compose files, or dependency manifests), rebuild and recreate the local Docker application with `docker compose up -d --build`.

Then verify `docker compose ps` reports a healthy database and running application, and confirm `http://localhost:8082/up` returns HTTP 200. Documentation-only and graph-only changes do not require a Docker rebuild.

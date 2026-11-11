# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Laravel 13 with Inertia, Vue, TypeScript, Tailwind CSS, PostgreSQL 16, and Docker Compose.

## Users

- Customer-service (CS) staff who update their assigned leads during daily follow-up.
- Administrators who monitor every lead and reassign work when needed.

## Product Purpose

Give CS one fast working screen to maintain lead progress, while giving admins an accurate view of follow-up, conversion, and ownership.

## Positioning

Each lead has one record and an append-only activity history instead of being split across daily spreadsheet tabs and WhatsApp-account columns.

## Operating Context

The initial data source is the existing KGJ monitoring spreadsheet. The working interface is used throughout the day on desktop and mobile; CS update a checklist, notes, and important lead details.

## Capabilities and Constraints

- Inferred from the supplied brief: CS sees assigned work; admins see all work.
- Admins can see every branch, assign leads, and import CSV; a CS account can only see and update assigned leads.
- The first surface provides lead filtering, lead detail/update, milestone history, and monitoring summaries.
- Import-ready fields preserve the spreadsheet's No, Nama/Nomor, Respond Time, Nama CS, Keterangan, Kebutuhan, Greeting, Waktu Chat, source, campaign, and initial lead definition alongside CS-maintained fields.
- CS-maintained qualification is separate from imported source text: Intent Buying (Tunangan, Lamaran, Nikahan, Anniversary, or Lainnya), usage date, budget, keterangan, and grouped Cold/Warm/Hot milestones.
- CSV and XLSX imports are one-way, select the `All Leads` worksheet when available, and reject duplicates by a stable contact key.
- Lead milestones are: Cold (Klik saja), Warm (Tanya-tanya saja, Sampai Pricelist), and Hot (Datang Offline, Diskusi Online, Closing tapi belum Transfer, Closing, Repeat Order).

## Evidence on Hand

- Existing Google Sheet: `Salinan dari 10. KGJ PUSAT - MONITORING ORDER & LEADS HARIAN OKTOBER 2026`.
- Supplied sketches of the desired lead table and detail fields.

## Product Principles

- One lead, one current record, one visible history.
- CS should update a lead in seconds, without navigating a spreadsheet grid.
- Status must communicate the next action, not merely record an outcome.
- Admin monitoring should be readable without asking CS for a manual recap.


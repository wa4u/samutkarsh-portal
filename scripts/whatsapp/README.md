# WhatsApp archive import

One-time / repeatable tooling to turn a WhatsApp group export into site content:
**Activities** (session reports), **Testimonials** (student/parent feedback), and
**Galleries** (photos). Reused whenever a fresh batch of feedback comes in.

Phone numbers are scrubbed everywhere before anything is published, and every
import lands **unpublished** for review. The raw chat (with phone numbers) is
kept under `storage/app/` which is git-ignored — it is never committed. The
GitHub repo is public, so only scrubbed, reviewed data is committed.

## The durable pieces (committed, in `app/Console/Commands/`)

| Command | What it does |
|---|---|
| `activities:parse-whatsapp {source}` | Parse the exported HTML into structured `messages.json` + `candidates.json`. |
| `activities:import` | Load classified session reports as Activity drafts. |
| `testimonials:import-whatsapp --file=…` | Upsert testimonials from a normalized JSON (scrubs phones, idempotent). |
| `gallery:import-whatsapp --base-url=…` | Download, optimise + watermark, and album the photos. |

The seeders (`ActivitySeeder`, `TestimonialSeeder`) load the committed,
phone-scrubbed JSON in `database/seeders/data/` on production.

## The one-off helpers (in this folder)

These bridge "parsed messages" → "committed seed", with an AI classification
step in between. They read/write under `storage/app/private/whatsapp/`.

- `gen_gallery_manifest.php` — groups image attachments by month into
  `database/seeders/data/gallery_manifest.json`.
- `chunk_feedback.php` — splits `candidates.json` into chunks for classification.
- `merge_testimonials.php` — merges the classified feedback, scrubs phones, and
  writes `database/seeders/data/testimonials.json`.

## Workflow for a new text export

1. `php artisan activities:parse-whatsapp path/to/export.html`
2. Classify `candidates.json` (report / feedback / noise) — see git history of
   this feature for the AI-classification prompt; produces `classified.json`.
3. Activities: `php artisan activities:import`
4. Testimonials: `php scripts/whatsapp/chunk_feedback.php` → classify → 
   `php scripts/whatsapp/merge_testimonials.php` → 
   `php artisan testimonials:import-whatsapp`
   (Classification should extract `author_name`, `role`, `event` AND `center`.
   Testimonials carry `center` + `date`, so the public page filters by centre
   and year — same as Activities. `date` comes from the message timestamp.)
5. Photos: `php scripts/whatsapp/gen_gallery_manifest.php` → 
   `php artisan gallery:import-whatsapp --base-url=<where the images are hosted>`

## Workflow for screenshots (e.g. the weekly feedback screenshots)

No HTML to parse — type/transcribe each screenshot into a JSON array of rows
(`author_name`, `role`, `event`, `body`, optional `date`) and run:

```bash
php artisan testimonials:import-whatsapp --file=storage/app/whatsapp/new.json
```

Same scrubbing + idempotency; review and publish in **admin → Content → Testimonials**.

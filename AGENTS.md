# PortGen repository guidance

- Follow the product and security requirements in the approved PortGen master prompt.
- Keep secrets out of Git, test output, documentation, screenshots, and generated artifacts. Never inspect or print credential values.
- Tests and destructive migration commands may use only the local Postgres test service (or in-memory SQLite during the scaffold phase); never target a hostname containing `supabase`.
- Preserve explicit Eloquent `$fillable` lists, escaped Blade output, URL validation, signed owner links, and the HCI tracker/test contract.
- Update `docs/HCI_TRACKER.md` whenever a user-facing behavior changes; use `Verified` only with a named automated test or committed screenshot.
- Log non-obvious implementation choices in `docs/DECISIONS.md` with the date and reason.
- Keep focused changes and run the applicable gates before committing.

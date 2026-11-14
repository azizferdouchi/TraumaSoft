"""Exécution autonome de la collecte quotidienne (utilisé par GitHub Actions)."""

from __future__ import annotations

import argparse
import sys

from worker.workflows.avocats_daily import (
    ANNUAIRE_GRATUIT_MAX_PAGES,
    DEFAULT_RECIPIENT,
    build_csv,
    merge_contacts,
    scrape_annuaire_desavocats,
    scrape_annuaire_gratuit,
    send_report,
)


def main() -> int:
    parser = argparse.ArgumentParser(description="Base de contacts avocats Casablanca")
    parser.add_argument("--recipient", default=DEFAULT_RECIPIENT)
    parser.add_argument("--max-pages", type=int, default=ANNUAIRE_GRATUIT_MAX_PAGES)
    parser.add_argument("--dry-run", action="store_true", help="collecte sans envoi d'email")
    args = parser.parse_args()

    print("Collecte annuaire-gratuit.ma...", flush=True)
    contacts_ag = scrape_annuaire_gratuit(args.max_pages)
    print(f"  -> {len(contacts_ag)} fiches", flush=True)

    print("Collecte annuairedesavocats.ma...", flush=True)
    contacts_adv = scrape_annuaire_desavocats()
    print(f"  -> {len(contacts_adv)} fiches", flush=True)

    contacts = merge_contacts(contacts_ag, contacts_adv)
    with_email = sum(1 for contact in contacts if contact.email)
    csv_content = build_csv(contacts)
    print(f"Base consolidée : {len(contacts)} contacts ({with_email} avec email), CSV {len(csv_content)} octets", flush=True)

    if args.dry_run:
        print("dry-run : email non envoyé", flush=True)
        return 0

    send_report(args.recipient, csv_content, len(contacts), with_email)
    print(f"Email envoyé à {args.recipient}", flush=True)
    return 0


if __name__ == "__main__":
    sys.exit(main())

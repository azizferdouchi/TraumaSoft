"""Workflow quotidien : base de contacts des avocats de Casablanca.

Sources publiques :
- annuaire-gratuit.ma (nom, téléphone, adresse, site web)
- annuairedesavocats.ma (nom, téléphone, email, adresse)

Le résultat consolidé est envoyé en pièce jointe CSV par Gmail (SMTP).
"""

from __future__ import annotations

import csv
import html
import io
import json
import os
import re
import smtplib
import time
from base64 import b64encode
from datetime import datetime, timezone
from email.message import EmailMessage
from typing import Any

import httpx
import mistralai.workflows as workflows
from pydantic import BaseModel

ANNUAIRE_GRATUIT_LIST_URL = (
    "https://www.annuaire-gratuit.ma/recherche/avocat-ville-casablanca-pg{page}"
)
ANNUAIRE_GRATUIT_MAX_PAGES = 200
ANNUAIRE_DESAVOCATS_LIST_URL = "https://www.annuairedesavocats.ma/fr/avocats?city=casablanca"
ANNUAIRE_DESAVOCATS_BASE_URL = "https://www.annuairedesavocats.ma"
DEFAULT_RECIPIENT = "azizferdouchi@gmail.com"
REQUEST_DELAY_SECONDS = 0.3
USER_AGENT = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/120.0 Safari/537.36"
)
EMAIL_RE = re.compile(r"[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}")
SMTP_HOST = "smtp.gmail.com"
SMTP_PORT = 465


class Contact(BaseModel):
    nom: str
    telephone: str = ""
    email: str = ""
    adresse: str = ""
    site_web: str = ""
    source: str = ""


class AvocatsDailyInput(BaseModel):
    recipient_email: str = DEFAULT_RECIPIENT
    dry_run: bool = False
    max_pages: int = ANNUAIRE_GRATUIT_MAX_PAGES


class AvocatsDailyReport(BaseModel):
    total_contacts: int
    with_email: int
    csv_bytes: int
    email_sent: bool
    per_source: dict[str, int]


def fetch_page(url: str) -> str:
    response = httpx.get(url, headers={"User-Agent": USER_AGENT}, timeout=30.0, follow_redirects=True)
    response.raise_for_status()
    return response.text


def _clean(value: str | None) -> str:
    if not value:
        return ""
    return re.sub(r"\s+", " ", html.unescape(value)).strip()


def _normalize_phone_key(phone: str) -> str:
    digits = re.sub(r"\D", "", phone)
    if len(digits) > 9:
        digits = digits[-9:]
    return digits


def _normalize_name_key(name: str) -> str:
    lowered = _clean(name).lower()
    return re.sub(r"[^a-z0-9\u0600-\u06ff]+", "", lowered)


def parse_annuaire_gratuit_page(page_html: str) -> list[Contact]:
    contacts: list[Contact] = []
    for block in re.findall(r'<article itemprop="item"[^>]*>(.*?)</article>', page_html, re.S):
        name_match = re.search(r'<h3 itemprop="name"><a[^>]*>(.*?)</a>', block, re.S)
        if not name_match:
            continue
        street_match = re.search(r'itemprop="streetAddress">(.*?)</span>', block, re.S)
        phone_match = re.search(r'itemprop="telephone"[^>]*>(.*?)</a>', block, re.S)
        site_match = re.search(r'<meta itemprop="sameAs" content="([^"]+)"', block)
        contacts.append(
            Contact(
                nom=_clean(name_match.group(1)),
                telephone=_clean(phone_match.group(1)) if phone_match else "",
                adresse=_clean(street_match.group(1)) if street_match else "",
                site_web=_clean(site_match.group(1)) if site_match else "",
                source="annuaire-gratuit.ma",
            )
        )
    return contacts


def scrape_annuaire_gratuit(max_pages: int = ANNUAIRE_GRATUIT_MAX_PAGES) -> list[Contact]:
    contacts: list[Contact] = []
    previous_names: list[str] | None = None
    for page in range(1, max(1, max_pages) + 1):
        page_html = fetch_page(ANNUAIRE_GRATUIT_LIST_URL.format(page=page))
        page_contacts = parse_annuaire_gratuit_page(page_html)
        if not page_contacts:
            break
        page_names = [c.nom for c in page_contacts]
        if page_names == previous_names:
            break
        previous_names = page_names
        contacts.extend(page_contacts)
        time.sleep(REQUEST_DELAY_SECONDS)
    return contacts


def parse_annuaire_desavocats_listing(page_html: str) -> list[str]:
    paths = re.findall(r'href="(/fr/avocats/[^"]+)"', page_html)
    unique: list[str] = []
    for path in paths:
        if path not in unique and path.rstrip("/") != "/fr/avocats":
            unique.append(path)
    return unique


def parse_attorney_jsonld(page_html: str) -> Contact | None:
    for match in re.findall(
        r'<script type="application/ld\+json">(.*?)</script>', page_html, re.S
    ):
        try:
            data: dict[str, Any] = json.loads(match.strip())
        except json.JSONDecodeError:
            continue
        if data.get("@type") != "Attorney":
            continue
        address = data.get("address") or {}
        email_matches = EMAIL_RE.findall(str(data.get("email") or ""))
        return Contact(
            nom=_clean(str(data.get("name") or "")),
            telephone=_clean(str(data.get("telephone") or "")),
            email=email_matches[0] if email_matches else "",
            adresse=_clean(str(address.get("streetAddress") or "")),
            source="annuairedesavocats.ma",
        )
    return None


def scrape_annuaire_desavocats() -> list[Contact]:
    listing_html = fetch_page(ANNUAIRE_DESAVOCATS_LIST_URL)
    contacts: list[Contact] = []
    for path in parse_annuaire_desavocats_listing(listing_html):
        profile_html = fetch_page(ANNUAIRE_DESAVOCATS_BASE_URL + path)
        contact = parse_attorney_jsonld(profile_html)
        if contact:
            contacts.append(contact)
        time.sleep(REQUEST_DELAY_SECONDS)
    return contacts


def merge_contacts(*groups: list[Contact]) -> list[Contact]:
    merged: list[Contact] = []
    seen: set[str] = set()
    for group in groups:
        for contact in group:
            if not contact.nom:
                continue
            phone_key = _normalize_phone_key(contact.telephone)
            key = f"tel:{phone_key}" if phone_key else f"nom:{_normalize_name_key(contact.nom)}"
            if key in seen:
                continue
            seen.add(key)
            merged.append(contact)
    return merged


def build_csv(contacts: list[Contact]) -> bytes:
    buffer = io.StringIO()
    writer = csv.writer(buffer, delimiter=";", quoting=csv.QUOTE_MINIMAL)
    writer.writerow(["Nom", "Téléphone", "Email", "Adresse", "Site web", "Source"])
    for contact in contacts:
        writer.writerow(
            [contact.nom, contact.telephone, contact.email, contact.adresse, contact.site_web, contact.source]
        )
    return buffer.getvalue().encode("utf-8-sig")


def send_report(
    recipient_email: str,
    csv_content: bytes,
    total_contacts: int,
    with_email: int,
) -> None:
    smtp_user = os.environ["GMAIL_SMTP_USER"]
    smtp_password = os.environ["GMAIL_SMTP_APP_PASSWORD"]

    message = EmailMessage()
    message["From"] = smtp_user
    message["To"] = recipient_email
    message["Subject"] = (
        f"Base de contacts avocats Casablanca — {datetime.now(timezone.utc):%d/%m/%Y}"
    )
    message.set_content(
        "Bonjour,\n\n"
        f"Voici la base de contacts consolidée des avocats de Casablanca.\n\n"
        f"- Contacts au total : {total_contacts}\n"
        f"- Contacts avec email : {with_email}\n\n"
        "Données issues d'annuaires publics. La base est en pièce jointe (CSV).\n"
    )
    message.add_attachment(
        csv_content,
        maintype="text",
        subtype="csv",
        filename=f"avocats_casablanca_{datetime.now(timezone.utc):%Y-%m-%d}.csv",
    )

    with smtplib.SMTP_SSL(SMTP_HOST, SMTP_PORT) as server:
        server.login(smtp_user, smtp_password)
        server.send_message(message)


@workflows.activity()
async def scrape_annuaire_gratuit_activity(max_pages: int) -> list[Contact]:
    return scrape_annuaire_gratuit(max_pages)


@workflows.activity()
async def scrape_annuaire_desavocats_activity() -> list[Contact]:
    return scrape_annuaire_desavocats()


@workflows.activity()
async def build_and_send_activity(
    contacts_annuaire_gratuit: list[Contact],
    contacts_annuaire_desavocats: list[Contact],
    recipient_email: str,
    dry_run: bool,
) -> AvocatsDailyReport:
    contacts = merge_contacts(contacts_annuaire_gratuit, contacts_annuaire_desavocats)
    csv_content = build_csv(contacts)
    with_email = sum(1 for contact in contacts if contact.email)
    per_source: dict[str, int] = {}
    for contact in contacts:
        per_source[contact.source] = per_source.get(contact.source, 0) + 1
    email_sent = False
    if not dry_run:
        send_report(recipient_email, csv_content, len(contacts), with_email)
        email_sent = True
    return AvocatsDailyReport(
        total_contacts=len(contacts),
        with_email=with_email,
        csv_bytes=len(csv_content),
        email_sent=email_sent,
        per_source=per_source,
    )


@workflows.workflow.define(
    name="avocats-casablanca-daily",
    workflow_display_name="Base de contacts avocats Casablanca",
    workflow_description=(
        "Collecte quotidienne des contacts publics des avocats de Casablanca "
        "et envoi du fichier CSV par email."
    ),
)
class AvocatsCasablancaDailyWorkflow:
    @workflows.workflow.entrypoint
    async def run(self, input: AvocatsDailyInput) -> AvocatsDailyReport:
        contacts_annuaire_gratuit = await scrape_annuaire_gratuit_activity(input.max_pages)
        contacts_annuaire_desavocats = await scrape_annuaire_desavocats_activity()
        return await build_and_send_activity(
            contacts_annuaire_gratuit,
            contacts_annuaire_desavocats,
            input.recipient_email,
            input.dry_run,
        )

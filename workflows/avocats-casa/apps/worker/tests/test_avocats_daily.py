import csv
import io

from worker.workflows import avocats_daily as mod


def test_parse_annuaire_gratuit_page_extracts_contacts():
    page_html = """
    <li><article itemprop="item" itemscope itemtype="https://schema.org/LocalBusiness">
    <h3 itemprop="name"><a href="/avocats/x.html" title="t">Cabinet d&#039;avocats Jaouad Benaissi</a></h3>
    <span itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
    <span itemprop="streetAddress">122 bd d&#039;anfa, casablanca 20250</span></span>
    <span class="ag_listing_phone"><a itemprop="telephone" href="tel:0610072121">06 10 07 21 21</a></span>
    <meta itemprop="sameAs" content="https://www.cabinet-benaissi.ma/" />
    </article></li>
    """
    contacts = mod.parse_annuaire_gratuit_page(page_html)
    assert len(contacts) == 1
    contact = contacts[0]
    assert contact.nom == "Cabinet d'avocats Jaouad Benaissi"
    assert contact.telephone == "06 10 07 21 21"
    assert contact.adresse == "122 bd d'anfa, casablanca 20250"
    assert contact.site_web == "https://www.cabinet-benaissi.ma/"
    assert contact.source == "annuaire-gratuit.ma"


def test_parse_annuaire_gratuit_page_skips_incomplete_blocks():
    page_html = "<article itemprop=\"item\"><p>pas de nom</p></article>"
    assert mod.parse_annuaire_gratuit_page(page_html) == []


def test_parse_attorney_jsonld_extracts_full_profile():
    page_html = """
    <script type="application/ld+json">{"@context":"https://schema.org","@type":"Attorney",
    "name":"Ma\\u00eetre Rachid Boumhali","telephone":"0663327887",
    "email":"rachid.boumhali@gmail.com",
    "address":{"@type":"PostalAddress","streetAddress":"395 boulevard","addressLocality":"casablanca"}}
    </script>
    """
    contact = mod.parse_attorney_jsonld(page_html)
    assert contact is not None
    assert contact.nom == "Maître Rachid Boumhali"
    assert contact.telephone == "0663327887"
    assert contact.email == "rachid.boumhali@gmail.com"
    assert contact.adresse == "395 boulevard"
    assert contact.source == "annuairedesavocats.ma"


def test_parse_attorney_jsonld_returns_none_without_attorney():
    assert mod.parse_attorney_jsonld('<script type="application/ld+json">{"@type":"WebSite"}</script>') is None


def test_parse_listing_paths_dedupes_and_skips_index():
    page_html = (
        '<a href="/fr/avocats/maitre-a-casablanca">A</a>'
        '<a href="/fr/avocats/maitre-a-casablanca">A</a>'
        '<a href="/fr/avocats">index</a>'
    )
    assert mod.parse_annuaire_desavocats_listing(page_html) == ["/fr/avocats/maitre-a-casablanca"]


def test_merge_contacts_dedupes_by_phone_then_name():
    group_a = [
        mod.Contact(nom="A", telephone="05 22 00 00 00"),
        mod.Contact(nom="B", telephone=""),
    ]
    group_b = [
        mod.Contact(nom="A bis", telephone="+212 522 000 000"),
        mod.Contact(nom="B", telephone="", email="b@example.com"),
    ]
    merged = mod.merge_contacts(group_a, group_b)
    assert len(merged) == 2
    assert merged[0].nom == "A"
    assert merged[1].nom == "B"


def test_build_csv_has_headers_and_rows():
    contacts = [
        mod.Contact(nom="Zoé", telephone="0600000000", email="z@example.com", adresse="1 rue", source="s"),
    ]
    content = mod.build_csv(contacts).decode("utf-8-sig")
    rows = list(csv.reader(io.StringIO(content), delimiter=";"))
    assert rows[0] == ["Nom", "Téléphone", "Email", "Adresse", "Site web", "Source"]
    assert rows[1][:4] == ["Zoé", "0600000000", "z@example.com", "1 rue"]


def test_send_report_sends_message_with_attachment(monkeypatch):
    sent = {}

    class FakeServer:
        def __init__(self, host, port):
            sent["host"] = host
            sent["port"] = port

        def __enter__(self):
            return self

        def __exit__(self, *args):
            return False

        def login(self, user, password):
            sent["credentials"] = (user, password)

        def send_message(self, message):
            sent["message"] = message

    monkeypatch.setattr(mod.smtplib, "SMTP_SSL", FakeServer)
    monkeypatch.setenv("GMAIL_SMTP_USER", "azizferdouchi@gmail.com")
    monkeypatch.setenv("GMAIL_SMTP_APP_PASSWORD", "secret-app-password")

    mod.send_report("azizferdouchi@gmail.com", b"csv", 2, 1)

    assert sent["host"] == mod.SMTP_HOST
    assert sent["port"] == mod.SMTP_PORT
    assert sent["credentials"] == ("azizferdouchi@gmail.com", "secret-app-password")
    message = sent["message"]
    assert message["To"] == "azizferdouchi@gmail.com"
    attachments = [part for part in message.walk() if part.get_content_maintype() == "text" and part.get_filename()]
    assert len(attachments) == 1
    assert attachments[0].get_filename().startswith("avocats_casablanca_")


def test_workflow_registered():
    assert mod.AvocatsCasablancaDailyWorkflow is not None

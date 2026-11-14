# Base de contacts avocats Casablanca (workflow quotidien)

Workflow [Mistral Workflows](https://docs.mistral.ai/studio/workflows/) qui collecte chaque jour les
contacts publics des avocats de Casablanca et envoie la base consolidée en CSV à
`azizferdouchi@gmail.com`.

## Fonctionnement

1. **Collecte** sur deux annuaires publics :
   - `annuaire-gratuit.ma` : nom, téléphone, adresse, site web (pagination jusqu'à 200 pages).
   - `annuairedesavocats.ma` : nom, téléphone, email, adresse (profils JSON-LD `Attorney`).
2. **Fusion et déduplication** par numéro de téléphone (normalisé) puis par nom.
3. **Export CSV** (`;`, UTF-8 BOM, ouvrable dans Excel) : `Nom;Téléphone;Email;Adresse;Site web;Source`.
4. **Envoi par email** via Gmail SMTP (pièce jointe CSV + résumé dans le corps).

## Configuration requise

Deux secrets d'espace de travail Mistral (https://console.mistral.ai/secrets), liés au déploiement :

| Variable | Valeur |
|---|---|
| `GMAIL_SMTP_USER` | `azizferdouchi@gmail.com` |
| `GMAIL_SMTP_APP_PASSWORD` | mot de passe d'application Google (https://myaccount.google.com/apppasswords, 2FA requise) |

## Entrées du workflow `avocats-casablanca-daily`

```json
{
  "recipient_email": "azizferdouchi@gmail.com",
  "dry_run": false,
  "max_pages": 200
}
```

- `dry_run: true` : collecte et génère le CSV sans envoyer d'email (utile pour tester).

## Développement local

```bash
uv sync --dev
uv run pytest
```

Worker local : `uv run python -m worker.entrypoints.worker` (nécessite `MISTRAL_API_KEY` dans `.env`).

## Déploiement

Déploiement managé : Dockerfile `deploy/docker/Dockerfile.worker`, contexte de build = racine de ce
dossier. Planification quotidienne à 08h00 (Africa/Casablanca) créée via l'API des schedules.

## Avertissement légal

Les données proviennent d'annuaires publics et sont destinées à un usage de prospection
professionnelle. Toute utilisation doit respecter la loi marocaine 09-08 (protection des données
personnelles, CNDP) : pas de démarchage abusif, respect des demandes d'opposition des personnes
contactées.

# SIMPEG‑RS

A **PHP + SQLite** HR management web application for RSUD Mimika.

## Prerequisites
- Termux (or any Linux) with **PHP 8** installed.
- SQLite (bundled with PHP).
- Optional: Git for pulling updates.

## Backend (PHP server)
The app runs on PHP's built‑in server. From the project root (`~/simpeg-rs`):

```bash
cd ~/simpeg-rs
# Serve on all interfaces, port 8000
php -S 0.0.0.0:8000
```

- Use `0.0.0.0` so the app is reachable from other devices on the LAN.
- Find your LAN IP with `ip addr show wlan0` (e.g., `10.46.156.105`).
- Open `http://<LAN‑IP>:8000` in a browser.

### Run in background (development)
```bash
php -S 0.0.0.0:8000 &
```

## Frontend
The frontend uses **Bootstrap 5** and is served directly by the PHP server; no separate build step is required.

- Edit UI files (`*.php`, `*.html`, `css/*.css`, `js/*.js`).
- Refresh the browser to see changes.

## Database
SQLite file is located at `data/database.sqlite` (as defined in `config.php`).

- Seed dummy data:
```bash
php seed_dummy.php
```
- You can also inspect the DB with:
```bash
sqlite3 data/database.sqlite
```

## Deployment to Hugging Face Spaces
The repository is configured for HF Spaces. Pushing to the `origin` remote restarts the Space automatically.

```bash
git add . && git commit -m "Your message"
# Replace <HF_TOKEN> with the token from reference_hf_token.md
git push https://t0536844-sketch:<HF_TOKEN>@huggingface.co/spaces/Timsupport/simpeg-rs.git
```

## Sharing the URL
Never share a `localhost` address. Use the LAN IP (e.g., `http://10.46.156.105:8000`) so other devices can access the app.

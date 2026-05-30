# Dashboard App (Dockerized)

This repository contains the Laravel Dashboard application, fully dockerized for a production environment. It uses a highly-optimized, memory-efficient architecture designed to run on a 2GB RAM EC2 server.

---

## 🏗 Architecture Overview
The application runs using 4 lightweight Docker containers orchestrated by `docker-compose.yml`:
1. **`app` (PHP-FPM 8.3)**: Handles all Laravel backend logic. The `vendor` directory and PHP code are **baked into the image** during the build process to protect against file overwrites.
2. **`web` (Nginx)**: Serves static assets directly from the `./public` directory on the host and proxies PHP requests to the `app` container.
3. **`db` (MySQL 8)**: The database server, with persistent data stored in the `dbdata` Docker volume.
4. **`certbot`**: Automatically manages and renews Let's Encrypt SSL certificates in the background.

---

## 💻 Local Development Workflow

When you are developing on your local machine (e.g., your Acer PC), you must compile your frontend assets locally before pushing to production. The production server **does not** compile assets to save memory.

1. Make your code changes (PHP, Blade, CSS, JS).
2. If you modified CSS or JS, build the Vite assets locally:
   ```bash
   npm run build
   ```
3. Commit everything (including the newly generated `public/build` folder):
   ```bash
   git add .
   git commit -m "Your commit message"
   git push
   ```

---

## 🚀 Production Deployment (The Golden Rule)

If you step away from this project for a few months, **this is the only section you need to remember.**

Whenever you push new code to GitHub and want to update the live EC2 server, SSH into the server and run these exact commands:

```bash
cd ~/dashboard_app
git pull
docker compose up --build -d
```

### Why this works:
- `git pull` instantly brings your pre-compiled CSS/JS into the server's `./public` directory for Nginx to serve.
- `docker compose up --build -d` quickly bakes your newest PHP files and the latest Vite `manifest.json` into the `app` container.
- **NEVER skip the `--build` flag!** If you skip it, Laravel will read an old `manifest.json` and generate HTML requesting old CSS/JS files, causing Nginx to throw 404 errors!

---

## 🔐 SSL Certificate Setup (Fresh Server Only)

If you ever migrate to a brand new EC2 instance, you will encounter the "Chicken-and-Egg" Let's Encrypt problem (Nginx won't start without certificates, but Certbot can't get certificates without Nginx running).

To solve this on a fresh server:
1. Clone the repo and setup your `.env`.
2. Ensure `docker/nginx/app.conf` and `init-letsencrypt.sh` have your correct domain names.
3. Run the automated script:
   ```bash
   sudo ./init-letsencrypt.sh
   ```
This script generates dummy certificates to let Nginx boot up, asks Let's Encrypt for the real certificates, and then smoothly reloads Nginx.

---

## 💾 Database Restoration

If you ever need to restore the `dashboard` database from a massive `--all-databases` `.sql.gz` dump file, do not unzip it on the server (it will take up too much space).

Run this command to stream it directly into the MySQL container, using `-f` to bypass the `--one-database` skip errors:

```bash
zcat all_databases_backup.sql.gz | docker compose exec -T db sh -c 'mysql -f -uroot -p"$MYSQL_ROOT_PASSWORD" --one-database dashboard'
```

---

## 🐛 Troubleshooting

- **502 Bad Gateway:** This usually means the `app` container crashed. Check its logs using `docker compose logs app`. In the past, this happened if the `vendor/` directory was overwritten by a bad volume mount.
- **CSS/JS is completely broken (404s):** You forgot to run `--build` on the server! Laravel is asking for old asset hashes. Run `docker compose up --build -d` to fix the manifest desync.
- **Database Connection Refused:** The database container (`db`) takes about 10-15 seconds to fully initialize on a fresh boot. The `entrypoint.sh` script automatically pauses Laravel until MySQL is ready. Just wait a few seconds and refresh.

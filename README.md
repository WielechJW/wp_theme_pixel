# Pixel WordPress Theme

A custom WordPress theme and companion Gutenberg block plugin created for a Polish 3D printing studio. The repository demonstrates theme development, editable content models, WooCommerce integration, responsive SCSS architecture, and an interactive Three.js hero.

## Highlights

- Custom responsive WordPress theme with dedicated home, about, contact, projects, blog, search, archive, and 404 templates
- WooCommerce catalogue, product, cart, checkout, and account styling
- Custom Projects and Testimonials post types exposed to the REST API
- Project categories and tags for structured portfolio content
- Custom Gutenberg blocks for projects, testimonials, calls to action, offers, process steps, and differentiators
- Three.js/GLTF hero block with separate editor and frontend assets
- SCSS organized into settings, tools, base, layout, and component layers
- Docker Compose environment for WordPress, MariaDB, and phpMyAdmin

## Tech stack

- WordPress and PHP
- Gutenberg Block API
- WooCommerce
- JavaScript and Three.js
- SCSS / CSS
- Docker Compose and MariaDB

## Repository structure

```text
themes/pixel/          # Theme templates, content models, assets, and blocks
plugins/pnw-hero-3d/   # Companion Gutenberg blocks and Three.js hero
docker-compose.yml     # Local WordPress, database, and phpMyAdmin services
```

## Local setup

Requirements: Docker with Docker Compose, plus Node.js if you want to rebuild the theme styles.

1. Start the local services:

   ```bash
   docker compose up -d
   ```

2. Complete the WordPress installation at [http://localhost:8050](http://localhost:8050).
3. Copy `themes/pixel` to `wordpress/wp-content/themes/pixel`.
4. Copy `plugins/pnw-hero-3d` to `wordpress/wp-content/plugins/pnw-hero-3d`.
5. Activate the **pixel** theme and **PNW Hero 3D** plugin in WordPress.
6. Install and activate WooCommerce if the shop templates are needed.

phpMyAdmin is available at [http://localhost:8060](http://localhost:8060) in the local environment.

### Rebuild theme styles

```bash
cd themes/pixel
npm install
npm run build:css
```

Use `npm run watch:css` during active styling work.

## Notes

The repository contains the custom theme and plugin source. WordPress core, uploaded media, and database content are intentionally excluded from version control.


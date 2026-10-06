# Northsmith — Custom WooCommerce Storefront

**Author:** Keshav

Northsmith is a fully customised English-language WooCommerce storefront. It began as a fork/rebrand of the original [FurniCraft demo](https://github.com/b4d5shub/FurniCraft) by b4d5shub and was transformed into a premium, minimal, editorial furniture retailer targeting the US market.

## What Was Changed

- **Full rebrand**: Renamed to "Northsmith" with a refined palette (charcoal `#1A1A1A`, brass accent `#C89B3C`, off-white `#FAFAF8`, muted `#6B6B6B`, border `#E5E5E0`).
- **Custom child theme**: All custom work lives in `wp-content/themes/northsmith-child/`, built on top of the premium Sober parent theme. Created custom styles, logo handling, typography (Inter + Fraunces), and header layout.
- **Internationalisation & currency**: English (en_US), USD pricing, and US-focused defaults.
- **Custom WooCommerce features**: Craftsmanship product tab on product pages, Delivery Instructions checkout field (saved to order meta and visible in admin), and a bulk quantity discount (10% off when ordering 5+ units).
- **SEO & structured data**: Meta descriptions, Open Graph/Twitter tags, canonical URLs, and JSON-LD (Organisation + Product schema) added via custom functions.
- **Templates & UX**: Custom search, archive and index templates to ensure correct rendering. Header, shop grid, product, cart/checkout and footer styling refined.
- **Security hardening**: Additional `.htaccess` rules to block public access to sensitive paths (`database/`, `.git/`, `.kilo/`, `docker-compose.yml`).
- **Docker development environment**: Local stack (WordPress 6.7/PHP 8.2, MariaDB 10.11, phpMyAdmin) for reproducible setup.
- **Brand assets**: Custom SVG/PNG wordmarks under `wp-content/themes/northsmith-child/assets/brand/`.

## Attribution

- **Base demo**: [FurniCraft](https://github.com/b4d5shub/FurniCraft) by b4d5shub — used as the starting point.
- **Parent theme**: [Sober](https://uix.store/) by UIX Themes — premium theme included under the terms of the original demo/purchase.
- **Commercial plugins** (bundled): WooCommerce, WPBakery Page Builder, Slider Revolution, Rank Math SEO, Contact Form 7, Sober Addons, and other bundled plugins — each under their respective licences.

## What Is Original

The original custom code in this repository is restricted to:
- `wp-content/themes/northsmith-child/` (CSS, PHP functions, templates, assets)
- Custom Docker configuration and supporting scripts
- Project-specific documentation (excluding the bundled demo content)

All third-party software remains the property of their respective authors and is licensed accordingly.

## Quick Start

```bash
# Clone repository
git clone https://github.com/[your-github-username]/northsmith.git
cd northsmith

# Start containers
docker compose up -d

# Import database (first time only)
docker compose exec -T db mysql -uroot -prootpass wordpress_db < database/wordpress_db.sql

# Access site
open http://localhost:8080
# phpMyAdmin: http://localhost:8081
```

## Notes

- Database credentials (local demo): `wordpress_db` / `wpuser` / `wppass` (host `db:3306`).
- This project is intended as a portfolio demonstration of WordPress theme customisation and WooCommerce development.

## License

Original demo code is distributed under its own terms. My custom work is released under the MIT License — see [LICENSE](LICENSE) for details. See [LICENSE-THIRD-PARTY.md](LICENSE-THIRD-PARTY.md) for third-party attributions.

# Migration notes — Northsmith rebrand

Everything here needs `docker compose exec`. Run from the repo root with the
stack up.

All SQL is written to be pasted through a heredoc on **stdin** rather than
`mysql -e`, because several values contain double quotes (the serialised
`woocommerce_permalinks` array) that fight with shell quoting.

```bash
cd ~/FurniCraft
docker compose exec -T db mysql -uroot -prootpass wordpress_db <<'SQL'
...statements...
SQL
```

`-T` matters: without it `docker compose exec` allocates a TTY and mangles
stdin.

---

## 1. Broken banner links (6 live on the homepage)

Sober banners store their target as a URL-encoded WPBakery link attribute.
The stored value has a stray `.` before `%2F`:

```
link="url:.%2Fshop%2F|title:Boutique"
```

`vc_parse_multi_attribute()` (`js_composer/include/helpers/helpers.php:955`)
`rawurldecode()`s that to `./shop/`, and `vc_templates/sober_banner.php:96`
passes it to `esc_url()`, which prepends `http://` and treats `.` as the host —
producing `href="http://./shop/"`.

This is **data**, not a theme bug. No template edit fixes it. 19 occurrences
live in `wp_posts.post_content`; nothing outside `wp_posts` is affected.

```sql
UPDATE wp_posts
   SET post_content = REPLACE(post_content, 'url:.%2F', 'url:%2F')
 WHERE post_content LIKE '%url:.%2F%';

UPDATE wp_posts
   SET post_content = REPLACE(post_content,
     'url:http%3A%2F%2Flocalhost%2Fwordpress', 'url:%2F');

UPDATE wp_posts
   SET post_content = REPLACE(post_content,
     'url:http%3A%2F%2Fde.v%2Fsober', 'url:%2F');
```

Idempotent — the replacement result no longer matches the search pattern.
`url:%2Fshop%2F|title:Boutique` already exists in the dump in the correct form,
which confirms leading-slash is what WPBakery expects.

## 2. Rank Math internal-link index

Seven rows hold `http://./shop/` and friends (`taxonomy = 366`, i.e. the demo
page). This table is a rebuildable cache, not source data.

```sql
DELETE FROM wp_rank_math_internal_links;
```

Rank Math repopulates it on the next scan.

## 3. French → English

**Decide first:** `woocommerce_default_country = MA:maagd` (Agadir, Morocco)
and `woocommerce_currency = MAD`. French may be deliberate for that market.
This flips the *storefront* language customers see.

### 3a. Language and leftover French terms

```sql
UPDATE wp_options SET option_value = 'en_US' WHERE option_name = 'WPLANG';

UPDATE wp_posts SET post_title = 'Checkout',        post_name = 'checkout'
 WHERE ID = 9;
UPDATE wp_posts SET post_title = 'Refunds and Returns', post_name = 'refunds-returns'
 WHERE ID = 11;

-- Terms 15 ('Non classé') and 43 ('Uncategorized') both have count = 0,
-- so nothing is orphaned; the relationship deletes are belt-and-braces.
DELETE FROM wp_term_relationships WHERE term_taxonomy_id IN (15, 43);
DELETE FROM wp_termmeta            WHERE term_id            IN (15, 43);
DELETE FROM wp_term_taxonomy       WHERE term_taxonomy_id  IN (15, 43);
DELETE FROM wp_terms               WHERE term_id            IN (15, 43);
```

Also set **Settings → General → Site Language → English (United States)** in
the admin. That is the only reliable way to drop the loaded French translation
set.

### 3b. WooCommerce permalink bases — use the admin, not SQL

**WooCommerce → Settings → Products** → *Product permalink base*,
*Category permalink base*, *Tag permalink base* → `product`,
`product-category`, `product-tag` → **Save**.

WooCommerce writes the serialised array and flushes rewrite rules itself. Doing
it by hand risks a corrupt serialised value. If you must use SQL:

```sql
UPDATE wp_options
   SET option_value = 'a:5:{s:12:"product_base";s:7:"product";s:13:"category_base";s:15:"product-category";s:8:"tag_base";s:11:"product-tag";s:14:"attribute_base";s:0:"";s:22:"use_verbose_page_rules";b:0;}'
 WHERE option_name = 'woocommerce_permalinks';
```

Note the `s:N:` length prefixes must match the string lengths exactly.

**Flushing rewrite rules:** the `wordpress:6.7-php8.2-apache` image ships no
WP-CLI, so `wp rewrite flush` will not work. Use **Settings → Permalinks →
Save** in the admin — that flushes rules and regenerates `.htaccess`.

> `Save changes` on the Permalinks screen will rewrite `.htaccess` between the
> `# BEGIN WordPress` / `# END WordPress` markers only. The hardening block
> lives outside those markers and survives.

## 4. Before deleting anything: check the duplicate cart pages

Both classic and Gutenberg-block WooCommerce pages exist, which is why
`/cart/`, `/panier/`, `/my-account/` and `/mon-compte/` **all** return 200.
Resolve which `page_id` backs what before touching page slugs:

```sql
SELECT ID, post_title, post_name, post_status
  FROM wp_posts
 WHERE post_type = 'page'
   AND ID IN (8, 9, 11, 12, 284, 299, 300)
 ORDER BY ID;
```

`woocommerce_cart_page_id`, `woocommerce_checkout_page_id`,
`woocommerce_myaccount_page_id`, `woocommerce_shop_page_id` and
`woocommerce_refund_returns_page_id` point at the *authoritative* pages. Any
other page row in that range is a superseded block-based duplicate — but do not
delete anything until you have confirmed which are referenced.

---

## Verification

```bash
# expect 0
curl -s http://localhost:8080 | grep -c 'href="http://\./'

# expect lang="en-US"
curl -s http://localhost:8080 | grep -oE '<html[^>]*lang="[^"]*"'

# expect 200 for the English base, 404 for the French one
curl -so /dev/null -w '%{http_code}\n' http://localhost:8080/product-category/accessories/
curl -so /dev/null -w '%{http_code}\n' http://localhost:8080/categorie-produit/accessories/

# options
docker compose exec -T db mysql -uroot -prootpass wordpress_db -e "
SELECT option_name, option_value FROM wp_options
 WHERE option_name IN ('blogname','stylesheet','template','siteurl','home','WPLANG');"
```

Expected: `blogname` = Northsmith, `stylesheet` = northsmith-child,
`template` = sober, `siteurl`/`home` = `http://localhost:8080`,
`WPLANG` = en_US.

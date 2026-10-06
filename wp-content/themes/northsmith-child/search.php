<?php
/**
 * Search results — Northsmith child theme.
 * Renders product cards directly, bypassing WooCommerce's template loader.
 */

get_header(); ?>

<main class="site-main search-results">
  <div class="container" style="max-width: 1280px; margin: 0 auto; padding: 0 32px;">

    <header class="search-header" style="text-align:center; margin: 60px 0 40px;">
      <p style="font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: #6B6B6B; margin: 0;">Search results</p>
      <h1 style="font-family: Fraunces, Georgia, serif; font-size: 40px; font-weight: 500; margin: 8px 0 0;">
        <?php echo esc_html( get_search_query() ); ?>
      </h1>
    </header>

    <?php if ( have_posts() ) : ?>

      <div class="product-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 32px; margin-bottom: 60px;">
        <?php while ( have_posts() ) : the_post(); ?>
          <?php
          global $product;
          $product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : null;
          if ( ! $product ) { continue; }
          ?>
          <div class="product-card" style="text-align:center;">
            <a href="<?php the_permalink(); ?>" style="display:block; text-decoration:none;">
              <div style="background:#F4F4F0; aspect-ratio: 1/1; overflow:hidden; margin-bottom:16px;">
                <?php echo $product->get_image( 'woocommerce_thumbnail', array( 'style' => 'width:100%; height:100%; object-fit:cover; display:block;' ) ); ?>
              </div>
            </a>
            <h3 style="font-family: Fraunces, Georgia, serif; font-size: 15px; font-weight: 500; margin: 0 0 6px; letter-spacing: -0.01em;">
              <a href="<?php the_permalink(); ?>" style="text-decoration:none; color: inherit;"><?php the_title(); ?></a>
            </h3>
            <p style="font-size: 14px; font-weight: 500; margin: 0; color: #1A1A1A;"><?php echo $product->get_price_html(); ?></p>
          </div>
        <?php endwhile; ?>
      </div>

      <div class="search-pagination" style="margin: 48px 0; text-align:center;">
        <?php
        the_posts_pagination( array(
          'prev_text' => '&larr; Previous',
          'next_text' => 'Next &rarr;',
          'mid_size'  => 1,
        ) );
        ?>
      </div>

    <?php else : ?>

      <div class="no-results" style="text-align:center; padding: 80px 20px;">
        <h2 style="font-family: Fraunces, Georgia, serif; font-size: 28px; font-weight: 500;">No results found</h2>
        <p style="color: #6B6B6B; margin: 12px 0 28px;">Try a different search term, or browse our collections.</p>
        <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" style="display: inline-block; background: #1A1A1A; color: #FAFAF8; padding: 14px 32px; text-decoration: none; text-transform: uppercase; font-size: 12px; letter-spacing: 0.08em;">
          Browse shop
        </a>
      </div>

    <?php endif; ?>

  </div>
</main>

<?php get_footer(); ?>

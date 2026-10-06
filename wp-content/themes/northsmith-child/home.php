<?php get_header(); ?>

<main class="site-main posts-archive" style="max-width: 900px; margin: 0 auto; padding: 80px 32px;">
  <header style="text-align:center; margin-bottom: 60px;">
    <p style="font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: #6B6B6B; margin: 0;">Journal</p>
    <h1 style="font-family: Fraunces, Georgia, serif; font-size: 40px; font-weight: 500; margin: 8px 0 0;">
      <?php echo esc_html( get_the_title( get_option( 'page_for_posts' ) ) ?: 'Journal' ); ?>
    </h1>
  </header>

  <?php if ( have_posts() ) : ?>
    <div class="post-list">
      <?php while ( have_posts() ) : the_post(); ?>
        <article style="margin-bottom: 56px; padding-bottom: 56px; border-bottom: 1px solid #E5E5E0;">
          <p style="font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: #6B6B6B; margin: 0 0 8px;">
            <?php echo esc_html( get_the_date() ); ?>
          </p>
          <h2 style="font-family: Fraunces, Georgia, serif; font-size: 28px; font-weight: 500; margin: 0 0 16px;">
            <a href="<?php the_permalink(); ?>" style="text-decoration:none; color:#1A1A1A;"><?php the_title(); ?></a>
          </h2>
          <div style="color: #23232c; line-height: 1.7;">
            <?php the_excerpt(); ?>
          </div>
          <a href="<?php the_permalink(); ?>" style="display:inline-block; margin-top: 16px; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #1A1A1A; border-bottom: 1px solid #1A1A1A; text-decoration: none; padding-bottom: 2px;">
            Read more
          </a>
        </article>
      <?php endwhile; ?>
    </div>

    <div style="text-align:center; margin-top: 60px;">
      <?php the_posts_pagination( array(
        'prev_text' => '&larr; Previous',
        'next_text' => 'Next &rarr;',
        'mid_size'  => 1,
      ) ); ?>
    </div>
  <?php else : ?>
    <div style="text-align: center; padding: 80px 20px;">
      <h2 style="font-family: Fraunces, Georgia, serif; font-size: 28px;">No journal entries yet</h2>
      <p style="color: #6B6B6B; margin: 12px 0 28px;">Check back soon.</p>
    </div>
  <?php endif; ?>
</main>

<?php get_footer(); ?>

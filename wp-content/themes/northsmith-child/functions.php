<?php
/**
 * Northsmith functions and definitions.
 *
 * @link    https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Northsmith Child
 */

add_action( 'wp_enqueue_scripts', 'northsmith_child_enqueue_fonts',   5 );
add_action( 'wp_enqueue_scripts', 'northsmith_child_enqueue_scripts', 20 );

/**
 * Enqueues Google Fonts for the child theme.
 *
 * @return void
 */
function northsmith_child_enqueue_fonts() {
    wp_enqueue_style(
        'northsmith-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Fraunces:wght@400;500;600&display=swap',
        array(),
        null
    );
}

/**
 * Enqueues stylesheets and scripts of the child theme.
 *
 * @return void
 */
function northsmith_child_enqueue_scripts() {
    if ( is_rtl() ) {
        wp_enqueue_style( 'sober-rtl', get_template_directory_uri() . '/rtl.css' );
    }

    wp_enqueue_style( 'northsmith-child', get_stylesheet_uri(), array( 'northsmith-fonts' ) );
}

/**
 * Override Sober logo with Northsmith SVG.
 *
 * @param mixed  $value The option value.
 * @param string $name  The option name.
 *
 * @return mixed
 */
function northsmith_child_override_logo( $value, $name ) {
    $theme_root = get_stylesheet_directory_uri();

    switch ( $name ) {
        case 'logo_type':
            return 'image';

        case 'logo':
            return $theme_root . '/assets/brand/northsmith.svg';

        case 'logo_light':
            return $theme_root . '/assets/brand/northsmith-light.svg';
    }

    return $value;
}
add_filter( 'sober_get_option', 'northsmith_child_override_logo', 20, 2 );

/**
 * Hide Sober's default site title/description to avoid duplicate branding.
 *
 * @return void
 */
function northsmith_child_logo_styles() {
    ?>
    <style type="text/css">
        .site-branding .site-title,
        .site-branding .site-description {
            display: none !important;
            visibility: hidden !important;
        }

        .site-branding .logo img {
            height: 40px;
            max-height: 40px;
            width: auto;
            max-width: 100%;
        }

        @media (min-width: 992px) {
            .site-branding .logo img {
                height: 48px;
                max-height: 48px;
            }
        }

        @media (max-width: 767px) {
            .site-branding .logo img {
                height: 36px;
                max-height: 36px;
            }
        }
    </style>
    <?php
}
add_action( 'wp_head', 'northsmith_child_logo_styles', 15 );

/**
 * Add custom product tab "Craftsmanship".
 *
 * @param array $tabs Existing product tabs.
 * @return array Modified product tabs.
 */
function northsmith_child_craftsmanship_tab( $tabs ) {
    $tabs['craftsmanship'] = array(
        'title'    => __( 'Craftsmanship', 'northsmith-child' ),
        'priority' => 50,
        'callback' => 'northsmith_child_craftsmanship_tab_content',
    );

    return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'northsmith_child_craftsmanship_tab' );

/**
 * Content for the Craftsmanship tab.
 *
 * @return void
 */
function northsmith_child_craftsmanship_tab_content() {
    echo '<h3>' . esc_html__( 'Craftsmanship', 'northsmith-child' ) . '</h3>';
    echo '<p>' . esc_html__( 'Hand-finished in small batches. Sustainably sourced hardwoods. Built to last.', 'northsmith-child' ) . '</p>';
}


/**
 * Add Delivery Instructions field to checkout.
 *
 * @param array $fields Checkout fields.
 * @return array Modified checkout fields.
 */
function northsmith_child_checkout_delivery_instructions( $fields ) {
    $fields['shipping']['delivery_instructions'] = array(
        'type'        => 'textarea',
        'label'       => __( 'Delivery Instructions (optional)', 'northsmith-child' ),
        'placeholder' => __( 'e.g. leave at back door, ring doorbell twice', 'northsmith-child' ),
        'required'    => false,
        'class'       => array( 'form-row-wide' ),
        'priority'    => 115,
    );

    return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'northsmith_child_checkout_delivery_instructions' );

/**
 * Save Delivery Instructions to order meta.
 *
 * @param int $order_id Order ID.
 * @return void
 */
function northsmith_child_save_delivery_instructions( $order_id ) {
    if ( isset( $_POST['delivery_instructions'] ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        update_post_meta( $order_id, '_delivery_instructions', sanitize_textarea_field( wp_unslash( $_POST['delivery_instructions'] ) ) );
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'northsmith_child_save_delivery_instructions' );

/**
 * Display Delivery Instructions in admin order details.
 *
 * @param WC_Order $order Order object.
 * @return void
 */
function northsmith_child_display_delivery_instructions_admin( $order ) {
    $delivery_instructions = get_post_meta( $order->get_id(), '_delivery_instructions', true );

    if ( $delivery_instructions ) {
        echo '<h3>' . esc_html__( 'Delivery Instructions', 'northsmith-child' ) . '</h3>';
        echo '<p>' . esc_html( $delivery_instructions ) . '</p>';
    }
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'northsmith_child_display_delivery_instructions_admin' );


/**
 * Apply 10% bulk discount when quantity >= 5 per cart item.
 *
 * @param WC_Cart $cart Cart object.
 * @return void
 */
function northsmith_child_bulk_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    foreach ( $cart->get_cart() as $cart_item ) {
        $quantity = $cart_item['quantity'];
        $product  = $cart_item['data'];

        if ( $product->is_purchasable() && $quantity >= 5 ) {
            $price     = $product->get_price();
            $new_price = $price * 0.90;

            $product->set_price( $new_price );
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'northsmith_child_bulk_discount', 10 );


/**
 * SEO: meta description
 */
function northsmith_meta_description() {
    $desc = '';
    if ( is_front_page() ) {
        $desc = 'Northsmith — premium furniture built to last. Sustainably sourced, quietly designed, made for daily use.';
    } elseif ( is_singular() ) {
        global $post;
        $desc = wp_strip_all_tags( $post->post_excerpt ?: wp_trim_words( $post->post_content, 30 ) );
    } elseif ( is_product_category() ) {
        $term = get_queried_object();
        $desc = $term->description ?: 'Shop ' . $term->name . ' at Northsmith.';
    } elseif ( is_shop() ) {
        $desc = 'Browse the full Northsmith collection — chairs, lighting, desks, and accessories.';
    }
    if ( $desc ) {
        echo '<meta name="description" content="' . esc_attr( wp_trim_words( $desc, 30 ) ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'northsmith_meta_description', 1 );

/**
 * SEO: OG tags
 */
function northsmith_og_tags() {
    echo '<meta property="og:site_name" content="Northsmith">' . "\n";
    echo '<meta property="og:type" content="' . ( function_exists( 'is_product' ) && is_product() ? 'product' : 'website' ) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( home_url( add_query_arg( array(), $GLOBALS['wp']->request ) ) ) . '">' . "\n";
    if ( is_singular() && has_post_thumbnail() ) {
        echo '<meta property="og:image" content="' . esc_url( get_the_post_thumbnail_url( null, 'large' ) ) . '">' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'northsmith_og_tags', 2 );

/**
 * SEO: JSON-LD
 */
function northsmith_json_ld() {
    if ( is_front_page() ) {
        $org = array(
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => 'Northsmith',
            'url'      => home_url( '/' ),
            'logo'     => get_stylesheet_directory_uri() . '/assets/brand/northsmith.png',
            'address'  => array(
                '@type'           => 'PostalAddress',
                'streetAddress'   => '123 Artisan Way',
                'addressLocality' => 'Portland',
                'addressRegion'   => 'OR',
                'postalCode'      => '97209',
                'addressCountry'  => 'US',
            ),
        );
        echo '<script type="application/ld+json">' . wp_json_encode( $org ) . '</script>' . "\n";
    }
    if ( function_exists( 'is_product' ) && is_product() ) {
        global $product;
        if ( $product ) {
            $product_schema = array(
                '@context'    => 'https://schema.org',
                '@type'       => 'Product',
                'name'        => $product->get_name(),
                'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
                'sku'         => $product->get_sku(),
                'image'       => wp_get_attachment_url( $product->get_image_id() ),
                'offers'      => array(
                    '@type'         => 'Offer',
                    'price'         => $product->get_price(),
                    'priceCurrency' => get_woocommerce_currency(),
                    'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                    'url'           => get_permalink( $product->get_id() ),
                ),
            );
            echo '<script type="application/ld+json">' . wp_json_encode( $product_schema ) . '</script>' . "\n";
        }
    }
}
add_action( 'wp_head', 'northsmith_json_ld', 3 );

if ( function_exists( 'remove_action' ) ) {
    remove_action( 'wp_head', 'rel_canonical' );
}
function northsmith_canonical() {
    if ( is_singular() ) {
        echo '<link rel="canonical" href="' . esc_url( get_permalink() ) . '">' . "\n";
    }
}
add_action( 'wp_head', 'northsmith_canonical' );

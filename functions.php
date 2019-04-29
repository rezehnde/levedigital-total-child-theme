<?php
/**
 * Child theme functions
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development
 * and http://codex.wordpress.org/Child_Themes), you can override certain
 * functions (those wrapped in a function_exists() call) by defining them first
 * in your child theme's functions.php file. The child theme's functions.php
 * file is included before the parent theme's file, so the child theme
 * functions would be used.
 *
 * Text Domain: wpex
 * @link http://codex.wordpress.org/Plugin_API
 *
 */

/**
 * Load the parent style.css file
 *
 * @link http://codex.wordpress.org/Child_Themes
 */
function total_child_enqueue_parent_theme_style() {

	// Dynamically get version number of the parent stylesheet (lets browsers re-cache your stylesheet when you update your theme)
	$theme   = wp_get_theme( 'Total' );
	$version = $theme->get( 'Version' );

	// Load the stylesheet
	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css', array(), $version );

}
add_action( 'wp_enqueue_scripts', 'total_child_enqueue_parent_theme_style' );

// Add Shortcode
function get_count_posts( $atts ) {

    // Attributes
    $atts = shortcode_atts(
        array(
            'type' => 'post',
        ),
        $atts,
        'count_posts'
    );
    $count_posts = wp_count_posts($atts['type']);
    $published_posts = $count_posts->publish;
    return $published_posts;
}
add_shortcode( 'count_posts', 'get_count_posts' );

add_action( 'woocommerce_single_product_summary', 'custom_single_product_summary', 2 );
function custom_single_product_summary(){
    global $product;

    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
    add_action( 'woocommerce_single_product_summary', 'custom_single_excerpt', 20 );
}

function custom_single_excerpt(){
    global $post, $product;

    $short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );

    // if ( ! $short_description ) return;

    // Sale formatted  price:
    $product_price = wc_price( wc_get_price_to_display( $product, array( 'price' => $product->get_sale_price() ) ) );

    $custom_text = '<p><strong>'.$product_price.'</strong> em até 10x no cartão ou R$ 790 à vista.</p>';

    // The custom text
    $custom_text .= '<ul class="fancy-bullet-points">
    <li>O modelo escolhido estará disponível em um ambiente de teste para iniciarmos a personalização em até 24 horas após a confirmação de pagamento.</li>
    <li>Seu novo site será responsivo, com layout adaptado para celulares, tablets e computadores.</li>
    <li>A primeira anuidade do registro do endereço do seu site (seunegocio.com.br) está inclusa no preço.</li>
    <li>Você ainda terá e-mails com endereço personalizado voce@seunegocio.com.br.</li>
    <li>Seu site terá certificado de segurança SSL que protege a troca de informações e transações financeiras.</li>
    <li>Você precisará enviar o conteúdo do site (logomarca, textos e imagens) e nós faremos todo o trabalho de personalização para você.</li>
    <li>Mensalidade que cabe no seu bolso. Apenas R$ 49/mês.</li>
    </ul>';

    ?>
    <div class="woocommerce-product-details__short-description">
        <?php echo $custom_text; // WPCS: XSS ok. ?>
    </div>
    <?php
}

add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );

function woo_remove_product_tabs( $tabs ) {
    unset( $tabs['description'] );          // Remove the description tab
    unset( $tabs['reviews'] );          // Remove the reviews tab
    unset( $tabs['additional_information'] );   // Remove the additional information tab
    return $tabs;
}

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

function custom_wc_get_gallery_image_html( $attachment_id, $main_image = false ) {
    $flexslider        = (bool) apply_filters( 'woocommerce_single_product_flexslider_enabled', get_theme_support( 'wc-product-gallery-slider' ) );
    $gallery_thumbnail = wc_get_image_size( 'gallery_thumbnail' );
    $thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
    $image_size        = apply_filters( 'woocommerce_gallery_image_size', $flexslider || $main_image ? 'woocommerce_single' : $thumbnail_size );
    $full_size         = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
    $thumbnail_src     = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
    $full_src          = wp_get_attachment_image_src( $attachment_id, $full_size );
    $alt_text          = trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
    $image             = wp_get_attachment_image(
        $attachment_id,
        $image_size,
        false,
        apply_filters(
            'woocommerce_gallery_image_html_attachment_image_params',
            array(
                'title'                   => _wp_specialchars( 'Modelo para Criação de Site ' . get_the_title(), ENT_QUOTES, 'UTF-8', true ),
                'data-caption'            => _wp_specialchars( get_post_field( 'post_excerpt', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
                'data-src'                => esc_url( $full_src[0] ),
                'data-large_image'        => esc_url( $full_src[0] ),
                'data-large_image_width'  => esc_attr( $full_src[1] ),
                'data-large_image_height' => esc_attr( $full_src[2] ),
                'class'                   => esc_attr( $main_image ? 'wp-post-image' : '' ),
            ),
            $attachment_id,
            $image_size,
            $main_image
        )
    );

    return '<div data-thumb="' . esc_url( $thumbnail_src[0] ) . '" data-thumb-alt="' . esc_attr( $alt_text ) . '" class="caption-style"><a href="'.esc_url( get_field('demo_url') ).'" target="_blank">' . $image . '</a><div class="caption"><a href="'.esc_url( get_field('demo_url') ).'" target="_blank">Visualizar Exemplo de Site</a></div></div>';
}
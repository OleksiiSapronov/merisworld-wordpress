<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
    wp_enqueue_style( 'monaco-font', 'https://fonts.googleapis.com/css?family=Monaco', [], null );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles', 20 );

function avada_lang_setup() {
    $lang = get_stylesheet_directory() . '/languages';
    load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

function portfolio_category_slider() {
    // Get the portfolio categories
    $temp_categories = get_terms('portfolio_category');

    // Sort the categories by slug
    usort($temp_categories, function($a, $b) {
        return strcmp($a->slug, $b->slug);
    });

    // Assign sorted array to $categories
    $categories = $temp_categories;

    if (!empty($categories) && !is_wp_error($categories)) {
        ob_start();
        ?>
        <div class="portfolio-slider">
            <?php foreach ($categories as $category) :
                // Get the serialized _fusion meta field
                $fusion_meta = get_term_meta($category->term_id, '_fusion', true);
                
                // Unserialize the data
                if (!empty($fusion_meta)) {
                    $fusion_data = maybe_unserialize($fusion_meta);
                    
                    // Check if the featured image exists and extract the URL
                    if (isset($fusion_data['featured_image']['url'])) {
                        $image_url = esc_url($fusion_data['featured_image']['url']);
                    }
                }
                
                // Get portfolio items within this category
                $portfolios = new WP_Query(array(
                    'post_type' => 'avada_portfolio',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'portfolio_category',
                            'field' => 'term_id',
                            'terms' => $category->term_id,
                        ),
                    ),
                ));
                ?>
                <div class="portfolio-slide" data-category-id="<?php echo esc_attr($category->term_id); ?>" style="background-image: url('<?php echo esc_url($image_url); ?>'); line-height: 1.2;">
                <?php
                    // Decode the serialized _fusion array
                    $fusion_data = maybe_unserialize(get_term_meta($category->term_id, '_fusion', true));

                    // Check if page_title_bg exists and retrieve the URL
                    if (!empty($fusion_data) && isset($fusion_data['page_title_bg']['url'])) {
                        $page_title_bg_url = esc_url($fusion_data['page_title_bg']['url']);
                    } else {
                        $page_title_bg_url = ''; // Fallback in case no image is found
                    }
                ?>

                <div class="category-info" data-category-id="<?php echo esc_attr($category->term_id); ?>">
                    <?php if ($page_title_bg_url) : ?>
                        <img src="<?php echo $page_title_bg_url; ?>" alt="<?php echo esc_attr($category->name); ?>" style="width:50%; height:auto; margin: auto;">
                    <?php endif; ?>
                    <p style="margin-top: 20px;"><?php echo esc_html($category->description); ?></p>
                </div>
                    
                    <div class="portfolio-info" data-category-id="<?php echo esc_attr($category->term_id); ?>" style="display: none;">
                    <?php if ($portfolios->have_posts()) : ?>
                        <?php while ($portfolios->have_posts()) : $portfolios->the_post(); ?>
                            <div class="portfolio-item" style="display: none;">
                                <!-- Fetch and display the featured image URL -->
                                <?php $featured_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>
                                <?php if ($featured_image_url): ?>
                                    <img src="<?php echo esc_url($featured_image_url); ?>" alt="<?php the_title(); ?>" style="margin: auto; margin-bottom: 15px;" class="dynamic-img"/>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function() {
                                            const images = document.querySelectorAll('.dynamic-img');

                                            images.forEach(img => {
                                                if (img.naturalWidth > img.naturalHeight) {
                                                    img.style.width = '80%';
                                                } else {
                                                    img.style.width = '60%';
                                                }

                                            });
                                        });
                                    </script>
                                <?php endif; ?>
                                
                                <p style="font-family: 'monaco'; font-size: 12px; font-weight: bold;"><?php echo get_post_meta(get_the_ID(), 'port_title', true); ?><span> (<?php echo get_post_meta(get_the_ID(), 'port_subtitle', true); ?>)</span></p>
                                <p style="font-family: 'monaco'; font-size: 12px;"><?php echo get_post_meta(get_the_ID(), 'port_description1', true); ?></p>
                                <p style="font-family: 'monaco'; font-size: 12px; font-weight: bold;"><?php echo get_post_meta(get_the_ID(), 'port_description2', true); ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
add_shortcode('portfolio_category_slider', 'portfolio_category_slider');

function custom_portfolio_slider_scripts() {
    // Enqueue Slick Slider CSS
    wp_enqueue_style('slick-slider-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_style('slick-slider-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
    
    // Enqueue Slick Slider JS
    wp_enqueue_script('slick-slider-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), null, true);

    // Enqueue custom slider script and styles
    wp_enqueue_script('portfolio-slider-js', get_stylesheet_directory_uri() . '/js/portfolio-slider.js', array('jquery', 'slick-slider-js'), null, true);
}
add_action('wp_enqueue_scripts', 'custom_portfolio_slider_scripts');

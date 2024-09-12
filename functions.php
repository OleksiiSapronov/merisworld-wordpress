<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'Anonymous Pro-font', 'https://fonts.googleapis.com/css2?family=Anonymous+Pro', [], null );
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
                <div class="portfolio-slide" data-category-id="<?php echo esc_attr($category->term_id); ?>">
                    <div class="target-div">
                        <div style="height: 100%; overflow: auto; scrollbar-width: none;">
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
                            
                            <div class="portfolio-info" data-category-id="<?php echo esc_attr($category->term_id); ?>">
                                <div class="portfolio-item" data-category-id="<?php echo esc_attr($category->term_id); ?>" style="margin-top: 40%; padding: 10px;">
                                    <?php if ($page_title_bg_url) : ?>
                                        <img src="<?php echo $page_title_bg_url; ?>" alt="<?php echo esc_attr($category->name); ?>" style="width:80%; height:auto; margin: auto;">
                                    <?php endif; ?>
                                    <p class="cate_desc_font" style="font-family: 'Anonymous Pro'; font-size: 18px;"><?php echo esc_html($category->description); ?></p>
                                </div>
                                <?php if ($portfolios->have_posts()) : ?>
                                    <?php while ($portfolios->have_posts()) : $portfolios->the_post(); ?>
                                        <div class="portfolio-item" style="display: none;">
                                            <?php 
                                            // Fetch the video URL from a custom field (assuming 'featured_video' is the meta key for the video)
                                            $featured_video_id = get_post_meta(get_the_ID(), 'featured_video', true);
                                            $featured_video_url = wp_get_attachment_url($featured_video_id);
                                            $featured_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');

                                            // Check if there is a video URL
                                            if (!empty($featured_video_url)): ?>
                                                <!-- Display video player -->
                                                <video controls autoplay muted loop playsinline style="width: 80%; margin: auto; margin-bottom: 10px;">
                                                    <source src="<?php echo esc_url($featured_video_url); ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php elseif (!empty($featured_image_url)): ?>
                                                <!-- If no video, display the featured image -->
                                                <img src="<?php echo esc_url($featured_image_url); ?>" alt="<?php the_title(); ?>" style="margin: auto; margin-bottom: 10px;" class="dynamic-img"/>
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
                                            <?php
                                                // Get the link field (which is an array containing url, title, and target)
                                                $link = get_field('custom_title'); // Assuming 'custom_title' is the field name for the ACF link

                                                if ($link) : 
                                                    // Extract the link components
                                                    $url = $link['url'];
                                                    $title = $link['title']; // This will be used as the link text
                                                    $target = $link['target'] ? $link['target'] : '_self'; // Defaults to '_self' if no target is set
                                                    $custom_subtitle = get_field('custom_subtitle'); // Assuming you still have the subtitle field
                                                ?>
                                                <p style="font-family: 'Anonymous Pro'; font-size: 16px; font-weight: bold; margin-bottom: 10px;">
                                                    <a href="<?php echo esc_url($url); ?>" target="<?php echo esc_attr($target); ?>" style="text-decoration: underline; color: #198fd9;"><?php echo esc_html($title); ?></a>
                                                    <?php if ($custom_subtitle) : ?>
                                                        <span>(<?php echo esc_html($custom_subtitle); ?>)</span>
                                                    <?php endif; ?>
                                                </p>
                                                <?php endif; ?>
                                            <p style="font-family: 'Anonymous Pro'; font-size: 14px; margin-bottom: 10px;"><?php echo get_post_meta(get_the_ID(), 'custom_description1', true); ?></p>
                                            <p style="font-family: 'Anonymous Pro'; font-size: 14px; font-weight: bold; margin-bottom: 10px;"><?php echo get_post_meta(get_the_ID(), 'custom_description2', true); ?></p>
                                        </div>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </div>
                        </div>
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
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [] );
}
add_action('wp_enqueue_scripts', 'custom_portfolio_slider_scripts');

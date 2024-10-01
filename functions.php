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
                <div id="portfolio-slide" class="portfolio-slide" data-category-id="<?php echo esc_attr($category->term_id); ?>">
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
                                    <p class="cate_desc_font" style="font-family: 'Anonymous Pro';"><?php echo esc_html($category->description); ?></p>
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
                                                <video controls autoplay muted loop playsinline style="width: 90%; margin: auto; margin-bottom: 3%;">
                                                    <source src="<?php echo esc_url($featured_video_url); ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            <?php elseif (!empty($featured_image_url)): ?>
                                                <!-- If no video, display the featured image -->
                                                <img src="<?php echo esc_url($featured_image_url); ?>" alt="<?php the_title(); ?>" style="margin: auto; margin-bottom: 3%;" class="dynamic-img"/>
                                                <script>
                                                    document.addEventListener("DOMContentLoaded", function() {
                                                        const images = document.querySelectorAll('.dynamic-img');

                                                        images.forEach(img => {
                                                            if (img.naturalWidth > img.naturalHeight) {
                                                                img.style.width = '90%';
                                                            } else {
                                                                img.style.width = '70%';
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
                                                <p class="port_title" style="font-family: 'Anonymous Pro'; font-weight: bold; margin-bottom: 3%;">
                                                    <a href="<?php echo esc_url($url); ?>" target="<?php echo esc_attr($target); ?>" style="text-decoration: underline; color: #198fd9;"><?php echo esc_html($title); ?></a>
                                                    <?php if ($custom_subtitle) : ?>
                                                        <span>(<?php echo esc_html($custom_subtitle); ?>)</span>
                                                    <?php endif; ?>
                                                </p>
                                                <?php endif; ?>
                                            <p class="port_description1" style="font-family: 'Anonymous Pro'; margin-bottom: 3%;"><?php echo get_post_meta(get_the_ID(), 'custom_description1', true); ?></p>
                                            <p class="port_description2" style="font-family: 'Anonymous Pro'; font-weight: bold; margin-bottom: 3%;"><?php echo get_post_meta(get_the_ID(), 'custom_description2', true); ?></p>
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

function save_user_data() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subscribers';

    // Check if the table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // If the table doesn't exist, create it
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_email (email)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Sanitize and retrieve form data
    $first_name = sanitize_text_field($_POST['first-name']);
    $last_name = sanitize_text_field($_POST['last-name']);
    $email = sanitize_email($_POST['email']);

    // Check if email already exists in the database
    $existing_email = $wpdb->get_var($wpdb->prepare("SELECT email FROM $table_name WHERE email = %s", $email));

    if ($existing_email) {
        wp_send_json_error(['message' => 'This email is already subscribed.']);
        return;
    }

    // Insert data into the WordPress database
    $data = array(
        'first_name' => $first_name,
        'last_name' => $last_name,
        'email' => $email,
        'created_at' => current_time('mysql'),
    );

    // Check if the data was inserted successfully
    if ($wpdb->insert($table_name, $data)) {
        $to = $email;
        $subject = "Subscription Confirmation";
        $message = "You have successfully subscribed to our mailing list.";
        $headers = 'From: Meris.World' . "\r\n";
        wp_mail($to, $subject, $message, $headers);

        // Send notification to the site admin
        $to1 = 'info@meris.world';
        $subject1 = "Subscription Confirmation";
        $message1 = "$first_name $last_name has successfully subscribed to your mailing list.";
        $headers1 = 'From: Meris.World' . "\r\n";
        wp_mail($to1, $subject1, $message1, $headers1);

        wp_send_json_success(['message' => 'Thank you for subscribing!']);
    } else {
        wp_send_json_error(['message' => 'There was an error saving your data.']);
    }
}

add_action('wp_ajax_save_user_data', 'save_user_data');
add_action('wp_ajax_nopriv_save_user_data', 'save_user_data');

function remove_user_data() {
    // Get the data from the request
    $first_name = isset($_POST['first-name']) ? sanitize_text_field($_POST['first-name']) : '';
    $last_name = isset($_POST['last-name']) ? sanitize_text_field($_POST['last-name']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

    // Check if the user exists in the database (assumes you're using a custom table)
    global $wpdb;
    $table_name = $wpdb->prefix . 'subscribers'; // Update with your actual table name

    $user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE first_name = %s AND last_name = %s AND email = %s", $first_name, $last_name, $email));
    
    if ($user) {
        // User found, proceed to delete
        $deleted = $wpdb->delete($table_name, array('id' => $user->id)); // Assuming 'id' is your primary key

        if ($deleted) {
            // Successful deletion
            $to = $email;
            $subject = "Unsubscription Confirmation";
            $message = "You have successfully unsubscribed from our mailing list.";
            $headers = 'From: Meris.World' . "\r\n";
            wp_mail($to, $subject, $message, $headers);

            $to1 = 'info@meris.world';
            $subject1 = "Unsubscription Confirmation";
            $message1 = "$first_name $last_name has successfully unsubscribed from your mailing list.";
            $headers1 = 'From: Meris.World' . "\r\n";
            wp_mail($to1, $subject1, $message1, $headers1);

            wp_send_json_success(['message' => 'User unsubscribed successfully.']);
        } else {
            // Deletion failed
            wp_send_json_error(['message' => 'There was an error removing the user from the database.']);
        }
    } else {
        // User not found
        wp_send_json_error(['message' => 'No matching user found. Please check the provided details.']);
    }

    // Always die in AJAX function
    wp_die();
}

add_action('wp_ajax_remove_user_data', 'remove_user_data');
add_action('wp_ajax_nopriv_remove_user_data', 'remove_user_data');

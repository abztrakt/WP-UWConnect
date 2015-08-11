<?php
function service_post_type() {
    $labels = array(
        'name' => _x('Services', 'post type general name'),
        'singular_name' => _x('Service', 'post type signular name'),
        'add_new' => _x('Add New', 'service'),
        'add_new_item' => __('Add New Service'),
        'edit_item' => __('Edit Service'),
        'new_item' => __('New Service'),
        'all_items' => __('All Services'),
        'view_item' => __('View Service'),
        'search_items' => __('Search Services'),
        'not_found' => __('No Services Found'),
        'not_found_in_trash' => __('No Services found in the Trash'),
        'parent_item_colon' => ''
    );

    $args = array(
        'labels' => $labels,
        'description' => 'Holds our services and service sepecific data.',
        'public' => true,
        'capability_type' => 'page',
        'menu_position' => 5,
        'register_meta_box_cb' => 'service_info',
        'supports' => array('title'),
        'has_archive' => false,
        'hierarchical' => true,
    );
    register_post_type('service', $args);
}
if (isset($_POST['uwc_SERVCAT'])) {
    if ((get_option('uwc_SERVCAT') == 'on' && $_POST['uwc_SERVCAT'] != 'off') || $_POST['uwc_SERVCAT'] == 'on') {
        add_action('init', 'service_post_type');
    }
} else if (get_option('uwc_SERVCAT') == 'on') {
    add_action('init', 'service_post_type');
}

add_action('add_meta_boxes', 'service_info');

function service_info() {
    add_meta_box('service_form', 'Service Details', 'service_content', 'service', 'normal', 'default');
}

function service_content($object, $box) {
    wp_nonce_field(basename(__FILE__), 'service_details_nonce'); ?>

        <h3><label for='uwc-short-description'><?php _e('Brief Service Description:', 'services'); ?></label></h3><br />
        <input type='text' name='uwc-short-description' id='uwc-short-description' value="<?php echo esc_attr(get_post_meta($object->ID, 'uwc-short-description', true)); ?>" size="90" /><br />
        <span>The "one-liner", high-level description of the service.</span>

        <h3><label for='uwc-description'><?php _e('Service Description', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-description', true), 'uwc-description'); ?><br />
        <span>A summary of the service that is meaningful to the customer. This might include business applications the service supports, benefits the service offers the customer, and features of the service.</span>

        <h3><label for='uwc-options-text'><?php _e('Service Options:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-options-text', true), 'uwc-options-text'); ?><br />
        <span>Placeholder for subcatagories of the service; if none, state "none", if "multiple flavors" are available, what are the features of each option? Include price and eligibility data if present.</span>

        <h3><label for='uwc-options-list'><? _e('Service Options (simple):', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-options-list', true), 'uwc-options-list'); ?><br />
        <span>Used for extracting service options for consumption in external applications (RT andFYI). Enter each service separated by columns.</span>

        <h3><label for='uwc-eligibility'><?php _e('Eligibility', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-eligibility', true), 'uwc-eligibility'); ?><br />
        <span>Groupings of people who may obtain the service.</span>

        <h3><label for='uwc-ordering'><?php _e('How to Order:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-ordering', true), 'uwc-ordering'); ?><br />
        <span>Contact information (email, phone or web link) to use to obtain the service.</span>

        <h3><label for='uwc-availability'><?php _e('Availabiliy:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-availability', true), 'uwc-availability'); ?><br />
        <span>State when and where service is available. Include restrictions of time, location or capacity that limit access to the service.</span>

        <h3><label for='uwc-price'><?php _e('Price:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-price', true), 'uwc-price'); ?><br />
        <span>The amount paid by the customer to obtain and use the service. If no charge, state "no charge".</span>

        <h3><label for='uwc-additional-info'><?php _e('Additional Information:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-additional-info', true), 'uwc-additional-info'); ?><br />
        <span>Link for more information, other data not captured elsewhere in record.</span>

        <h3><label for='uwc-level-descr'><?php _e('Service Level Description', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-level-descr', true), 'uwc-level-descr'); ?><br />
        <span>A summary of what the customer can expect if they were to obtain the service. This might include commitments about reliability, quality, typical time to respond to service requests, or uptime.</span>

        <h3><label for='uwc-support-info'><?php _e('Support Information', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-support-info', true), 'uwc-support-info'); ?><br />
        <span>Contact information for getting help witht he service. This should include the contacts' hours of availability. If during certain hours the contact information is different, include the additional contact information and the respective hours of availabilty.</span>

        <h3><label for='uwc-customer-ref'><?php _e('Customer References:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-customer-ref', true), 'uwc-customer-ref'); ?><br />
        <span>List of contacts for current customers of the service who have agreed to discuss the service with prospective customers.</span>

        <h3><label for='uwc-more-info'><?php _e('Contact for More Information:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-more-info', true), 'uwc-more-info'); ?><br />
        <span>Contact (phone and/or email address) to whom questions about service may be directed. This may or may not differ from the contacts listed.</span>

        <h3><label for='uwc-service-rep'><?php _e('Maintainance:', 'services'); ?></label></h3><br />
        <h4><label for='uwc-service-rep'><?php _e('Service Representative:', 'services'); ?></label></h4><br />
        <input type='text' name='uwc-service-rep' id='uwc-service-rep' value='<?php echo esc_attr(get_post_meta($object->ID, 'uwc-service-rep', true)); ?>' size='70' /><br />
        <span>Person assigned to the Service Representative role. See Roles and Responsibilities.</span>
        <h4><label for='uwc-last-review'><?php _e('Last Review Date:', 'services'); ?></label></h4><br />
        <input type='text' name='uwc-last-review' id='uwc-last-review' value='<?php echo esc_attr(get_post_meta($object->ID, 'uwc-last-review', true)); ?>' size='70' /><br />
        <span>Format: 03/03/2015<br />Last date the service representative verified the data in the service catalog entry for accuracy.</span>
        <h4><label for='uwc-next-review'><?php _e('Next Review Date:', 'services'); ?></label></h4><br />
        <input type='text' name='uwc-next-review' id='uwc-next-review' value='<?php echo esc_attr(get_post_meta($object->ID, 'uwc-next-review', true)); ?>' size='70' /><br />
        <span>Format: 03/03/2015<br />Scheduled date for next review of the catalog entry for accuracy by the service representative.</span>

        <h3><label for='uwc-keywords'><?php _e('Keywords:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-keywords', true), 'uwc-keywords'); ?><br />
        <span>Search terms customers might use to find the service catalog entry. Each keyword should be separated by a comma.</span>

        <h3><label for='uwc-cost'><?php _e('Cost', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-cost', true), 'uwc-cost'); ?><br />
        <span>Placeholder for unit measures and total resource allocation required to deliver the service.</span>

        <h3><label for='uwc-extra-notes'><?php _e('Notes:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-extra-notes', true), 'uwc-extra-notes'); ?><br />
        <span>Placeholder for information not captured elsewhere.</span>

        <h3><label for='uwc-teams'><?php _e('Internal Teams:', 'services'); ?></label></h3><br />
        <?php wp_editor(get_post_meta($object->ID, 'uwc-teams', true), 'uwc-teams'); ?><br />
        <span>A list of internal UW Technology teams that are involved in providing this service.</span>

<?php }

add_action('save_post', 'save_service_form', 10, 2);

function save_service_form($post_id, $post) {

    if (!verify_save('service_details_nonce', $post_id))
        return $post_id;

    update_service($post_id, 'service_form', 'uwc-short-description');
    update_service($post_id, 'service_form', 'uwc-description');
    update_service($post_id, 'service_form', 'uwc-options-text');
    update_service($post_id, 'service_form', 'uwc-options-list');
    update_service($post_id, 'service_form', 'uwc-eligibility');
    update_service($post_id, 'service_form', 'uwc-ordering');
    update_service($post_id, 'service_form', 'uwc-availability');
    update_service($post_id, 'service_form', 'uwc-price');
    update_service($post_id, 'service_form', 'uwc-additional-info');
    update_service($post_id, 'service_form', 'uwc-level-descr');
    update_service($post_id, 'service_form', 'uwc-support-info');
    update_service($post_id, 'service_form', 'uwc-customer-ref');
    update_service($post_id, 'service_form', 'uwc-more-info');
    update_service($post_id, 'service_form', 'uwc-service-rep');
    update_service($post_id, 'service_form', 'uwc-last-review');
    update_service($post_id, 'service_form', 'uwc-next-review');
    update_service($post_id, 'service_form', 'uwc-keywords');
    update_service($post_id, 'service_form', 'uwc-cost');
    update_service($post_id, 'service_form', 'uwc-extra-notes');
    update_service($post_id, 'service_form', 'uwc-teams');

    flush_rewrite_rules();
}

function verify_save ($nonce_name, $post_id) {
    if (!isset($_POST[$nonce_name])) {
        return false;
    }
    if (!wp_verify_nonce($_POST[$nonce_name], basename(__FILE__))) {
        return false;
    }
    if (!current_user_can('edit_page', $post_id)) {
        return false;
    }

    return true;
}

function update_service($post_id, $service_form, $detail_name) {
    $new_meta_value = (isset($_POST[$detail_name]) ? sanitize_post_field($detail_name, $_POST[$detail_name], $post_id, 'display'):'');

    $meta_key = $detail_name;

    $meta_value = get_post_meta($post_id, $meta_key, true);

    if ($new_meta_value && '' == $meta_value)
        add_post_meta($post_id, $meta_key, $new_meta_value, true);
    elseif ($new_meta_value && $new_meta_value != $meta_value)
        update_post_meta($post_id, $meta_key, $new_meta_value);
    elseif ('' == $new_meta_value && $meta_value)
        delete_post_meta($post_id, $meta_key, $meta_value);
}

add_action('init', 'category_taxonomy', 0);
add_action('init', 'views_taxonomy', 0);

function category_taxonomy() {
    register_taxonomy(
        'servicecategory',
        'service',
        array(
            'labels' => array (
                'name' => 'Service Category',
                'add_new_item' => 'Add New Service Category',
                'new_item_name' => 'New Service Category'
            ),
            'show_ui' => true,
            'show_tagcloud' => true,
            'hierarchical' => true,
            'capabilities' => array(
                        'assign_terms' => 'edit_pages'
                        )
        )
    );
}

function views_taxonomy() {
    register_taxonomy(
        'views',
        'service',
        array (
            'labels' => array (
                'name' => 'Views',
                'add_new_item' => 'Add New View',
                'new_item_name' => 'New View'
            ),
            'show_ui' => true,
            'show_tagcloud' => true,
            'hierarchical' => true
        )
    );
}

function servicecatalog_widgets_init() {

  register_sidebar( array(
    'name'          => 'Service Catalog Sidebar',
    'id'            => 'Service-Catalog-Sidebar',
    'before_widget' => '<div>',
    'after_widget'  => '</div>',
    'before_title'  => '<h2 class="rounded">',
    'after_title'   => '</h2>',
  ) );

}
add_action( 'widgets_init', 'servicecatalog_widgets_init' );

function taxonomy_list_shortcode($atts) {
    $tax = $atts['tax'];
    $terms = get_terms($tax);
    $taxonomy = get_taxonomy($tax);
    #$output = '<h4 class="tax-head">' . $taxonomy->labels->name . '</h4>';
    $output = '<ul style="list-style-type:none; padding-left:0px; margin-left:15px;">';
    $siteurl = site_url();
    foreach ($terms as $term) {
        $url = $siteurl . '/' . $tax .  '/' . $term->slug;
        $output .= '<a href="' . $url . '"><li style="font-size:14pt;">' . $term->name . '</li></a>';
        $output .= '<p style="margin-left:25px;">' . $term->description . '</p>';
    }
    $output .= '</ul>';
    return $output;
}
function register_tax_shortcodes() {
    add_shortcode( 'taxtermlist', 'taxonomy_list_shortcode' );
}
add_action( 'init', 'register_tax_shortcodes' );

add_filter('template_include', 'service_page_template', 1);

function service_page_template($template) {
    global $post;
    if ( $post->post_type == 'service' && ( basename( $template ) == "single.php" || basename( $template) == "index.php") ) {
        $new_template = dirname(__FILE__) . '/single-service.php';
        if ( '' != $new_template ) {
            return $new_template;
        }
    } else if ( $post->post_type == 'service' && basename( $template ) == "archive.php" && is_tax('servicecategory')) {
        $new_template = dirname(__FILE__) . '/taxonomy-servicecategory.php';
        if ( '' != $new_template ) {
            return $new_template;
        }
    } else if ( $post->post_type == 'service' && basename( $template ) == "archive.php" && is_tax('views')) {
        $new_template = dirname(__FILE__) . '/taxonomy-views.php';
        if ( '' != $new_template ) {
            return $new_template;
        }
    }
    return $template;
}

function service_breadcrumbs($post = '') {
    $breadcrumb = '';
    $service_title;
    $homepagetitle = 'Service Catalog';
    $homepageslug = 'services';
    if ( !empty($post) && $post->post_type == 'service' ) {
        $service_title = $post->post_title;
    }
    echo "<div class='breadcrumbs-container' style='margin-left:0px;'>";
    echo "<ul class='breadcrumbs-list'>";
    if (isset($service_title)) {
        echo "<li><a title='" . $homepagetitle . "' href='" . get_site_url() . "/" . $homepageslug . "'>" . $homepagetitle . "</a></li>";
        echo "<li class='current'><a title='" . $post->post_title . "' href='" . get_permalink() . "'>" . $post->post_title . "</a></li>";
    } else {
        echo "<li class='current'><a title='" . $homepagetitle . "' href='" . get_site_url() . "/" . $homepageslug . "'>" . $homepagetitle . "</a></li>";
    }
    echo "</ul>";
    echo "</div>";
}
?>

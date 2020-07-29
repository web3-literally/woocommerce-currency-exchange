<?php

require_once 'global.php';
require_once 'price_helper.php';

/**
 * Woocommerce Real-Time prices
 *
 * @package           WoocommerceRealTimePrices
 * @author            Mike Castro Demaria
 * @copyright         2020 Supersonique Studio
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Woocommerce Real-Time prices
 * Plugin URI:  https://supersonique-studio.com/
 * Description: Woocommerce Plugin for Real-Time prices. Add 2 fields to Woocomerce
 * products and variation with the purchasing price and the margin percentage
 * to provide a real time price calculation who use parameters in product's sheet
 * Version:             20200710
 * Requires at least:   5.2
 * Requires PHP:        7.0
 * Author:      Mike Castro Demaria
 * Author URI:  https://supersonique-studio.com/
 * Text Domain: woo-rtp
 * Domain Path: /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 *
 * {Plugin Name} is free software: you can redistribute it and/or modify
 *
 * {Plugin Name} is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with {Plugin Name}. If not, see {URI to Plugin License}.
 * ****************************************************************************************
*/
/**
 * To create our fields we will mainly use WooCommerce builtin function like
 * (All these functions are located in WooCommerce/Admin/WritePanels/writepanels-init.php.):
 * - woocommerce_wp_text_input()
 * - woocommerce_wp_textarea_input()
 * - woocommerce_wp_select()
 * - woocommerce_wp_checkbox()
 * - woocommerce_wp_hidden_input()
 *
 * Other SRC : https://gist.github.com/corsonr/9152652
 * Others :
 * https://www.cloudways.com/blog/add-custom-product-fields-woocommerce/
 * https://www.webhat.in/article/woocommerce-tutorial/adding-custom-fields-to-woocommerce-product-category/
 *
 * To create a text field type, you will need to use that code:
 *
 * function woo_add_custom_general_fields() {
 *
 *   global $woocommerce, $post;
 *
 *   echo '<div class="options_group">';
 *
 *   // Custom fields will be created here...
 *
 *   echo '</div>';
 *
 *}
*/
/**
 * add extra Number Field for purchasing price and margin percentage
 * used to calculate the final price JIT
 */

// Display Fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');

// Save Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');

function woocommerce_product_custom_fields(){

    global $woocommerce, $post;
    $cur = get_option('woocommerce_currency');
    $cur_symbol = get_woocommerce_currency_symbol();

    //echo '<div class="product_custom_field">';

    // Custom Product REF for sync
    woocommerce_wp_text_input(
        array(
            'id'                => '_global_REF',
            'label'             => __( 'Reference (CUR)', 'woo-rtp' ),
            'placeholder'       => '',
            'desc_tip'          => 'true',
            'description'       => __( 'Enter the reference you like to use for sync external DATA.', 'woo-rtp' ),
            'type'              => 'text'
        )
    );

    // Custom Product update time : store last update date
    // need to do the same for variations too !!!
    woocommerce_wp_text_input(
        array(
            'id'                => '_global_update_date',
            'label'             => __( 'Last update date', 'woo-rtp' ),
            'placeholder'       => '',
            'desc_tip'          => 'true',
            'description'       => __( 'Product last\'s update date with external DATA .', 'woo-rtp' ),
            'type'              => 'text'
        )
    );

    // Custom Product Text Field
    woocommerce_wp_text_input(
        array(
            'id'                => '_purchasing_price',
            'label'             => __( 'Exchange flat rate ('.$cur_symbol.')', 'woo-rtp' ),
            'placeholder'       => '',
            'desc_tip'          => 'true',
            'description'       => __( 'Enter the custom value in € here. Warning: this will be updated by real time loading rate', 'woo-rtp' ),
            'data_type'         => 'price',
            'type'              => 'number',
            'custom_attributes' => array(
                    'step' 	=> '0.001',
                    'min'	=> '0'
                )
        )
    );

    // Custom Product Number Field
    woocommerce_wp_text_input(
        array(
            'id'                => '_global_margin_percentage',
            'label'             => __( 'Margin Percentage (%)', 'woo-rtp' ),
            'placeholder'       => '',
            'desc_tip'          => 'true',
            'description'       => __( 'Enter the custom % here you like to use.  Warning: if product have variations, if filled, the variation rate will be used instead.', 'woo-rtp' ),
            'data_type'         => 'price',
            'type'              => 'number',
            'custom_attributes' => array(
                    'step' 	=> '0.001',
                    'min'	=> '0'
                )
        )
    );

    // Custom Product Number Field
    woocommerce_wp_text_input(
        array(
            'id'                => '_global_weight_oz',
            'label'             => __( 'Weight (oz)', 'woo-rtp' ),
            'placeholder'       => '',
            'desc_tip'          => 'true',
            'description'       => __( 'Enter the weight for all metals(unit is g)', 'woo-rtp' ),
            'data_type'         => 'price',
            'type'              => 'number',
            'custom_attributes' => array(
                'step' 	=> '0.000001',
                'min'	=> '0'
            )
        )
    );
}

function woocommerce_product_custom_fields_save($post_id){

    $now = right_now();

    // Custom Product REF for sync
    $woocommerce_global_ref = $_POST['_global_REF'];
    if (!empty($woocommerce_global_ref)){
        update_post_meta($post_id, '_global_REF', esc_attr($woocommerce_global_ref));
    }

    // Custom Product last update to update on each sync
    $woocommerce_global_update_date = $_POST['_global_update_date'];
    if (!empty($woocommerce_global_update_date)){
        update_post_meta($post_id, '_global_update_date', esc_attr($woocommerce_global_update_date));
    } else  {
        update_post_meta($post_id, '_global_update_date', esc_attr($now));
    }

    // Custom Product Text Field
    $woocommerce_purchasing_price = $_POST['_purchasing_price'];
    if (!empty($woocommerce_purchasing_price)){
        update_post_meta($post_id, '_purchasing_price', esc_attr($woocommerce_purchasing_price));
    }

    // Custom Product Number Field
    $woocommerce_global_margin_percentage = $_POST['_global_margin_percentage'];
    if (!empty($woocommerce_global_margin_percentage)){
        update_post_meta($post_id, '_global_margin_percentage', esc_attr($woocommerce_global_margin_percentage));
    }

    // Custom Product Number Field
    $woocommerce_global_weight_oz = $_POST['_global_weight_oz'];
    if (!empty($woocommerce_global_weight_oz)){
        update_post_meta($post_id, '_global_weight_oz', esc_attr($woocommerce_global_weight_oz));
    }
}


// Add Variation Settings
 add_action( 'woocommerce_product_after_variable_attributes', 'variation_settings_fields', 10, 3 );

// Save Variation Settings
 add_action( 'woocommerce_save_product_variation', 'save_variation_settings_fields', 10, 2 );

/**
 * Create new fields for variations
 *
*/
function variation_settings_fields( $loop, $variation_data, $variation ) {

    global $woocommerce, $post;
    $cur = get_option('woocommerce_currency');
    $cur_symbol = get_woocommerce_currency_symbol();

    // Custom Product REF for sync
    woocommerce_wp_text_input(
        array(
            'id'                => '_global_REF[' . $variation->ID . ']',
            'label'             => __( 'Reference (CUR)', 'woo-rtp' ),
            'desc_tip'          => 'true',
            'placeholder'       => '',
            'desc_tip'          => 'true',
            'description'       => __( 'Enter the reference you like to use for sync external DATA.', 'woo-rtp' ),
            'value'             => get_post_meta( $variation->ID, '_global_REF', true ),
            'type'              => 'text'
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'                => '_global_update_date[' . $variation->ID . ']',
            'label'             => __( 'Last update date', 'woo-rtp' ),
            'placeholder'       => '',
            'desc_tip'          => 'true',
            'description'       => __( 'Product last\'s update date with external DATA .', 'woo-rtp' ),
            'value'             => get_post_meta( $variation->ID, '_global_update_date', true ),
            'type'              => 'text'
        )
    );

    // Number Field
	woocommerce_wp_text_input(
		array(
			'id'          => '_purchasing_price[' . $variation->ID . ']',
			'label'       => __('Exchange flat rate ('.$cur_symbol.')', 'woo-rtp' ),
			'desc_tip'    => 'true',
			'description' => __( 'Enter the custom value in € here. Warning: this will be updated by real time loading rate', 'woo-rtp' ),
            'value'       => get_post_meta( $variation->ID, '_purchasing_price', true ),
            'type'              => 'number',
			'custom_attributes' => array(
                'step' 	=> '0.001',
                'min'	=> '0'
            )
		)
    );

    woocommerce_wp_text_input(
        array(
            'id'                => '_global_margin_percentage[' . $variation->ID . ']',
            'label'             => __( 'Margin Percentage (%)', 'woo-rtp' ),
            'desc_tip'    => 'true',
            'description'       => __( 'Enter the custom % here you like to use.  Warning: if product have variations, if filled, the variation rate will be used instead.', 'woo-rtp' ),
            'value'       => get_post_meta( $variation->ID, '_global_margin_percentage', true ),
            'type'              => 'number',
            'custom_attributes' => array(
                'step' 	=> '0.001',
                'min'	=> '0'
            )
        )
    );

    woocommerce_wp_text_input(
        array(
            'id'                => '_global_weight_oz[' . $variation->ID . ']',
            'label'             => __( 'Weight (oz)', 'woo-rtp' ),
            'desc_tip'    => 'true',
            'description'       => __( 'Enter the weight for all metals(unit is g)', 'woo-rtp' ),
            'value'       => get_post_meta( $variation->ID, '_global_weight_oz', true ),
            'type'              => 'number',
            'custom_attributes' => array(
                'step' 	=> '0.000001',
                'min'	=> '0'
            )
        )
    );
}

/**
 * Save new fields for variations
 *
*/
function save_variation_settings_fields( $post_id ) {

    $now = right_now();

    // Custom Product REF for sync
    $woocommerce_global_ref = $_POST['_global_REF'][ $post_id ];
    if (!empty($woocommerce_global_ref)){
        update_post_meta($post_id, '_global_REF', esc_attr($woocommerce_global_ref));
    }

    // Custom Product last update to update on each sync
    $woocommerce_global_update_date = $_POST['_global_update_date'][ $post_id ];
    if (!empty($woocommerce_global_update_date)){
        update_post_meta($post_id, '_global_update_date', esc_attr($woocommerce_global_update_date));
    } else {
        update_post_meta($post_id, '_global_update_date', esc_attr($now));
    }

    // Custom Product Text Field
    $woocommerce_purchasing_price = $_POST['_purchasing_price'][ $post_id ];
    if (!empty($woocommerce_purchasing_price)){
        update_post_meta($post_id, '_purchasing_price', esc_attr($woocommerce_purchasing_price));
    }

    // Custom Product Number Field
    $woocommerce_global_margin_percentage = $_POST['_global_margin_percentage'][ $post_id ];
    if (!empty($woocommerce_global_margin_percentage)){
        update_post_meta($post_id, '_global_margin_percentage', esc_attr($woocommerce_global_margin_percentage));
    }

    // Custom Product Number Field
    $woocommerce_global_weight_oz = $_POST['_global_weight_oz'][ $post_id ];
    if (!empty($woocommerce_global_weight_oz)){
        update_post_meta($post_id, '_global_weight_oz', esc_attr($woocommerce_global_weight_oz));
    }
}

/**
 * TO DO
 * the following code is a base to add margin % on categories too
 * need to use _global_margin_percentage
 */
add_action('product_cat_add_form_fields', 'wh_taxonomy_add_new_meta_field', 10, 1);
add_action('product_cat_edit_form_fields', 'wh_taxonomy_edit_meta_field', 10, 1);

//Product Cat Create page
function wh_taxonomy_add_new_meta_field() {
    $wh_meta_title = __('Meta Title', 'wh');
    $wh_enter_meta_title = __('Enter a meta title, <= 60 character', 'wh');
    $wh_ref = __('Reference (CUR)', 'wh');
    $wh_enter_ref = __('Enter the reference you like to use for sync external DATA', 'wh');
    $wh_margin = __('Margin Percentage (%)', 'wh');
    $wh_enter_margin = __('Enter the custom % here you like to use', 'wh');
    $wh_weight = __('Weight (oz)', 'wh');
    $wh_enter_weight = __('Enter the weight for all metals(unit is g)', 'wh');

$meta_field  = <<<EOT
    <div class="form-field">
        <label for="wh_meta_title">$wh_meta_title</label>
        <input type="text" name="wh_meta_title" id="wh_meta_title">
        <p class="description">$wh_enter_meta_title</p>
    </div>
    <div class="form-field">
        <label for="_global_REF">$wh_ref</label>
        <input type="text" name="_global_REF" id="_global_REF">
        <p class="description">$wh_enter_ref</p>
    </div>
    <div class="form-field">
        <label for="_global_margin_percentage">$wh_margin</label>
        <input type="text" name="_global_margin_percentage" id="_global_margin_percentage">
        <p class="description">$wh_enter_margin</p>
    </div>
    <div class="form-field">
        <label for="_global_weight_oz">$wh_weight</label>
        <input type="text" name="_global_weight_oz" id="_global_weight_oz">
        <p class="description">$wh_enter_weight</p>
    </div>
EOT;

    echo $meta_field;
}

//Product Cat Edit page
function wh_taxonomy_edit_meta_field($term) {

    //getting term ID
    $term_id = $term->term_id;

    // retrieve the existing value(s) for this meta field.
    $wh_meta_title_value = get_term_meta($term_id, 'wh_meta_title', true);
    $wh_ref_value = get_term_meta($term_id, '_global_REF', true);
    $wh_margin_value = get_term_meta($term_id, '_global_margin_percentage', true);
    $wh_weight_value = get_term_meta($term_id, '_global_weight_oz', true);

//    $wh_meta_title_clean = esc_attr($wh_meta_title) ? esc_attr($wh_meta_title) : '';
    $wh_meta_title = __('Meta Title', 'wh');
    $wh_meta_desc = __('Enter a meta title, <= 60 character', 'wh');
    $wh_ref = __('Reference (CUR)', 'wh');
    $wh_enter_ref = __('Enter the reference you like to use for sync external DATA', 'wh');
    $wh_margin = __('Margin Percentage (%)', 'wh');
    $wh_enter_margin = __('Enter the custom % here you like to use', 'wh');
    $wh_weight = __('Weight (oz)', 'wh');
    $wh_enter_weight = __('Enter the weight for all metals(unit is g)', 'wh');

$form_field  = <<<EOT
    <tr class="form-field">
        <th scope="row" valign="top"><label for="wh_meta_title">$wh_meta_title</label></th>
        <td>
            <input type="text" name="wh_meta_title" id="wh_meta_title" value="$wh_meta_title_value">
            <p class="description">$wh_meta_desc</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="_global_REF">$wh_ref</label></th>
        <td>
            <input type="text" name="_global_REF" id="_global_REF" value="$wh_ref_value">
            <p class="description">$wh_enter_ref</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="_global_margin_percentage">$wh_margin</label></th>
        <td>
            <input type="text" name="_global_margin_percentage" id="_global_margin_percentage" value="$wh_margin_value">
            <p class="description">$wh_enter_margin</p>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="_global_weight_oz">$wh_weight</label></th>
        <td>
            <input type="text" name="_global_weight_oz" id="_global_weight_oz" value="$wh_weight_value">
            <p class="description">$wh_enter_weight</p>
        </td>
    </tr>
EOT;
    // echo esc_attr($wh_meta_title) ? esc_attr($wh_meta_title) : '';
    echo $form_field;
}

add_action('edited_product_cat', 'wh_save_taxonomy_custom_meta', 10, 1);
add_action('create_product_cat', 'wh_save_taxonomy_custom_meta', 10, 1);

// Save extra taxonomy fields callback function.
function wh_save_taxonomy_custom_meta($term_id) {

    $wh_meta_title = filter_input(INPUT_POST, 'wh_meta_title');
    $wh_ref = filter_input(INPUT_POST, '_global_REF');
    $wh_margin = filter_input(INPUT_POST, '_global_margin_percentage');
    $wh_weight = filter_input(INPUT_POST, '_global_weight_oz');

    update_term_meta($term_id, 'wh_meta_title', $wh_meta_title);
    update_term_meta($term_id, '_global_REF', $wh_ref);
    update_term_meta($term_id, '_global_margin_percentage', $wh_margin);
    update_term_meta($term_id, '_global_weight_oz', $wh_weight);
}

//Displaying Additional Columns
add_filter( 'manage_edit-product_cat_columns', 'wh_customFieldsListTitle' ); //Register Function
add_action( 'manage_product_cat_custom_column', 'wh_customFieldsListDisplay' , 10, 3); //Populating the Columns

/**
 * Meta Title and Description column added to category admin screen.
 *
 * @param mixed $columns
 * @return array
 */
function wh_customFieldsListTitle( $columns ) {
    $columns['pro_meta_title'] = __( 'Meta Title', 'woocommerce' );
    return $columns;
}

/**
 * Meta Title and Description column value added to product category admin screen.
 *
 * @param string $columns
 * @param string $column
 * @param int $id term ID
 *
 * @return string
 */
function wh_customFieldsListDisplay( $columns, $column, $id ) {
    if ( 'pro_meta_title' == $column ) {
        $columns = esc_html( get_term_meta($id, 'wh_meta_title', true) );
    }
    return $columns;
}

/**
 * end of TODO
 */


 /**
  * date & time used for updates
  */
function right_now(){
    date_default_timezone_set("Europe/Paris");
    return date("Y-m-d h:i:s");
}

// Register route to make api call enable
add_action('rest_api_init', function () {
    register_rest_route( 'api/v1', '/cron-update-price', array(
        'methods' => 'GET',
        'callback' => 'handle_cron_func',
    ));
});

// Api endpoint for cron job
function handle_cron_func($data) {

    $price_helper = new PriceHelper();
    $price_helper->init();

    // Get categories
    $product_categories = get_terms(array(
        'taxonomy'     => 'product_cat',
        'orderby'      => 'name',
        'order'        => 'ASC',
        'show_count'   => 0,
        'pad_counts'   => 0,
        'hierarchical' => 1,
        'title_li'     => '',
        'hide_empty'   => 1
    ));

    $categories_count = count($product_categories);

    var_dump('categories count '.$categories_count);

    foreach ($product_categories as $product_category) {

        $category_id = $product_category->term_id;
        $category_slug = $product_category->slug;
        $category_margin = get_term_meta($category_id, '_global_margin_percentage', true);
        $category_symbol = get_term_meta($category_id, '_global_REF', true);

        list($post_type, $new_purchasing_price) = $price_helper->check_symbol($category_symbol, '', 0);
//        var_dump('category id '.$category_id.' margin '.$category_margin.' symbol '.$category_symbol);

        // Get products in this category(search with <slug> field)
        $args = array(
            'posts_per_page' => -1,
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $category_slug
                )
            ),
            'post_type' => 'product',
            'orderby' => 'title,'
        );
        $products = new WP_Query( $args );

        // Check searched products
        while ($products->have_posts()) {
            $products->the_post();
            $product_id = get_the_ID();
            $product = new WC_Product( $product_id );

            $product_margin = get_post_meta($product_id, '_global_margin_percentage', true);
            // Get new purchasing price from the product symbol
            $product_symbol = get_post_meta($product_id, '_global_REF', true);
            list($post_type, $new_purchasing_price) = $price_helper->check_symbol($product_symbol, $post_type, $new_purchasing_price);
//            var_dump('product margin '.$product_margin.' symbol '.$product_symbol);

            // Get product variations
            $args = array(
                'post_type'     => 'product_variation',
                'numberposts'   => -1,
                'orderby'       => 'menu_order',
                'order'         => 'asc',
                'post_parent'   => $product_id
            );
            $variations = get_posts( $args );

            // Update product price
            $current_selling_price = floatval($product->get_regular_price());
            $ratio = $price_helper->get_ratio($category_margin, $product_margin, 0);
            if ($ratio > 0) {       // This means there is margin value exist
                $new_selling_price = $new_purchasing_price * $ratio;
                if ($post_type == METAL) {
                    $metal_weight = floatval(get_post_meta($product_id, '_global_weight_oz', true)) / 1000;     // convert g to kg
                    $new_selling_price *= $metal_weight;
                    var_dump('metal product weight '.$metal_weight.' new selling price '.$new_selling_price);
                }
                if ($new_selling_price != $current_selling_price) {     // Update only when the price is different
                    $product->set_regular_price(strval($new_selling_price));
                    $product->save();
                    var_dump('update selling price of product id '.$product_id.' to '.strval($new_selling_price));
                }
            }

            if (count($variations) > 0) {
                // There exist variations, then update the price of the variations
                foreach ( $variations as $variation ) {
                    $variation_id = $variation->ID;
                    $product_variation = new WC_Product_Variation( $variation_id );

                    $variant_margin = get_post_meta($variation_id, '_global_margin_percentage', true);
                    // Get new purchasing price from the variant symbol
                    // If variant symbol does not exist in the currency symbols, use product symbol
                    $variant_symbol = get_post_meta($variation_id, '_global_REF', true);
                    list($post_type, $new_purchasing_price) = $price_helper->check_symbol($variant_symbol, $post_type, $new_purchasing_price);
                    var_dump('variant margin '.$variant_margin.' symbol '.$variant_symbol);

                    $current_selling_price = floatval($product_variation->get_regular_price());
                    $ratio = $price_helper->get_ratio($category_margin, $product_margin, $variant_margin);
                    if ($ratio > 0) {       // This means there is margin value exist
                        $new_selling_price = $new_purchasing_price * $ratio;
                        if ($post_type == METAL) {
                            $metal_weight = floatval(get_post_meta($variation_id, '_global_weight_oz', true)) / 1000;     // convert g to kg
                            $new_selling_price *= $metal_weight;
                            var_dump('metal variant weight '.$metal_weight.' new selling price '.$new_selling_price);
                        }
                        if ($new_selling_price != $current_selling_price) {     // Update only when the price is different
                            $product_variation->set_regular_price(strval($new_selling_price));
                            $product_variation->save();
                            var_dump('update selling price of variation id '.$variation_id
                                .' of product id '.$product_id.' to '.strval($new_selling_price));
                        }
                    }
                }
            }
        }
    }

    echo 'Price updating module finished ';
}

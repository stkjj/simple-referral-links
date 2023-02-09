<?php

/*
Plugin Name: Referral Links Plugin
Plugin URI: https://github.com/stkjj/simple-referral-links
GitHub Plugin URI: https://github.com/stkjj/simple-referral-links
Description: A plugin for tracking sales through referral links in WooCommerce
Version: 1.0
Author: KeDe Digital LLP
Author URI: https://digital-bridge.de
*/

function referral_links_plugin_init() {
    // Check if WooCommerce is active
    if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
        return;
    }

    // Add the custom field to the WooCommerce user profile
    function referral_links_add_custom_field( $user ) {
        $referral_link = get_user_meta( $user->ID, 'referral_link', true );
        ?>
        <h3>Referral Link Information</h3>
        <table class="form-table">
            <tr>
                <th><label for="referral_link">Referral Link</label></th>
                <td>
                    <input type="text" name="referral_link" id="referral_link" value="<?php echo esc_attr( $referral_link ); ?>" class="regular-text" /><br />
                    <span class="description">Enter your referral link here.</span>
                </td>
            </tr>
        </table>
        <?php
    }

    // Save the custom field data
    function referral_links_save_custom_field( $user_id ) {
        if ( !current_user_can( 'edit_user', $user_id ) )
            return false;
        update_user_meta( $user_id, 'referral_link', $_POST['referral_link'] );
    }

    // Add the custom field to the WooCommerce user profile
    add_action( 'show_user_profile', 'referral_links_add_custom_field' );
    add_action( 'edit_user_profile', 'referral_links_add_custom_field' );

    // Save the custom field data
    add_action( 'personal_options_update', 'referral_links_save_custom_field' );
    add_action( 'edit_user_profile_update', 'referral_links_save_custom_field' );
    
    // Add a custom column to the orders list in the dashboard to show the referral link used
    function referral_links_add_order_column( $columns ) {
        $columns['referral_link'] = 'Referral Link';
        return $columns;
    }

    // Display the referral link used in the custom column in the orders list
    function referral_links_show_order_column_data( $column, $post_id ) {
        if ( $column == 'referral_link' ) {
            $order = wc_get_order( $post_id );
            $referral_link = get_post_meta( $order->get_id(), 'referral_link', true );
            if ( !empty( $referral_link ) ) {
                echo $referral_link;
            }
        }
    }
    
function referer_dashboard() {
    // Get the referer's user ID
    $referer_id = get_current_user_id();
    
    // Get the referer's referral link
    $referral_link = get_user_meta($referer_id, 'referral_link', true);
    
    // Get the default commission rate
    $default_commission_rate = get_option('commission_rate', 0);
    
    // Get the referer's referrals
    $referrals = get_users(array(
        'meta_key' => 'referral_link',
        'meta_value' => $referral_link,
    ));
    
    // Calculate the total amount purchased by the referer's referrals
    $total_amount = 0;
    foreach ($referrals as $referral) {
        // Get the orders made by the referral
        $orders = wc_get_orders(array(
            'customer_id' => $referral->ID,
        ));
        
        // Add the total of each order to the total amount
        foreach ($orders as $order) {
            $order_items = $order->get_items();
            
            // Loop through the items in the order
            foreach ($order_items as $item) {
                // Get the product
                $product = $item->get_product();
                
                // Get the product commission rate
                $product_commission_rate = get_post_meta($product->get_id(), 'commission_rate', true);
                
                // If a product commission rate is set, use it
                if ($product_commission_rate) {
                    $commission_rate = $product_commission_rate;
                } else {
                    // Otherwise, check for a category commission rate
                    $product_categories = get_the_terms($product->get_id(), 'product_cat');
                    foreach ($product_categories as $category) {
                        $category_commission_rate = get_term_meta($category->term_id, 'commission_rate', true);
                        if ($category_commission_rate) {
                            $commission_rate = $category_commission_rate;
                            break;
                        }
                    }
                    
                    // If no category commission rate is set, use the default commission rate
                    if (!isset($commission_rate)) {
                        $commission_rate = $default_commission_rate;
                    }
                }
                
                // Add the item total to the total amount
                $total_amount += $item->get_total();
            }
        }
    }
    
    // Calculate the referer's commission
    $commission = $total_amount * $commission_rate / 100;
    
    // Output the dashboard
	?>    
	<h2>Referral Dashboard</h2>
    <p>Referral Link: <?php echo $referral_link; ?></p>
    <p>Total Purchased: <?php echo wc_price($total_purchased); ?></p>
    <p>Commission Rate: <?php echo $commission_rate; ?>%</p>
    <p>Commission: <?php echo wc_price($commission); ?></p>
    <?php
    
    function handle_commissions_paid_out() {
    // Check if the commission flag has been set
    if (get_option('commissions_paid_out', false)) {
        // If the commission flag has been set, display a message
        echo '<p>Commissions have already been paid out for this period.</p>';
        
        // Return early to prevent further processing
        return;
  	  }
    
    // Otherwise, display a form for paying out commissions
    echo '<form action="' . esc_url(admin_url('admin-post.php')) . '" method="post">';
    echo '<input type="hidden" name="action" value="pay_out_commissions">';
    echo '<input type="submit" value="Pay Out Commissions">';
    echo '</form>';
	}

	// Handle the form submission
	add_action('admin_post_pay_out_commissions', 'pay_out_commissions');

	function pay_out_commissions() {
		// Set the commission flag to indicate that commissions have been paid out
		update_option('commissions_paid_out', true);
	
		// Redirect the user back to the referer dashboard
		wp_redirect(admin_url('admin.php?page=referer_dashboard'));
		exit;
	}
}
}

// Initialize the plugin
add_action( 'plugins_loaded', 'referral_links_plugin_init' );
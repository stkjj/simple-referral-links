<?

function referral_links_add_settings_page() {
    add_submenu_page(
        'woocommerce',
        'Referral Links Settings',
        'Referral Links Settings',
        'manage_options',
        'referral-links-settings',
        'referral_links_render_settings_page'
    );
}

function referral_links_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Referral Links Settings</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'referral_links_settings' );
            do_settings_sections( 'referral_links_settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function referral_links_register_settings() {
    register_setting( 'referral_links_settings', 'referral_links_settings' );

    add_settings_section(
        'referral_links_general',
        'General Settings',
        'referral_links_render_general_section',
        'referral_links_settings'
    );

    add_settings_field(
        'default_commission_rate',
        'Default Commission Rate',
        'referral_links_render_default_commission_rate_field',
        'referral_links_settings',
        'referral_links_General'
    );
}

function referral_links_render_General_section() {
    echo '<p>Settings for the Referral Links plugin.</p>';
}

function referral_links_render_default_commission_rate_field() {
    $settings = get_option( 'referral_links_settings' );
    $default_commission_rate = ! empty( $settings['default_commission_rate'] ) ? $settings['default_commission_rate'] : '';
    ?>
    <input type="text" name="referral_links_settings[default_commission_rate]" value="<?php echo esc_attr( $default_commission_rate ); ?>">
    <?php
}

add_action( 'admin_menu', 'referral_links_add_settings_page' );
add_action( 'admin_init', 'referral_links_register_settings' );

<?php
/**
 * Plugin Name:       Premmerce Product Filter for WooCommerce
 * Plugin URI:        https://premmerce.com/woocommerce-product-filter/
 * Description:       Premmerce Product Filter for WooCommerce plugin is a convenient and flexible tool for managing filters for WooCommerce products.
 * Version:     3.8.4
 *  *
 * Author:            Premmerce
 * Author URI:        https://premmerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       premmerce-filter
 * Domain Path:       /languages
 *
 * Tested up to: 7.1
 * WC requires at least: 3.6.0
 * WC tested up to: 11.1.2
 *
 *  *
 *
  */

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

if (! function_exists('premmerce_pwpf_fs')) {
	call_user_func(
		function () {
			include_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
			include_once plugin_dir_path(__FILE__) . '/freemius.php';
			$main = new Premmerce\Filter\FilterPlugin(__FILE__);

			register_activation_hook(__FILE__, [$main, 'activate']);

			register_deactivation_hook(__FILE__, [$main, 'deactivate']);

			// Freemius registers its own uninstall hook, which a second WordPress uninstall hook here
			// would replace, so its uninstall event would never be sent.
			premmerce_pwpf_fs()->add_action('after_uninstall', [Premmerce\Filter\FilterPlugin::class, 'uninstall']);
			
			$main->run();
		}
	);
}

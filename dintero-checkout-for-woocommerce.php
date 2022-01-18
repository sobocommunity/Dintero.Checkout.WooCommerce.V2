<?php //phpcs:ignore
/**
 * Plugin Name: Dintero Checkout for WooCommerce
 * Plugin URI: @TODO: Add plugin URI
 * Description: Dintero Checkout for WooCommerce.
 * Author: Krokedil
 * Author URI: https://krokedil.com/
 * Version: 0.1.0
 * Text Domain: dintero-checkout-for-woocommerce
 * Domain Path: /languages
 *
 * WC requires at least: 6.1.0
 * WC tested up to: @TODO:
 *
 * Copyright (c) 2022 Krokedil
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DINTERO_CHECKOUT_VERSION', '1.0.0' );
define( 'DINTERO_CHECKOUT_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'DINTERO_CHECKOUT_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );


if ( ! class_exists( 'Dintero' ) ) {

	class Dintero {

		/**
		 * The reference the *Singleton* instance of this class.
		 *
		 * @var Dintero $instance
		 */
		private static $instance;

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @return Dintero The *Singleton* instance.
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
		/**
		 * Private clone method to prevent cloning of the instance of the
		 * *Singleton* instance.
		 *
		 * @return void
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Private unserialize method to prevent unserializing of the *Singleton*
		 * instance.
		 *
		 * @return void
		 */
		public function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Nope' ), '1.0' );
		}

		/**
		 * Class constructor.
		 */
		public function __construct() {
			load_plugin_textdomain( 'dintero-checkout-for-woocommerce', false, plugin_basename( __DIR__ ) . '/languages' );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		}

		/**
		 * Add plugin action links.
		 *
		 * @param array $links Plugin action link before filtering.
		 * @return array Filtered links.
		 */
		public function plugin_action_links( $links ) {
			$settings_link = $this->get_settings_link();
			$plugin_links  = array(
				'<a href="' . $settings_link . '">' . __( 'Settings', 'dintero-for-woocommerce' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Get URL link to Dintero's settings in WooCommerce.
		 *
		 * @return string Settings link
		 */
		public function get_settings_link() {
			$section_slug = 'dintero';

			$params = array(
				'page'    => 'wc-settings',
				'tab'     => 'checkout',
				'section' => $section_slug,
			);

			// admin url
			return esc_url( add_query_arg( $params, 'admin.php' ) );
		}
	}

	Dintero::get_instance();
}

/**
 * Main instance Dintero.
 *
 * Returns the main instance of Dintero.
 *
 * @return Dintero
 */
function Dintero() {                  // phpcs:ignore WordPress.NamingConventions.ValidFunctionName
	return Dintero::get_instance();
}

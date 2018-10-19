<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Main class
 *
 * @class   YITH_WC_Coupon_Email_System_Premium
 * @package Yithemes
 * @since   1.0.0
 * @author  Your Inspiration Themes
 */

if ( ! class_exists( 'YITH_WC_Coupon_Email_System_Premium' ) ) {

	class YITH_WC_Coupon_Email_System_Premium extends YITH_WC_Coupon_Email_System {

		/**
		 * @var array
		 */
		var $_date_placeholders = array();

		/**
		 * @var array
		 */
		var $_date_formats = array();

		/**
		 * @var array
		 */
		var $_date_patterns = array();

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WC_Coupon_Email_System_Premium
		 * @since 1.0.0
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {

				self::$instance = new self( $_REQUEST );

			}

			return self::$instance;

		}

		/**
		 * Constructor
		 *
		 * @since   1.0.0
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function __construct() {

			parent::__construct();

			$this->_email_templates   = array(
				'ywces-1' => array(
					'folder' => '/emails/template-1',
					'path'   => YWCES_TEMPLATE_PATH
				),
				'ywces-2' => array(
					'folder' => '/emails/template-2',
					'path'   => YWCES_TEMPLATE_PATH
				),
				'ywces-3' => array(
					'folder' => '/emails/template-3',
					'path'   => YWCES_TEMPLATE_PATH
				),
			);
			$this->_date_placeholders = $this->get_date_placeholders();
			$this->_date_formats      = $this->get_date_formats();
			$this->_date_patterns     = $this->get_date_patterns();

			// register plugin to licence/update system
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

			$this->includes_premium();

			add_action( 'init', array( $this, 'init_multivendor_integration' ), 20 );

			add_filter( 'yith_wcet_email_template_types', array( $this, 'add_yith_wcet_template' ) );

			if ( is_admin() ) {

				add_action( 'admin_notices', array( $this, 'ywces_admin_notices_premium' ) );
				add_filter( 'ywces_check_active_options_premium', array( $this, 'check_active_options_premium' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_premium' ) );
				add_filter( 'ywces_admin_scripts_filter', array( $this, 'ywces_admin_scripts_filter' ) );

				add_action( 'show_user_profile', array( $this, 'add_birthday_field_admin' ) );
				add_action( 'edit_user_profile', array( $this, 'add_birthday_field_admin' ) );

				add_action( 'personal_options_update', array( $this, 'save_birthday_field_admin' ) );
				add_action( 'edit_user_profile_update', array( $this, 'save_birthday_field_admin' ) );

			} else {

				add_action( 'woocommerce_edit_account_form', array( $this, 'add_birthday_field' ) );
				add_action( 'woocommerce_register_form', array( $this, 'add_birthday_field' ) );
				add_action( 'woocommerce_save_account_details', array( $this, 'save_birthday_field' ) );
				add_action( 'woocommerce_created_customer', array( $this, 'save_birthday_field' ), 10, 1 );
				add_filter( 'woocommerce_checkout_fields', array( $this, 'add_birthday_field_checkout' ) );

			}

			if ( get_option( 'ywces_coupon_purge' ) == 'yes' ) {

				add_action( 'ywces_trash_coupon_cron', array( $this, 'trash_expired_coupons' ) );

			}

			add_action( 'ywces_user_purchase_premium', array( $this, 'ywces_user_purchase_premium' ), 10, 5 );
			add_action( 'ywces_daily_send_mail_job', array( $this, 'ywces_daily_send_mail_job' ) );
			//add_action( 'user_register', array( $this, 'ywces_user_registration' ), 10 );

		}

		/**
		 * Files inclusion
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		private function includes_premium() {

			include_once( 'includes/class-ywces-mandrill.php' );

			if ( is_admin() ) {

				include_once( 'templates/admin/class-ywces-custom-table.php' );
				include_once( 'templates/admin/class-ywces-custom-collapse.php' );
				include_once( 'templates/admin/class-ywces-custom-coupon.php' );
				include_once( 'templates/admin/class-ywces-custom-mailskin.php' );
				include_once( 'templates/admin/class-ywces-custom-coupon-purge.php' );
				include_once( 'templates/admin/class-yith-wc-custom-product-select.php' );
				include_once( 'includes/class-ywces-ajax-premium.php' );

			}

		}

		/**
		 * If is active YITH WooCommerce Email Templates, add YWCES to list
		 *
		 * @since   1.0.1
		 *
		 * @param   $templates
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function add_yith_wcet_template( $templates ) {

			$templates[] = array(
				'id'   => 'yith-coupon-email-system',
				'name' => 'YITH WooCommerce Coupon Email System',
			);

			return $templates;

		}

		/**
		 * Check if YITH WooCommerce Multi Vendor is active
		 *
		 * @since   1.0.5
		 * @return  bool
		 * @author  Alberto Ruggiero
		 */
		public function is_multivendor_active() {

			return defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM;

		}

		/**
		 * Check if YITH WooCommerce Email Templates is active
		 *
		 * @since   1.0.5
		 * @return  bool
		 * @author  Alberto Ruggiero
		 */
		public function is_email_templates_active() {

			return defined( 'YITH_WCET_PREMIUM' ) && YITH_WCET_PREMIUM;

		}

		/**
		 * ADMIN FUNCTIONS
		 */

		/**
		 * Initializes CSS and javascript
		 *
		 * @since   1.0.5
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function admin_scripts_premium() {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'ywces-admin-premium', YWCES_ASSETS_URL . '/js/ywces-admin-premium' . $suffix . '.js', array( 'jquery' ), YWCES_VERSION );

		}

		/**
		 * Add YITH WooCommerce Multi Vendor integration
		 *
		 * @since   1.0.5
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function init_multivendor_integration() {

			if ( $this->is_multivendor_active() ) {

				include_once( 'includes/class-ywces-multivendor.php' );

				$this->_available_coupons = YWCES_MultiVendor()->get_vendor_coupons();

			}

		}

		/**
		 * Add premium strings for localization
		 *
		 * @since   1.0.0
		 *
		 * @param   $strings
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function ywces_admin_scripts_filter( $strings ) {

			$strings['test_mail_no_threshold'] = __( 'You need to set at least a threshold to send a test email', 'yith-woocommerce-coupon-email-system' );
			$strings['test_mail_no_product']   = __( 'Please select at least a product', 'yith-woocommerce-coupon-email-system' );
			$strings['test_mail_no_amount']    = __( 'You need to select at least the amount/percentage of a coupon to send it in a test email', 'yith-woocommerce-coupon-email-system' );
			$strings['test_mail_days_elapsed'] = __( 'Please specify the number of days', 'yith-woocommerce-coupon-email-system' );

			if ( $this->is_multivendor_active() ) {

				if ( YWCES_MultiVendor()->vendors_coupon_active() ) {

					$vendor               = yith_get_vendor( 'current', 'user' );
					$strings['vendor_id'] = $vendor->id;

				}

			}

			return $strings;

		}

		/**
		 * Advise if the plugin cannot be performed
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywces_admin_notices_premium() {

			if ( get_option( 'ywces_mandrill_enable' ) == 'yes' && get_option( 'ywces_mandrill_apikey' ) == '' ) : ?>
                <div class="error">
                    <p>
						<?php _e( 'Please enter Mandrill API Key for YITH WooCommerce Coupon Email System', 'yith-woocommerce-coupon-email-system' ); ?>
                    </p>
                </div>
			<?php endif;

		}

		/**
		 * Check if active options have a coupon assigned
		 *
		 * @since   1.0.0
		 *
		 * @param   $messages
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function check_active_options_premium( $messages ) {

			if ( isset( $_POST['ywces_enable_first_purchase'] ) && '1' == $_POST['ywces_enable_first_purchase'] ) {

				if ( $_POST['ywces_coupon_first_purchase'] == '' ) {

					$messages[] = __( 'You need to select a coupon to send one for a new first purchase.', 'yith-woocommerce-coupon-email-system' );

				}

			}

			if ( isset( $_POST['ywces_enable_purchases'] ) && '1' == $_POST['ywces_enable_purchases'] ) {

				if ( ! isset( $_POST['ywces_thresholds_purchases'] ) ) {

					$messages[] = __( 'You need to set a threshold to send a coupon once a user reaches a specific number of purchases.', 'yith-woocommerce-coupon-email-system' );
					update_option( 'ywces_thresholds_purchases', '' );

				} else {

					$count = 0;

					foreach ( maybe_unserialize( $_POST['ywces_thresholds_purchases'] ) as $threshold ) {

						if ( $threshold['coupon'] == '' ) {

							$count ++;

						}

					}

					if ( $count > 0 ) {
						$messages[] = __( 'You need to set a coupon for each threshold to send one when users reach a specific number of purchases.', 'yith-woocommerce-coupon-email-system' );
					}

				}

			}

			if ( isset( $_POST['ywces_enable_spending'] ) && '1' == $_POST['ywces_enable_spending'] ) {

				if ( ! isset( $_POST['ywces_thresholds_spending'] ) ) {

					$messages[] = __( 'You need to set a threshold to send a coupon once a user reaches a specific spent amount.', 'yith-woocommerce-coupon-email-system' );
					update_option( 'ywces_thresholds_spending', '' );

				} else {

					$count = 0;

					foreach ( maybe_unserialize( $_POST['ywces_thresholds_spending'] ) as $threshold ) {

						if ( $threshold['coupon'] == '' ) {

							$count ++;

						}

					}

					if ( $count > 0 ) {

						$messages[] = __( 'You need to set a coupon for each threshold to send one when users reach a specific spent amount.', 'yith-woocommerce-coupon-email-system' );
					}

				}

			}

			if ( isset( $_POST['ywces_enable_product_purchasing'] ) && '1' == $_POST['ywces_enable_product_purchasing'] ) {

				if ( ! isset( $_POST['ywces_targets_product_purchasing'] ) || $_POST['ywces_targets_product_purchasing'] == '' ) {

					$messages[] = __( 'You need to select at least one product to send a coupon once purchased.', 'yith-woocommerce-coupon-email-system' );

				}

				$coupon = maybe_unserialize( $_POST['ywces_coupon_product_purchasing'] );

				if ( $coupon['coupon_amount'] == '' ) {

					$messages[] = __( 'You need to select at least the amount/percentage of a coupon to send it for the purchase of a specific product.', 'yith-woocommerce-coupon-email-system' );

				}

			}

			if ( isset( $_POST['ywces_enable_birthday'] ) && '1' == $_POST['ywces_enable_birthday'] ) {

				$coupon = maybe_unserialize( $_POST['ywces_coupon_birthday'] );

				if ( $coupon['coupon_amount'] == '' ) {

					$messages[] = __( 'You need to select at least the amount/percentage of a coupon to send it for the birthday of a user.', 'yith-woocommerce-coupon-email-system' );

				}

			}

			if ( isset( $_POST['ywces_enable_last_purchase'] ) && '1' == $_POST['ywces_enable_last_purchase'] ) {

				$coupon = maybe_unserialize( $_POST['ywces_coupon_last_purchase'] );

				if ( $coupon['coupon_amount'] == '' ) {

					$messages[] = __( 'You need to select at least the amount/percentage of a coupon to send it after a specific number of days following the last order.', 'yith-woocommerce-coupon-email-system' );

				}

			}

			return $messages;

		}

		/**
		 * Trigger coupons on user purchase
		 *
		 * @since   1.0.0
		 *
		 * @param   $order
		 * @param   $customer_id
		 * @param   $order_count
		 * @param   $order_date
		 * @param   $billing_email
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywces_user_purchase_premium( WC_Order $order, $customer_id, $order_count, $order_date, $billing_email ) {


			if ( count( $this->_available_coupons ) > 0 ) {

				//check if uses has reached an order threshold
				if ( get_option( 'ywces_enable_purchases' ) == 'yes' ) {

					$purchase_threshold = $this->check_threshold( $order_count, 'purchases', $customer_id );

					if ( ! empty( $purchase_threshold ) ) {

						$coupon_code = $purchase_threshold['coupon_id'];

						if ( $this->check_if_coupon_exists( $coupon_code ) ) {

							$args = array(
								'order_date' => $order_date,
								'threshold'  => $purchase_threshold['threshold'],
							);

							$this->bind_coupon( $coupon_code, $billing_email );

							$email_result = YWCES_Emails()->prepare_coupon_mail( $customer_id, 'purchases', $coupon_code, $args );

						}

						return;

					}

				}

				//$money_spent = get_user_meta( $customer_id, '_money_spent', true );
				$money_spent = ywces_total_spent( $customer_id );

				//check if uses has reached a spending threshold
				if ( get_option( 'ywces_enable_spending' ) == 'yes' ) {

					$spending_threshold = $this->check_threshold( $money_spent, 'spending', $customer_id );

					if ( ! empty( $spending_threshold ) ) {

						$coupon_code = $spending_threshold['coupon_id'];

						if ( $this->check_if_coupon_exists( $coupon_code ) ) {

							$args = array(
								'order_date' => $order_date,
								'threshold'  => $spending_threshold['threshold'],
								'expense'    => $money_spent,
							);

							$this->bind_coupon( $coupon_code, $billing_email );

							$email_result = YWCES_Emails()->prepare_coupon_mail( $customer_id, 'spending', $coupon_code, $args );

						}

						return;

					}

				}

			}

			if ( get_option( 'ywces_enable_product_purchasing' ) == 'yes' && get_option( 'ywces_targets_product_purchasing' ) != '' ) {

				$is_deposits = yit_get_prop( $order, '_created_via' ) == 'yith_wcdp_balance_order';

				if ( ! $is_deposits ) {

					$target_products = get_option( 'ywces_targets_product_purchasing' );
					$target_products = is_array( $target_products ) ? $target_products : explode( ',', $target_products );
					$order_items     = $order->get_items();
					$found_product   = '';
					foreach ( $order_items as $item ) {

						$product_id = ( $item['variation_id'] != '0' ? $item['variation_id'] : $item['product_id'] );

						if ( in_array( $product_id, $target_products ) && $found_product == '' ) {

							$found_product = $product_id;
						}

					}

					if ( $found_product != '' ) {

						$coupon_code = $this->create_coupon( $customer_id, 'product_purchasing' );
						$args        = array(
							'order_date' => $order_date,
							'product'    => $found_product,
						);

						$email_result = YWCES_Emails()->prepare_coupon_mail( $customer_id, 'product_purchasing', $coupon_code, $args );

					}

				}

			}

		}

		/**
		 * Check if a threshold is reached and returns the coupon code and the threshold
		 *
		 * @since   1.0.0
		 *
		 * @param   $amount
		 * @param   $type
		 * @param   $customer_id
		 * @param   $vendor_id
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function check_threshold( $amount, $type, $customer_id = false, $vendor_id = '' ) {

			$thresholds        = get_option( 'ywces_thresholds_' . $type . ( apply_filters( 'ywces_set_vendor_id', '', $vendor_id ) ) );
			$closest_threshold = 0;
			$result            = array();

			if ( $thresholds != '' ) {

				foreach ( $thresholds as $key => $threshold ) {

					if ( $amount >= $threshold['amount'] && $closest_threshold < $threshold['amount'] ) {

						$customers = isset( $threshold['customers'] ) ? explode( ',', $threshold['customers'] ) : array();

						if ( ! empty( $customers ) && in_array( $customer_id, $customers ) ) {
							continue;
						}

						$closest_threshold = $threshold['amount'];
						$result            = array( 'coupon_id' => $threshold['coupon'], 'threshold' => $threshold['amount'] );

						if ( $customer_id ) {

							$customers[]                     = $customer_id;
							$thresholds[ $key ]['customers'] = implode( ',', $customers );
							update_option( 'ywces_thresholds_' . $type . ( apply_filters( 'ywces_set_vendor_id', '', $vendor_id ) ), $thresholds );

						}

					}

				}

			}

			return $result;

		}

		/**
		 * Creates a coupon with specific settings
		 *
		 * @since   1.0.0
		 *
		 * @param   $user_id
		 * @param   $type
		 * @param   $coupon_args
		 * @param   $vendor_id
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function create_coupon( $user_id, $type, $coupon_args = array(), $vendor_id = '' ) {

			$user_nickname      = get_user_meta( $user_id, 'nickname', true );
			$user_email         = get_user_meta( $user_id, 'billing_email', true );
			$coupon_first_part  = apply_filters( 'ywces_coupon_code_first_part', $user_nickname );
			$coupon_separator   = apply_filters( 'ywces_coupon_code_separator', '-' );
			$coupon_second_part = apply_filters( 'ywces_coupon_code_second_part', current_time( 'YmdHis' ) );

			$coupon_code = $coupon_first_part . $coupon_separator . $coupon_second_part . $vendor_id;

			$coupon_desc = '';

			switch ( $type ) {

				case 'last_purchase':
					$coupon_desc = __( 'On a specific number of days from the last purchase', 'yith-woocommerce-coupon-email-system' );
					break;

				case 'birthday':
					$coupon_desc = __( 'On customer birthday', 'yith-woocommerce-coupon-email-system' );
					break;

				case 'product_purchasing':
					$coupon_desc = __( 'On specific product purchase', 'yith-woocommerce-coupon-email-system' );
					break;

			}

			$coupon_data = array(
				'post_title'   => $coupon_code,
				'post_content' => '',
				'post_excerpt' => $coupon_desc,
				'post_status'  => 'publish',
				'post_author'  => apply_filters( 'ywces_set_coupon_author', 0, $vendor_id ),
				'post_type'    => 'shop_coupon'
			);

			$coupon_id = wp_insert_post( $coupon_data );

			if ( empty( $coupon_args ) ) {

				$option_suffix = '';

				if ( $vendor_id != '' ) {

					$option_suffix = '_' . $vendor_id;

				}

				$coupon_option = get_option( 'ywces_coupon_' . $type . $option_suffix );

			} else {

				$coupon_option = $coupon_args;

			}

			$ve          = get_option( 'gmt_offset' ) > 0 ? '+' : '-';
			$expiry_date = ( $coupon_option['expiry_days'] != '' ) ? date( 'Y-m-d', strtotime( '+' . $coupon_option['expiry_days'] . ' days' . $ve . get_option( 'gmt_offset' ) . ' HOURS' ) ) : '';

			update_post_meta( $coupon_id, 'discount_type', $coupon_option['discount_type'] );
			update_post_meta( $coupon_id, 'coupon_amount', $coupon_option['coupon_amount'] );
			update_post_meta( $coupon_id, 'individual_use', ( isset( $coupon_option['individual_use'] ) && $coupon_option['individual_use'] != '' ? 'yes' : 'no' ) );
			update_post_meta( $coupon_id, 'usage_limit', '1' );
			update_post_meta( $coupon_id, 'expiry_date', $expiry_date );
			update_post_meta( $coupon_id, 'customer_email', $user_email );
			update_post_meta( $coupon_id, 'minimum_amount', $coupon_option['minimum_amount'] );
			update_post_meta( $coupon_id, 'maximum_amount', $coupon_option['maximum_amount'] );
			update_post_meta( $coupon_id, 'free_shipping', ( isset( $coupon_option['free_shipping'] ) && $coupon_option['free_shipping'] != '' ? 'yes' : 'no' ) );
			update_post_meta( $coupon_id, 'exclude_sale_items', ( isset( $coupon_option['exclude_sale_items'] ) && $coupon_option['exclude_sale_items'] != '' ? 'yes' : 'no' ) );

			do_action( 'ywces_additional_coupon_features', $coupon_id, $type, $coupon_option );


			if ( $vendor_id != '' ) {
				$vendor      = yith_get_vendor( $vendor_id, 'vendor' );
				$product_ids = implode( ',', $vendor->get_products() );

				update_post_meta( $coupon_id, 'vendor_id', $vendor_id );
				update_post_meta( $coupon_id, 'product_ids', $product_ids );
			}

			update_post_meta( $coupon_id, 'generated_by', 'ywces' );

			return $coupon_code;

		}

		/**
		 * Daily cron job
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function ywces_daily_send_mail_job() {

			if ( get_option( 'ywces_enable_last_purchase' ) == 'yes' ) {

				$users = $this->get_customers_id_by_last_purchase();

				if ( ! empty( $users ) ) {

					foreach ( $users as $customer_id ) {

						$coupon_code = $this->create_coupon( $customer_id, 'last_purchase' );

						$args = array(
							'days_ago' => get_option( 'ywces_days_last_purchase' )
						);

						$email_result = YWCES_Emails()->prepare_coupon_mail( $customer_id, 'last_purchase', $coupon_code, $args );

						//Set the user to not receive another coupon until he does a new purchase
						update_user_meta( $customer_id, '_last_purchase_coupon_sent', 'yes' );

					}

				}

			}

			if ( get_option( 'ywces_enable_birthday' ) == 'yes' ) {

				$users = $this->get_customers_id_by_birthdate();

				if ( ! empty( $users ) ) {

					foreach ( $users as $customer_id ) {

						$coupon_code = $this->create_coupon( $customer_id, 'birthday' );

						$email_result = YWCES_Emails()->prepare_coupon_mail( $customer_id, 'birthday', $coupon_code );

					}

				}

			}

		}

		/**
		 * Get a list of id of customers by birthdate who need to receive the coupon
		 *
		 * @since   1.0.0
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_customers_id_by_birthdate() {
			global $wpdb;

			$customers_ids = array();

			$user_query = $wpdb->get_results( "SELECT user_id FROM {$wpdb->base_prefix}usermeta WHERE meta_key = 'ywces_birthday' AND MONTH(meta_value) = MONTH(NOW()) AND DAY(meta_value) = DAY(NOW())" );

			if ( ! empty( $user_query ) ) {

				foreach ( $user_query as $user ) {

					$customers_ids[] = $user->user_id;

				}

			}

			return $customers_ids;

		}

		/**
		 * Set custom where condition
		 *
		 * @since   1.0.0
		 *
		 * @param   $where
		 *
		 * @return  string
		 * @author  Alberto Ruggiero
		 */
		public function ywces_filter_where_orders( $where = '' ) {

			$days = get_option( 'ywces_days_last_purchase' );

			$where .= " AND post_date <= '" . date( 'Y-m-d', strtotime( '-' . $days . ' days' ) ) . "'";

			return $where;

		}

		/**
		 * Get a list of id of customers by last purchase who need to receive the coupon
		 *
		 * @since   1.0.0
		 *
		 * @param   $vendor_id
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_customers_id_by_last_purchase( $vendor_id = '' ) {

			$days     = get_option( 'ywces_days_last_purchase' . ( apply_filters( 'ywces_set_vendor_id', '', $vendor_id ) ) );
			$date     = date( 'Y-m-d', strtotime( '-' . $days . ' days' ) );
			$statuses = apply_filters( 'ywces_days_last_purchase_statuses', array( 'wc-completed' ) );

			$before_ids = array();
			$args       = array(
				'post_type'      => 'shop_order',
				'post_status'    => $statuses,
				'post_parent'    => 0,
				'posts_per_page' => - 1,
				'date_query'     => array(
					array(
						'before' => $date
					)
				)
			);
			$query      = new WP_Query( $args );

			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					$query->the_post();

					if ( in_array( $query->post->post_status, $statuses ) ) {

						$before_ids[] = yit_get_prop( $query->post, '_customer_user' );

					}

				}

			}

			wp_reset_query();
			wp_reset_postdata();
			$before_ids = array_unique( $before_ids );

			$after_ids = array();
			$args      = array(
				'post_type'      => 'shop_order',
				'post_status'    => 'any',
				'post_parent'    => 0,
				'posts_per_page' => - 1,
				'date_query'     => array(
					array(
						'after' => $date
					)
				)
			);
			$query     = new WP_Query( $args );

			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					$query->the_post();

					if ( in_array( $query->post->post_status, $statuses ) ) {

						$after_ids[] = yit_get_prop( $query->post, '_customer_user' );

					}

				}

			}

			wp_reset_query();
			wp_reset_postdata();

			$after_ids     = array_unique( $after_ids );
			$filtered_ids  = array_diff( $before_ids, $after_ids );
			$customers_ids = array();

			if ( ! empty( $filtered_ids ) ) {

				$user_args = array(
					'include'    => $filtered_ids,
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'key'     => '_last_purchase_coupon_sent' . ( apply_filters( 'ywces_set_vendor_id', '', $vendor_id, true ) ),
							'value'   => 'no',
							'compare' => '='
						),
						array(
							'key'     => '_last_purchase_coupon_sent' . ( apply_filters( 'ywces_set_vendor_id', '', $vendor_id, true ) ),
							'compare' => 'NOT EXISTS'
						)
					)
				);

				$user_query = new WP_User_Query( $user_args );

				if ( ! empty( $user_query->results ) ) {

					foreach ( $user_query->results as $user ) {

						$customers_ids[] = $user->ID;

					}

				}

			}

			return $customers_ids;

		}

		/**
		 * Trash expired coupons
		 *
		 * @since   1.0.0
		 *
		 * @param   $return
		 *
		 * @return  mixed
		 * @author  Alberto Ruggiero
		 */
		public function trash_expired_coupons( $return = false ) {

			$args = array(
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'   => 'generated_by',
						'value' => 'ywces',
					),
					array(
						'relation' => 'OR',
						array(
							'key'     => 'expiry_date',
							'value'   => date( 'Y-m-d', strtotime( "today" ) ),
							'compare' => '<',
							'type'    => 'DATE'
						),
						array(
							'key'     => 'usage_count',
							'value'   => 1,
							'compare' => '>='
						)
					)
				)
			);

			$query = new WP_Query( $args );
			$count = $query->post_count;

			if ( $query->have_posts() ) {

				while ( $query->have_posts() ) {

					$query->the_post();

					wp_trash_post( $query->post->ID );

				}

			}

			wp_reset_query();
			wp_reset_postdata();

			if ( $return ) {

				return $count;

			}

			return null;

		}

		/**
		 * Add customer birthday field
		 *
		 * @since   1.1.3
		 *
		 * @param   $user
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function add_birthday_field_admin( $user ) {

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				return;
			}

			$date_format = get_option( 'ywces_date_format' );
			$birth_date  = '';

			if ( ! empty ( $user ) && get_user_meta( $user->ID, 'ywces_birthday', true ) ) {

				$date       = DateTime::createFromFormat( 'Y-m-d', esc_attr( $user->ywces_birthday ) );
				$birth_date = $date->format( $this->_date_formats[ $date_format ] );

			}
			?>
            <h3><?php _e( 'Coupon Email System', 'yith-woocommerce-coupon-email-system' ); ?></h3>
            <table class="form-table">

                <tr>
                    <th><label for="ywces_birthday"><?php _e( 'Birth date', 'yith-woocommerce-coupon-email-system' ); ?></label></th>
                    <td>
                        <input
                            type="text"
                            class="ywces_date"
                            name="ywces_birthday"
                            id="ywces_birthday"
                            value="<?php echo esc_attr( $birth_date ); ?>"
                            placeholder="<?php echo $this->_date_placeholders[ $date_format ]; ?>"
                            maxlength="10"
                            pattern="<?php echo $this->_date_patterns[ $date_format ] ?>"
                        />

                    </td>
                </tr>

            </table>

			<?php

		}

		/**
		 * Save customer birth date from admin page
		 *
		 * @since   1.0.0
		 *
		 * @param   $customer_id
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function save_birthday_field_admin( $customer_id ) {

			if ( isset( $_POST['ywces_birthday'] ) && $_POST['ywces_birthday'] != '' ) {

				$date_format = get_option( 'ywces_date_format' );

				if ( preg_match( "/{$this->_date_patterns[$date_format]}/", $_POST['ywces_birthday'] ) ) {
					$this->save_birthdate( $customer_id );
				}

			}

		}

		/**
		 * FRONTEND FUNCTIONS
		 */

		/**
		 * Get Date placeholders
		 *
		 * @since   1.0.6
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_date_placeholders() {

			return apply_filters( 'ywces_date_placeholders', array(
				'yy-mm-dd' => __( 'YYYY-MM-DD', 'yith-woocommerce-coupon-email-system' ),
				'yy/mm/dd' => __( 'YYYY/MM/DD', 'yith-woocommerce-coupon-email-system' ),
				'mm-dd-yy' => __( 'MM-DD-YYYY', 'yith-woocommerce-coupon-email-system' ),
				'mm/dd/yy' => __( 'MM/DD/YYYY', 'yith-woocommerce-coupon-email-system' ),
				'dd-mm-yy' => __( 'DD-MM-YYYY', 'yith-woocommerce-coupon-email-system' ),
				'dd/mm/yy' => __( 'DD/MM/YYYY', 'yith-woocommerce-coupon-email-system' ),
			) );

		}

		/**
		 * Get Date formats
		 *
		 * @since   1.0.6
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_date_formats() {

			return apply_filters( 'ywces_date_formats', array(
				'yy-mm-dd' => 'Y-m-d',
				'yy/mm/dd' => 'Y/m/d',
				'mm-dd-yy' => 'm-d-Y',
				'mm/dd/yy' => 'm/d/Y',
				'dd-mm-yy' => 'd-m-Y',
				'dd/mm/yy' => 'd/m/Y',
			) );

		}

		/**
		 * Get Date patterns
		 *
		 * @since   1.0.6
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function get_date_patterns() {

			return apply_filters( 'ywces_date_patterns', array(
				'yy-mm-dd' => '([0-9]{4})-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])',
				'yy/mm/dd' => '([0-9]{4})\/(0[1-9]|1[012])\/(0[1-9]|1[0-9]|2[0-9]|3[01])',
				'mm-dd-yy' => '(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])-([0-9]{4})',
				'mm/dd/yy' => '(0[1-9]|1[012])\/(0[1-9]|1[0-9]|2[0-9]|3[01])\/([0-9]{4})',
				'dd-mm-yy' => '(0[1-9]|1[0-9]|2[0-9]|3[01])-(0[1-9]|1[012])-([0-9]{4})',
				'dd/mm/yy' => '(0[1-9]|1[0-9]|2[0-9]|3[01])\/(0[1-9]|1[012])\/([0-9]{4})',
			) );

		}

		/**
		 * Add customer birth date field to checkout process
		 *
		 * @since   1.0.0
		 *
		 * @param   $fields
		 *
		 * @return  array
		 * @author  Alberto Ruggiero
		 */
		public function add_birthday_field_checkout( $fields ) {

			$date_format = get_option( 'ywces_date_format' );

			if ( is_user_logged_in() ) {

				$user = get_user_by( 'id', get_current_user_id() );

				if ( $user->ywces_birthday ) {
					$section = '';

				} else {

					$section = 'billing';

				}


			} else {

				$section = 'account';

			}

			if ( $section != '' ) {

				$fields[ $section ]['ywces_birthday'] = array(
					'label'             => apply_filters( 'ywces_birthday_label', __( 'Birth date', 'yith-woocommerce-coupon-email-system' ), $this ),
					'custom_attributes' => array(
						'pattern'   => $this->_date_patterns[ $date_format ],
						'maxlength' => 10,

					),
					'placeholder'       => $this->_date_placeholders[ $date_format ],
					'input_class'       => array( 'ywces-birthday' ),
					'class'             => array( 'form-row-wide' )
				);

				if ( apply_filters( 'ywces_required_birthday', '' ) == 'required' ) {

					$fields[ $section ]['ywces_birthday']['label']                         .= ' <abbr class="required" title="required">*</abbr>';
					$fields[ $section ]['ywces_birthday']['custom_attributes']['required'] = 'required';

				}

			}

			return $fields;

		}

		/**
		 * Add customer birth date field to edit account page
		 *
		 * @since   1.0.0
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function add_birthday_field() {

			$user        = get_user_by( 'id', get_current_user_id() );
			$date_format = get_option( 'ywces_date_format' );
			$birth_date  = '';

			if ( ! empty ( $user ) && $user->ywces_birthday ) {

				$date       = DateTime::createFromFormat( 'Y-m-d', esc_attr( $user->ywces_birthday ) );
				$birth_date = $date->format( $this->_date_formats[ $date_format ] );

			}

			$enabled = ( $birth_date == '' ) ? '' : 'disabled';

			?>

            <p class="form-row form-row-wide">
                <label for="ywces_birthday">
					<?php echo apply_filters( 'ywces_birthday_label', __( 'Birth date', 'yith-woocommerce-coupon-email-system' ), $this ); ?><?php echo ( apply_filters( 'ywces_required_birthday', '' ) == 'required' ) ? ' <abbr class="required" title="required">*</abbr>' : '' ?>
                </label>
                <input
                    type="text"
                    class="input-text"
                    name="ywces_birthday"
                    maxlength="10"
                    placeholder="<?php echo $this->_date_placeholders[ $date_format ]; ?>"
                    pattern="<?php echo $this->_date_patterns[ $date_format ] ?>"
                    value="<?php echo $birth_date; ?>"
					<?php echo apply_filters( 'ywces_required_birthday', '' ); ?>
					<?php echo $enabled; ?>
                />

            </p>

			<?php

		}

		/**
		 * Save customer birth date from edit account page
		 *
		 * @since   1.0.0
		 *
		 * @param   $customer_id
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function save_birthday_field( $customer_id ) {

			$this->save_birthdate( $customer_id );

		}

		/**
		 * Save customer birth date
		 *
		 * @since   1.0.0
		 *
		 * @param   $customer_id
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		public function save_birthdate( $customer_id ) {

			if ( isset( $_POST['ywces_birthday'] ) && $_POST['ywces_birthday'] != '' ) {

				$date_format = get_option( 'ywces_date_format' );

				if ( preg_match( "/{$this->_date_patterns[$date_format]}/", $_POST['ywces_birthday'] ) ) {

					$date       = DateTime::createFromFormat( $this->_date_formats[ $date_format ], sanitize_text_field( $_POST['ywces_birthday'] ) );
					$birth_date = $date->format( 'Y-m-d' );

					update_user_meta( $customer_id, 'ywces_birthday', $birth_date );

				}

			}

		}

		/**
		 * Trigger coupon on user registration
		 *
		 * @since   1.0.0
		 *
		 * @param    $customer_id
		 *
		 * @return  void
		 * @author  Alberto Ruggiero
		 */
		/*public function ywces_user_registration( $customer_id ) {
			error_log( 'here' );

			$coupon_code = get_option( 'ywces_coupon_register' );

			if ( get_option( 'ywces_enable_register' ) == 'yes' && count( $this->_available_coupons ) > 0 && YITH_WCES()->check_if_coupon_exists( $coupon_code ) ) {

				$user = get_user_by( 'id', $customer_id );

				$this->bind_coupon( $coupon_code, $user->user_email );

				$email_result = YWCES_Emails()->prepare_coupon_mail( $customer_id, 'register', $coupon_code );

			}

		}*/

		/**
		 * YITH FRAMEWORK
		 */

		/**
		 * Register plugins for activation tab
		 *
		 * @return  void
		 * @since   2.0.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once 'plugin-fw/licence/lib/yit-licence.php';
				require_once 'plugin-fw/licence/lib/yit-plugin-licence.php';
			}
			YIT_Plugin_Licence()->register( YWCES_INIT, YWCES_SECRET_KEY, YWCES_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return  void
		 * @since   2.0.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once( 'plugin-fw/lib/yit-upgrade.php' );
			}
			YIT_Upgrade()->register( YWCES_SLUG, YWCES_INIT );
		}

		/**
		 * Plugin row meta
		 *
		 * add the action links to plugin admin page
		 *
		 * @since   1.0.0
		 *
		 * @param   $new_row_meta_args
		 * @param   $plugin_meta
		 * @param   $plugin_file
		 * @param   $plugin_data
		 * @param   $status
		 * @param   $init_file
		 *
		 * @return  array
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     plugin_row_meta
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YWCES_INIT' ) {
			$new_row_meta_args = parent::plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file );

			if ( defined( $init_file ) && constant( $init_file ) == $plugin_file ){
				$new_row_meta_args['is_premium'] = true;
			}

			return $new_row_meta_args;
		}

		/**
		 * Action Links
		 *
		 * add the action links to plugin admin page
		 * @since   1.0.0
		 *
		 * @param   $links | links plugin array
		 *
		 * @return  mixed
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 * @use     plugin_action_links_{$plugin_file_name}
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->_panel_page, true );
			return $links;
		}

	}

}

if ( ! function_exists( 'save_birthday_field_checkout' ) ) {

	/**
	 * Save customer birth date from checkout process
	 *
	 * @since   1.0.0
	 *
	 * @param   $customer_id
	 * @param   $posted
	 *
	 * @return  void
	 * @author  Alberto Ruggiero
	 */
	function save_birthday_field_checkout( $customer_id, $posted ) {

		YITH_WCES()->save_birthdate( $customer_id );

	}

	add_action( 'woocommerce_checkout_update_user_meta', 'save_birthday_field_checkout', 10, 2 );

}

if ( ! function_exists( 'ywces_user_registration' ) ) {

	/**
	 * Trigger coupon on user registration
	 *
	 * @since   1.0.0
	 *
	 * @param    $customer_id
	 *
	 * @return  void
	 * @author  Alberto Ruggiero
	 */
	function ywces_user_registration( $customer_id ) {

		$coupon_code = get_option( 'ywces_coupon_register' );

		if ( get_option( 'ywces_enable_register' ) == 'yes' && YITH_WCES()->check_if_coupon_exists( $coupon_code ) ) {

			$user = get_user_by( 'id', $customer_id );

			YITH_WCES()->bind_coupon( $coupon_code, $user->user_email );

			$email_result = YWCES_Emails()->prepare_coupon_mail( $customer_id, 'register', $coupon_code );

		}

	}

	add_action( 'woocommerce_created_customer', 'ywces_user_registration', 10, 1 );

}


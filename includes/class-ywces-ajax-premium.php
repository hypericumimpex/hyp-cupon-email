<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( 'YWCES_Ajax_Premium' ) ) {

    /**
     * Implements AJAX for YWCES plugin
     *
     * @class   YWCES_Ajax_Premium
     * @package Yithemes
     * @since   1.0.0
     * @author  Your Inspiration Themes
     *
     */
    class YWCES_Ajax_Premium {

        /**
         * Single instance of the class
         *
         * @var \YWCES_Ajax_Premium
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YWCES_Ajax_Premium
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

            add_action( 'wp_ajax_ywces_clear_expired_coupons', array( $this, 'clear_expired_coupons' ) );

        }

        /**
         * Clear expired coupons manually
         *
         * @since   1.0.0
         * @return  void
         * @author  Alberto Ruggiero
         */
        public function clear_expired_coupons() {

            $result = array(
                'success' => true,
                'message' => ''
            );

            try {

                $count = YITH_WCES()->trash_expired_coupons( true );

                $result['message'] = sprintf( _n( 'Operation completed. %d coupon trashed.', 'Operation completed. %d coupons trashed.', $count, 'yith-woocommerce-coupon-email-system' ), $count );

            } catch ( Exception $e ) {

                $result['success'] = false;
                $result['message'] = sprintf( __( 'An error occurred: %s', 'yith-woocommerce-coupon-email-system' ), $e->getMessage() );

            }

            wp_send_json( $result );

        }


    }

    /**
     * Unique access to instance of YWCES_Ajax_Premium class
     *
     * @return \YWCES_Ajax_Premium
     */
    function YWCES_Ajax_Premium() {

        return YWCES_Ajax_Premium::get_instance();

    }

    new YWCES_Ajax_Premium();

}
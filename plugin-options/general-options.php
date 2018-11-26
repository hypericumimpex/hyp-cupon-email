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

$query_args              = array(
	'page' => isset( $_GET['page'] ) ? $_GET['page'] : '',
	'tab'  => 'howto',
);
$howto_url               = esc_url( add_query_arg( $query_args, admin_url( 'admin.php' ) ) );
$placeholders_text       = __( 'Allowed placeholders:', 'yith-woocommerce-coupon-email-system' );
$ph_reference_link       = ' - <a href="' . $howto_url . '" target="_blank">' . __( 'More info', 'yith-woocommerce-coupon-email-system' ) . '</a>';
$ph_site_title           = ' <b>{site_title}</b>';
$ph_customer_name        = ' <b>{customer_name}</b>';
$ph_customer_last_name   = ' <b>{customer_last_name}</b >';
$ph_customer_email       = ' <b>{customer_email}</b>';
$ph_coupon_description   = ' <b>{coupon_description}</b>';
$ph_order_date           = ' <b>{order_date}</b>';
$ph_purchases_threshold  = ' <b>{purchases_threshold}</b>';
$ph_spending_threshold   = ' <b>{spending_threshold}</b>';
$ph_customer_money_spent = ' <b>{customer_money_spent}</b>';
$ph_purchased_product    = ' <b>{purchased_product}</b>';
$ph_days_ago             = ' <b>{days_ago}</b>';
$ph_unsubscribe_link     = ' <b>{unsubscribe_link}</b>';


$coupons = array_merge( array( '' => __( 'Select a coupon', 'yith-woocommerce-coupon-email-system' ) ), YITH_WCES()->_available_coupons );

$disabled = ( count( YITH_WCES()->_available_coupons ) > 0 ? array() : array( 'disabled' => 'disabled' ) );

$email_templates_enable = ! YITH_WCES()->is_email_templates_active() ? '' : array(
	'name'    => __( 'Use YITH WooCommerce Email Templates', 'yith-woocommerce-coupon-email-system' ),
	'type'      => 'yith-field',
	'yith-type' => 'onoff',
	'id'      => 'ywces_mail_template_enable',
	'default' => 'no',
);

return array(

	'general' => array(
		'ywces_main_section_title'   => array(
			'name' => __( 'Coupon Email System settings', 'yith-woocommerce-coupon-email-system' ),
			'type' => 'title',
		),
		'ywces_enable_plugin'        => array(
			'name'      => __( 'Enable YITH WooCommerce Coupon Email System', 'yith-woocommerce-coupon-email-system' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywces_enable_plugin',
			'default'   => 'yes',
		),
		'ywces_refuse_coupon'        => array(
			'name'      => __( 'Allow customers to accept or refuse to receive coupons', 'yith-woocommerce-coupon-email-system' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywces_refuse_coupon',
			'default'   => 'no',
		),
		'ywces_date_format'          => array(
			'name'    => __( 'Birthday Input Date Format', 'yith-woocommerce-coupon-email-system' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',			'options' => YITH_WCES()->_date_placeholders,
			'default' => 'yy-mm-dd',
			'id'      => 'ywces_date_format',
		),
		'ywces_mail_type'            => array(
			'name'    => __( 'Email type', 'yith-woocommerce-coupon-email-system' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',				'desc'    => __( 'Choose which email format to send.', 'yith-woocommerce-coupon-email-system' ),
			'options' => array(
				'html'  => __( 'HTML', 'yith-woocommerce-coupon-email-system' ),
				'plain' => __( 'Plain text', 'yith-woocommerce-coupon-email-system' )
			),
			'default' => 'html',
			'id'      => 'ywces_mail_type'
		),
		'ywces_mail_template_enable' => $email_templates_enable,
		'ywces_mail_template'        => array(
			'name'    => __( 'Email template', 'yith-woocommerce-coupon-email-system' ),
			'type'    => 'ywces-mailskin',
			'desc'    => __( 'Choose which email template to send. Remember to save options before sending the test email.', 'yith-woocommerce-coupon-email-system' ),
			'options' => array(
				'base'    => __( 'WooCommerce Template', 'yith-woocommerce-coupon-email-system' ),
				'ywces-1' => __( 'Template 1', 'yith-woocommerce-coupon-email-system' ),
				'ywces-2' => __( 'Template 2', 'yith-woocommerce-coupon-email-system' ),
				'ywces-3' => __( 'Template 3', 'yith-woocommerce-coupon-email-system' )
			),
			'default' => 'base',
			'id'      => 'ywces_mail_template'
		),
		'ywces_coupon_purge'         => array(
			'name'    => __( 'Deletion of Expired Coupons', 'yith-woocommerce-review-for-discounts' ),
			'type'    => 'ywces-coupon-purge',
			'desc'    => __( 'Delete automatically expired coupons (only those created by this plugin)', 'yith-woocommerce-coupon-email-system' ),
			'default' => 'no',
			'id'      => 'ywces_coupon_purge'
		),
		'ywces_main_section_end'     => array(
			'type' => 'sectionend',
		),

		'ywces_section_title_register' => array(
			'name' => __( 'On user registration', 'yith-woocommerce-coupon-email-system' ),
			'type' => 'title',
		),
		'ywces_collapse_register'      => array(
			'type' => 'ywces-collapse'
		),
		'ywces_enable_register'        => array(
			'name'              => __( 'Enable coupon sending', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'id'                => 'ywces_enable_register',
			'default'           => 'no',
			'custom_attributes' => $disabled
		),
		'ywces_coupon_register'        => array(
			'name'    => __( 'Selected Coupon', 'yith-woocommerce-coupon-email-system' ),
			'type'      => 'yith-field',
			'yith-type' => 'select',
			'desc'    => __( 'Choose the coupon to send', 'yith-woocommerce-coupon-email-system' ),
			'options' => $coupons,
			'default' => '',
			'id'      => 'ywces_coupon_register',
		),
		'ywces_subject_register'       => array(
			'name'              => __( 'Email subject', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'text',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_reference_link,
			'id'                => 'ywces_subject_register',
			'default'           => __( 'You have received a welcome coupon from {site_title}', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'ywces-text',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_mailbody_register'      => array(
			'name'              => __( 'Email content', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-wc-textarea',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_customer_email . $ph_coupon_description . $ph_unsubscribe_link . $ph_reference_link,
			'id'                => 'ywces_mailbody_register',
			'default'           => __( 'Hi {customer_name},
thanks for your the registration on {site_title}!
We would like to offer you this coupon as a welcome gift:

{coupon_description}

See you on our shop,

{site_title}.', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'yith-wc-textarea',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_test_register'          => array(
			'name'              => __( 'Test email', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'ywces-send',
			'field_id'          => 'ywces_test_register',
			'custom_attributes' => $disabled
		),
		'ywces_section_end_register'   => array(
			'type' => 'sectionend',
		),

		'ywces_section_title_first_purchase' => array(
			'name' => __( 'On first purchase', 'yith-woocommerce-coupon-email-system' ),
			'type' => 'title',
		),
		'ywces_collapse_first_purchase'      => array(
			'type' => 'ywces-collapse'
		),
		'ywces_enable_first_purchase'        => array(
			'name'              => __( 'Enable coupon sending', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'id'                => 'ywces_enable_first_purchase',
			'default'           => 'no',
			'custom_attributes' => $disabled
		),
		'ywces_coupon_first_purchase'        => array(
			'name'    => __( 'Selected Coupon', 'yith-woocommerce-coupon-email-system' ),
			'type'    => 'select',
			'desc'    => __( 'Choose the coupon to send', 'yith-woocommerce-coupon-email-system' ),
			'options' => $coupons,
			'default' => '',
			'id'      => 'ywces_coupon_first_purchase',
		),
		'ywces_subject_first_purchase'       => array(
			'name'              => __( 'Email subject', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'text',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_reference_link,
			'id'                => 'ywces_subject_first_purchase',
			'default'           => __( 'You have received a coupon from {site_title}', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'ywces-text',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_mailbody_first_purchase'      => array(
			'name'              => __( 'Email content', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-wc-textarea',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_customer_email . $ph_order_date . $ph_coupon_description . $ph_unsubscribe_link . $ph_reference_link,
			'id'                => 'ywces_mailbody_first_purchase',
			'default'           => __( 'Hi {customer_name},
thanks for making the first purchase on {order_date} on our shop {site_title}!
Because of this, we would like to offer you this coupon as a little gift:

{coupon_description}

See you on our shop,

{site_title}.', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'yith-wc-textarea',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_test_first_purchase'          => array(
			'name'              => __( 'Test email', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'ywces-send',
			'field_id'          => 'ywces_test_first_purchase',
			'custom_attributes' => $disabled
		),
		'ywces_section_end_first_purchase'   => array(
			'type' => 'sectionend',
		),

		'ywces_section_title_purchases' => array(
			'name' => __( 'On specific order threshold', 'yith-woocommerce-coupon-email-system' ),
			'type' => 'title',
		),
		'ywces_collapse_purchases'      => array(
			'type' => 'ywces-collapse'
		),
		'ywces_enable_purchases'        => array(
			'name'              => __( 'Enable coupon sending', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'id'                => 'ywces_enable_purchases',
			'default'           => 'no',
			'custom_attributes' => $disabled
		),
		'ywces_thresholds_purchases'    => array(
			'name'    => __( 'Order thresholds', 'yith-woocommerce-coupon-email-system' ),
			'id'      => 'ywces_thresholds_purchases',
			'options' => $coupons,
			'default' => '',
			'type'    => 'ywces-table'
		),
		'ywces_subject_purchases'       => array(
			'name'              => __( 'Email subject', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'text',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_reference_link,
			'id'                => 'ywces_subject_purchases',
			'default'           => __( 'You have received a coupon from {site_title}', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'ywces-text',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_mailbody_purchases'      => array(
			'name'              => __( 'Email content', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-wc-textarea',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_customer_email . $ph_order_date . $ph_purchases_threshold . $ph_coupon_description . $ph_unsubscribe_link . $ph_reference_link,
			'id'                => 'ywces_mailbody_purchases',
			'default'           => __( 'Hi {customer_name},
with the order made on {order_date}, you have reached {purchases_threshold} orders!
Because of this, we would like to offer you this coupon as a gift:

{coupon_description}

See you on our shop,

{site_title}.', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'yith-wc-textarea',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_test_purchases'          => array(
			'name'              => __( 'Test email', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'ywces-send',
			'field_id'          => 'ywces_test_purchases',
			'custom_attributes' => $disabled
		),
		'ywces_section_end_purchases'   => array(
			'type' => 'sectionend',
		),

		'ywces_section_title_spending' => array(
			'name' => __( 'On specific spent threshold', 'yith-woocommerce-coupon-email-system' ),
			'type' => 'title',
		),
		'ywces_collapse_spending'      => array(
			'type' => 'ywces-collapse'
		),
		'ywces_enable_spending'        => array(
			'name'              => __( 'Enable coupon sending', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-field',
			'yith-type'         => 'onoff',
			'id'                => 'ywces_enable_spending',
			'default'           => 'no',
			'custom_attributes' => $disabled
		),
		'ywces_thresholds_spending'    => array(
			'name'    => __( 'Amount thresholds', 'yith-woocommerce-coupon-email-system' ),
			'id'      => 'ywces_thresholds_spending',
			'options' => $coupons,
			'default' => '',
			'type'    => 'ywces-table'
		),
		'ywces_subject_spending'       => array(
			'name'              => __( 'Email subject', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'text',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_reference_link,
			'id'                => 'ywces_subject_spending',
			'default'           => __( 'You have received a coupon from {site_title}', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'ywces-text',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_mailbody_spending'      => array(
			'name'              => __( 'Email content', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-wc-textarea',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_customer_email . $ph_order_date . $ph_spending_threshold . $ph_customer_money_spent . $ph_coupon_description . $ph_unsubscribe_link . $ph_reference_link,
			'id'                => 'ywces_mailbody_spending',
			'default'           => __( 'Hi {customer_name},
with the order made on {order_date}, you have reached the amount of {spending_threshold} for a total purchase amount of {customer_money_spent}!
Because of this, we would like to offer you this coupon as a gift:

{coupon_description}

See you on our shop,

{site_title}.', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'yith-wc-textarea',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_test_spending'          => array(
			'name'              => __( 'Test email', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'ywces-send',
			'field_id'          => 'ywces_test_spending',
			'custom_attributes' => $disabled
		),
		'ywces_section_end_spending'   => array(
			'type' => 'sectionend',
		),

		'ywces_section_title_product_purchasing' => array(
			'name' => __( 'On specific product purchase', 'yith-woocommerce-coupon-email-system' ),
			'type' => 'title',
		),
		'ywces_collapse_product_purchasing'      => array(
			'type' => 'ywces-collapse'
		),
		'ywces_enable_product_purchasing'        => array(
			'name'      => __( 'Enable coupon sending', 'yith-woocommerce-coupon-email-system' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywces_enable_product_purchasing',
			'default'   => 'no',
		),
		'ywces_targets_product_purchasing'       => array(
			'title'       => __( 'Target products', 'yith-woocommerce-coupon-email-system' ),
			'id'          => 'ywces_targets_product_purchasing',
			'type'        => 'yith-wc-product-select',
			'class'       => 'wc-product-search',
			'multiple'    => true,
			'default'     => '',
			'variations'  => 'true',
			'placeholder' => __( 'Search for a product&hellip;', 'yith-woocommerce-coupon-email-system' ),
			'desc'        => __( 'Products that will cause the sending of the coupon', 'yith-woocommerce-coupon-email-system' )
		),
		'ywces_coupon_product_purchasing'        => array(
			'name'    => __( 'Coupon settings', 'yith-woocommerce-coupon-email-system' ),
			'id'      => 'ywces_coupon_product_purchasing',
			'default' => array(
				'discount_type'      => 'fixed_cart',
				'coupon_amount'      => '',
				'expiry_days'        => '',
				'minimum_amount'     => '',
				'maximum_amount'     => '',
				'free_shipping'      => '',
				'individual_use'     => '',
				'exclude_sale_items' => '',
			),
			'type'    => 'ywces-coupon'
		),
		'ywces_subject_product_purchasing'       => array(
			'name'              => __( 'Email subject', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'text',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_reference_link,
			'id'                => 'ywces_subject_product_purchasing',
			'default'           => __( 'You have received a coupon from {site_title}', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'ywces-text',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_mailbody_product_purchasing'      => array(
			'name'              => __( 'Email content', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-wc-textarea',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_customer_email . $ph_order_date . $ph_purchased_product . $ph_coupon_description . $ph_unsubscribe_link . $ph_reference_link,
			'id'                => 'ywces_mailbody_product_purchasing',
			'default'           => __( 'Hi {customer_name},
thanks for purchasing the following product with the order made on {order_date}: {purchased_product}.
We would like to offer you this coupon as a gift:

{coupon_description}

See you on our shop,

{site_title}.', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'yith-wc-textarea',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_test_product_purchasing'          => array(
			'name'     => __( 'Test email', 'yith-woocommerce-coupon-email-system' ),
			'type'     => 'ywces-send',
			'field_id' => 'ywces_test_product_purchasing',
		),
		'ywces_section_end_product_purchasing'   => array(
			'type' => 'sectionend',
		),

		'ywces_section_title_birthday' => array(
			'name' => __( 'On customer birthday', 'yith-woocommerce-coupon-email-system' ),
			'type' => 'title',
		),
		'ywces_collapse_birthday'      => array(
			'type' => 'ywces-collapse'
		),
		'ywces_enable_birthday'        => array(
			'name'      => __( 'Enable coupon sending', 'yith-woocommerce-coupon-email-system' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywces_enable_birthday',
			'default'   => 'no',
		),
		'ywces_coupon_birthday'        => array(
			'name'    => __( 'Coupon settings', 'yith-woocommerce-coupon-email-system' ),
			'id'      => 'ywces_coupon_birthday',
			'default' => array(
				'discount_type'      => 'fixed_cart',
				'coupon_amount'      => '',
				'expiry_days'        => '',
				'minimum_amount'     => '',
				'maximum_amount'     => '',
				'free_shipping'      => '',
				'individual_use'     => '',
				'exclude_sale_items' => '',
			),
			'type'    => 'ywces-coupon'
		),
		'ywces_subject_birthday'       => array(
			'name'              => __( 'Email subject', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'text',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_reference_link,
			'id'                => 'ywces_subject_birthday',
			'default'           => __( 'Happy birthday from {site_title}', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'ywces-text',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_mailbody_birthday'      => array(
			'name'              => __( 'Email content', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-wc-textarea',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_customer_email . $ph_coupon_description . $ph_unsubscribe_link . $ph_reference_link,
			'id'                => 'ywces_mailbody_birthday',
			'default'           => __( 'Hi {customer_name},
we would like to make you our best wishes for a happy birthday!
Please, accept our coupon as a small gift for you:

{coupon_description}

See you on our shop,

{site_title}.', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'yith-wc-textarea',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_test_birthday'          => array(
			'name'     => __( 'Test email', 'yith-woocommerce-coupon-email-system' ),
			'type'     => 'ywces-send',
			'field_id' => 'ywces_test_birthday',
		),
		'ywces_section_end_birthday'   => array(
			'type' => 'sectionend',
		),

		'ywces_section_title_last_purchase' => array(
			'name' => __( 'On a specific number of days from the last purchase', 'yith-woocommerce-coupon-email-system' ),
			'type' => 'title',
		),
		'ywces_collapse_last_purchase'      => array(
			'type' => 'ywces-collapse'
		),
		'ywces_enable_last_purchase'        => array(
			'name'      => __( 'Enable coupon sending', 'yith-woocommerce-coupon-email-system' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'id'        => 'ywces_enable_last_purchase',
			'default'   => 'no',
		),
		'ywces_days_last_purchase'          => array(
			'name'              => __( 'Days to elapse', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'number',
			'desc'              => __( 'The number of days that have to pass after the last order has been set as "completed"', 'yith-woocommerce-coupon-email-system' ),
			'default'           => 20,
			'id'                => 'ywces_days_last_purchase',
			'custom_attributes' => array(
				'min'      => 1,
				'required' => 'required'
			)
		),
		'ywces_coupon_last_purchase'        => array(
			'name'    => __( 'Coupon settings', 'yith-woocommerce-coupon-email-system' ),
			'id'      => 'ywces_coupon_last_purchase',
			'default' => array(
				'discount_type'      => 'fixed_cart',
				'coupon_amount'      => '',
				'expiry_days'        => '',
				'minimum_amount'     => '',
				'maximum_amount'     => '',
				'free_shipping'      => '',
				'individual_use'     => '',
				'exclude_sale_items' => '',
			),
			'type'    => 'ywces-coupon'
		),
		'ywces_subject_last_purchase'       => array(
			'name'              => __( 'Email subject', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'text',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_reference_link,
			'id'                => 'ywces_subject_last_purchase',
			'default'           => __( 'You have received a coupon from {site_title}', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'ywces-text',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_mailbody_last_purchase'      => array(
			'name'              => __( 'Email content', 'yith-woocommerce-coupon-email-system' ),
			'type'              => 'yith-wc-textarea',
			'desc'              => $placeholders_text . $ph_site_title . $ph_customer_name . $ph_customer_last_name . $ph_customer_email . $ph_days_ago . $ph_coupon_description . $ph_unsubscribe_link . $ph_reference_link,
			'id'                => 'ywces_mailbody_last_purchase',
			'default'           => __( 'Hi {customer_name},
{days_ago} days have passed since your last order.
We would like to encourage you to purchase something more with this coupon:

{coupon_description}

See you on our shop,

{site_title}.', 'yith-woocommerce-coupon-email-system' ),
			'class'             => 'yith-wc-textarea',
			'custom_attributes' => array(
				'required' => 'required'
			)
		),
		'ywces_test_last_purchase'          => array(
			'name'     => __( 'Test email', 'yith-woocommerce-coupon-email-system' ),
			'type'     => 'ywces-send',
			'field_id' => 'ywces_test_last_purchase',
		),
		'ywces_section_end_last_purchase'   => array(
			'type' => 'sectionend',
		),

	)

);
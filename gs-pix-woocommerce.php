<?php defined( 'ABSPATH' ) || exit;
/**
 * Plugin Name:          GSPix - Forma de Pagamento Pix
 * Description:          Habilita modo de pagameto via QRCODE e codigo Pix em seu E-Commerce
 * Author:               Giovane Sedano
 * Author URI:           https://github.com/giovaness30
 * Version:              1.0.2
 * License:              GPLv3 or later
 * Text Domain: 		 gs-pix-woocommerce
 *
 * Essystem - Plugin desenvolvido para utilizar emissao de boletos via API Getnet nos E-commerce para os clientes da empresa.
 */

require __DIR__.'/vendor/autoload.php';

if ( ! class_exists( 'gs_pix' ) ) {
	require_once __DIR__ . '/class.gs-pix-woocommerce.php';
	add_action( 'plugins_loaded', 'init_gs_pix_class' );

	//ADICIONA LINK ATALHO CONFIGURAÇÕES DO PLUGIN.
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'gs_pix_add_action_links' );
	function gs_pix_add_action_links ( $links ) {
	$mylinks = array(
	'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=gs-pix' ) . '">Configurações</a>',
	);
	return array_merge( $links, $mylinks );
	}

	add_action( 'woocommerce_email_order_details','gs_pix_add_order_email_instructions', 10, 4 );
	function gs_pix_add_order_email_instructions( $order, $sent_to_admin, $plain_text, $email ) {
		if ( $order->status == 'Pending payment' && $order->get_payment_method() == 'gs-pix') {
			echo '<strong>Pague pelo QrCode Gerado na Loja Virtual e envie-nos o compovante pelo whatsapp.</strong>';
		} 
	}
	
}
<?php defined( 'ABSPATH' ) || exit;

function init_gs_pix_class(){

    class GS_Gateway_pix extends WC_Payment_Gateway {

       public function __construct(){

        $this->id = 'gs-pix';
        $this->has_fields = true;
        $this->method_title = __('Pix');
        $this->method_description = __('Integração de Pix');

        $this->init_form_fields();
        $this->init_settings();

        $this->title 		 	= $this->get_option('title');
        $this->description 	 	= $this->get_option('description');
		$this->chave_pix	 	= $this->get_option('chave_pix');
		$this->merchant_name 	= $this->get_option('Merchant_name');
        $this->merchant_city	= $this->get_option('merchant_city');
        $this->numb_whats	= $this->get_option('numb_whats');

        add_action ('woocommerce_update_options_payment_gateways_'. $this-> id, array ($this, 'process_admin_options'));
        add_action('woocommerce_thankyou_' . $this->id, array( $this, 'order_summary_preview' ) );
        
        
       }

       public function init_form_fields(){

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => __('Habilitar'),
                    'label'       => __('Habilita ou Desabilita a forma de pagamento'),
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => __('Nome que aparece pro cliente no finalizar pedido'),
                    'type'        => 'text',
                    'description' => __('Nome que aparece pro cliente no finalizar pedido'),
                    'default'     => __('Pagamento Via Pix'),
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => __('Descrição'),
                    'type'        => 'textarea',
                    'description' => __('Informação extra mostrada na seleção da forma de pagamento'),
                    'default'     => __('Será gerado um QrCode e um codigo para pagamento.'),
                ),
                'chave_pix' => array(
                    'title'       => __('Chave Pix'),
                    'type'        => 'text',
                    'description' => __('Valido CPF, CNPJ, E-mails, Numero de telefone ( sendo preciso colocar +55dd no inicio. ) ou até mesmo uma chave aleatória pix.'),
                    'class'	      => 'production'		
                ),
                'merchant_name' => array(
                    'title'       => __('Nome do Titular da Chave Pix'),
                    'type'        => 'text',
                    'description' => __('Sem acentos, podendo ser Maiusculo ou Minusculo.'),
                    'class'	      => 'production'		
                ),
                'merchant_city' => array(
                    'title'       => __('Cidade do Titular da Chave Pix'),
                    'type'        => 'text',
                    'description' => __('Sem acentos, podendo ser Maiusculo ou Minusculo.'),
                    'class'	      => 'production'		
                ),
                'numb_whats' => array(
                    'title'       => __('Numero Whatsapp para Receber o Comprovante de pagamento.'),
                    'type'        => 'text',
                    'description' => __('Com ddd. "Ex 1188888888"'),
                    'class'	      => 'production'		
                )
            );
        }

        //CAMPO EMBAIXO DA OPÇÃO DE PAGAMENTO PARA DESCRIÇÕES.
        function payment_fields()
        {
            
            if(!empty($this->description)) {
                echo wpautop(trim(sanitize_text_field($this->description)));
            }
            
        }

        // FUNÇÃO PADRAO DO WOOCOMMERCE PARA PROCESSO DO PAGAMENTO
        public function process_payment( $order_id ) {

            global $woocommerce;
            $order = new WC_Order( $order_id );

            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );

        }
        

        // EXIBE DEPOIS DE GERADO NA PROXIMA PAGINA
        function order_summary_preview( $order_id ) {
		
            $order = wc_get_order( $order_id );

            include 'pix-create.php';
            ?>
            <div class="container">
             <div class="row d-flex justify-content-center">
                <input type="hidden" value="<?php echo $payloadQrCode; ?>" id="copiar">
                <p class="col-12">Codigo Gerado! Efetue o pagamento pelo QR CODE utilizando o aplicativo do seu banco favorito.</p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo $payloadQrCode; ?>">
                <p class="col-12 d-flex justify-content-center" style="font-size: 12px"><?php echo $payloadQrCode; ?></p>
                <div class="col-12"></div><button class="button pix-button-copy-code" style="margin-bottom: 20px;" onclick="copyCode()"><?php echo __('Clique aqui para copiar o Código acima', 'woocommerce-pix'); ?> </button></div>
                <div class="pix-response-output inactive" style="margin: 2em 0.5em 1em;padding: 0.2em 1em;border: 2px solid #46b450;display: none;" aria-hidden="true" style=""><?php echo __('O código foi copiado para a área de transferência.', 'woocommerce-pix'); ?></div>
             </div>
            <br>
            <strong><p>Envie seu Comprovante após o pagamento atraves do <a style="" href="http://api.whatsapp.com/send?phone=+55<?= $this->numb_whats ?>&text=Comprovante Compra Online <?= get_bloginfo('name') ?>, Pedido: <?= (string) $order_id ?> ." target="_blank"><img src="https://img.icons8.com/carbon-copy/30/000000/whatsapp.png"/>Whatsapp</a>.</p></strong>
			<br>
            </div>
			<script>
				function copyCode() {
					var copyText = document.getElementById("copiar");
					copyText.type = "text";
					copyText.select();
					copyText.setSelectionRange(0, 99999)
					document.execCommand("copy");
					copyText.type = "hidden";

					if (jQuery("div.pix-response-output")){
						jQuery("div.pix-response-output").show();
					}else{
						alert('O código foi copiado para a área de transferência.');
					}
					return false;
				}
			</script>
            <?php	
        }
    }
}

// INFORMA AO WOOCOMMERCE QUE EXISTE UMA NOVA FUNÇÃO
function gs_pix( $methods ) {
    $methods[] = 'GS_Gateway_pix'; 
    return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'gs_pix' );
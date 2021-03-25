<?php
require __DIR__.'/vendor/autoload.php';

use \App\Pix\Payload;

//Instancia principal do Payload Pix
$obPayload = (new Payload)->setPixKey($this->chave_pix)
                          ->setDescription('Site '. get_bloginfo('name') .' Cliente '.$order->get_billing_first_name())
                          ->setMerchantName($this->merchant_name)
                          ->setMerchantCity($this->merchant_city)
                          ->setTxid('PED'.(string) $order_id)
                          ->setAmount($order->get_total());

$payloadQrCode = $obPayload->getPayload();


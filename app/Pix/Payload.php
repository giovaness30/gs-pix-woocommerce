<?php
namespace App\Pix;

class Payload{

    /**
    * IDs do Payload do Pix
    * @var string
    */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    /* Chave Pix, OBS: Se for Numero de telefone temos que colocar +55dd */
    private $pixKey;

    /* Descrição do pagamento que aparece para cliente */
    private $description;

    /* Nome do Titular da Conta Pix */
    private $merchantName;

    /* Cidade do Titular da Conta */
    private $merchantCity;

    /* Id da transação pix */
    private $txid;

    /* Valor da transação */
    private $amount;

    /* Método responsavel por definir valor de $pixkey */
    public function setPixKey($pixKey){
        $this->pixKey = $pixKey;
        return $this;
    }

    /* Método responsavel por definir valor de $description */
    public function setDescription($description){
        $this->description = $description;
        return $this;
    }

    /* Método responsavel por definir valor de $merchantName */
    public function setMerchantName($merchantName){
        $this->merchantName = $merchantName;
        return $this;
    }

    /* Método responsavel por definir valor de $merchantCity */
    public function setMerchantCity($merchantCity){
        $this->merchantCity = $merchantCity;
        return $this;
    }

    /* Método responsavel por definir valor de $txid */
    public function setTxid($txid){
        $this->txid = $txid;
        return $this;
    }

    /* Método responsavel por definir valor de $amount */
    public function setAmount($amount){
        $this->amount = (string)number_format($amount,2,'.','');
        return $this;
    }

    /* Responvavel por retornar valor completo */
    private function getValue($id,$value){
        $size = str_pad(strlen($value),2,'0',STR_PAD_LEFT);
        return $id.$size.$value;

    }

    /* Metodo para retornar valor completo da informação conta */
    private function getMerchantAccountInformation(){
        /* Dominio do Banco */
        $gui = $this->getvalue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI,'br.gov.bcb.pix');

        /* Chave Pix */
        $key =$this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY,$this->pixKey);

        /* Descrição do pagamento */
        $description = strlen($this->description) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION,$this->description) : '';

        return $this->getvalue(self::ID_MERCHANT_ACCOUNT_INFORMATION,$gui.$key.$description);
    }

    /* Metoro Responsavel por retornar valor completo do campo adicional do pix TXID */
    private function getAdditionalDataFieldTemplate(){

        $txid = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID,$this->txid);
        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE,$txid);
    }

    /* Metodo Responsavel por gerar o codigo pix FINAL*/
    public function getPayload(){
        // Cria o Payload
        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR,'01').
                   $this-> getMerchantAccountInformation().
                   $this->getValue(self::ID_MERCHANT_CATEGORY_CODE,'0000').
                   $this->getValue(self::ID_TRANSACTION_CURRENCY,'986').
                   $this->getValue(self::ID_TRANSACTION_AMOUNT,$this->amount).
                   $this->getValue(self::ID_COUNTRY_CODE,'BR').
                   $this->getValue(self::ID_MERCHANT_NAME,$this->merchantName).
                   $this->getValue(self::ID_MERCHANT_CITY,$this->merchantCity).
                   $this->getAdditionalDataFieldTemplate();

        /* Aqui retorna todo Payload gerado, mais a HASH CRC16 do calculo total da Chave gerada no Payload */
        // Aqui RETORNA FINAL DE TUDO
        return $payload.$this->getCRC16($payload);
    }

    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    private function getCRC16($payload) {
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16.'04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) $resultado ^= $polinomio;
                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16.'04'.strtoupper(dechex($resultado));
    }

}
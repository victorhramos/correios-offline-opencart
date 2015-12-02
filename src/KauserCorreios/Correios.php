<?php

namespace KauserCorreios;

class Correios
{
    public $fields;

    public $capitais;

    public $interiores;

    public $pesos;

    public function __construct($origem, $empresa = '', $senha = '')
    {
        $this->ws = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?StrRetorno=xml&";

        $fields = array(
            "sCepOrigem"          => $origem,
            "sCepDestino"         => '',
            "nVlPeso"             => 3,
            "nVlComprimento"      => 16,
            "nVlAltura"           => 2,
            "nVlLargura"          => 11,
            "nCdServico"          => "40010,41106",
            "sCdMaoPropria"       => "S",
            "sCdAvisoRecebimento" => "S",
            "nCdEmpresa"          => $empresa,
            "sDsSenha"            => $senha,
            "nVlValorDeclarado"   => "0",
            "nVlDiametro"         => "0",
            "nCdFormato"          => "1",
        );

        $this->fields = $fields;

        $this->setPesos();
        $this->setCeps();
    }

    protected function setPesos()
    {
        $this->pesos = array(0.3);

        for ($i = 1; $i < 31; $i++) {
            array_push($this->pesos, $i);
        }
    }

    protected function setCeps()
    {
        $this->capitais = array(
            array('AC - Capital', "69900000", "69920999", "69906380"),
            array('AL - Capital', "57000000", "57099999", "57046270"),
            array('AM - Capital', "69000000", "69099000", "69020210"),
            array('AP - Capital', "68900000", "68929999", "68901100"),
            array('BA - Capital', "40000000", "43849999", "40110010"),
            array('CE - Capital', "60000000", "61699999", "60165082"),
            array('DF - Capital', "70000000", "73699999", "70040902"),
            array('ES - Capital', "29000000", "29184999", "29060370"),
            array('MA - Capital', "65000000", "65099000", "65026260"),
            array('MG - Capital', "30000000", "34999999", "30190000"),
            array('MS - Capital', "79000000", "79124999", "79002400"),
            array('MT - Capital', "78000000", "78169999", "78020010"),
            array('PA - Capital', "66000000", "67999999", "66010902"),
            array('PB - Capital', "58000000", "58099999", "58011040"),
            array('PE - Capital', "50000000", "54999999", "50610360"),
            array('PI - Capital', "64000000", "64099999", "64001280"),
            array('PR - Capital', "80000000", "83729999", "80010000"),
            array('RJ - Capital', "20000000", "24799999", "20270270"),
            array('RN - Capital', "59000000", "59099000", "59030380"),
            array('RO - Capital', "76800000", "76834999", "76829684"),
            array('RR - Capital', "69300000", "69339999", "69312450"),
            array('RS - Capital', "90000000", "94999999", "90450090"),
            array('SC - Capital', "88000000", "88139999", "88010500"),
            array('SE - Capital', "49000000", "49099999", "49027000"),
            array('SP - Capital', "01000000", "09999999", "04811210"),
            array('TO - Capital', "77000000", "77299999", "77020116"),
        );

        $this->interiores = array(
            array('AC - Interior', "69921000", "69999999", "69921000"),
            array('AL - Interior', "57100000", "57999999", "57230000"),
            array('AM - Interior', "69100000", "69299000", "69110000"),
            array('AM - Interior', "69400000", "69899999", "69470000"),
            array('AP - Interior', "68930000", "68999999", "68970000"),
            array('BA - Interior', "43850000", "48999999", "44260000"),
            array('CE - Interior', "61700000", "63999999", "61930000"),
            array('ES - Interior', "29185000", "29999999", "29200250"),
            array('GO - Interior', "73700000", "76799999", "75044450"),
            array('MA - Interior', "65100000", "65999999", "65275000"),
            array('MG - Interior', "35000000", "39999999", "35930075"),
            array('MS - Interior', "79125000", "79999999", "79200000"),
            array('MT - Interior', "78170000", "78899999", "78307000"),
            array('PA - Interior', "68000000", "68899999", "68385000"),
            array('PB - Interior', "58100000", "58999999", "58428720"),
            array('PE - Interior', "55000000", "56999999", "55805000"),
            array('PI - Interior', "64100000", "64999999", "64310000"),
            array('PR - Interior', "83730000", "87999999", "84015070"),
            array('RJ - Interior', "24800000", "28999999", "24800000"),
            array('RN - Interior', "59100000", "59999999", "59140840"),
            array('RO - Interior', "76835000", "76999999", "76870762"),
            array('RR - Interior', "69340000", "69399999", "69340000"),
            array('RS - Interior', "95000000", "99999999", "95680000"),
            array('SC - Interior', "88140000", "89999999", "88220000"),
            array('SE - Interior', "49100000", "49999999", "49220000"),
            array('SP - Interior', "10000000", "19999999", "14801000"),
            array('TO - Interior', "77300000", "77999999", "77818550"),
        );

        return $this;
    }

    private function getXml($destino, $peso)
    {
        $this->fields['sCepDestino'] = $destino;
        $this->fields['nVlPeso']     = $peso;

        $url = $this->ws . http_build_query($this->fields);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;

    }

    public function getFrete($text, $destino, $peso)
    {

        $xml = simplexml_load_string($this->getXml($destino, $peso));

        foreach ($xml->Servicos->cServico as $servico) {
            $frete[(int) $servico->Codigo] = array(
                "text"              => $text,
                "destino"           => $destino,
                "origem"            => $this->fields['sCepOrigem'],
                "peso"              => $peso,
                "servico_codigo"    => (string) $servico->Codigo,
                "valor"             => (string) $servico->Valor,
                "prazo_entrega"     => (string) $servico->PrazoEntrega,
                "mao_propria"       => (string) $servico->ValorMaoPropria,
                "aviso_recebimento" => (string) $servico->ValorAvisoRecebimento,
                "valor_declarado"   => (string) $servico->ValorValorDeclarado,
                "en_domiciliar"     => (string) $servico->EntregaDomiciliar,
                "en_sabado"         => (string) $servico->EntregaSabado,
                "erro"              => (int) $servico->Erro,
                "msg_erro"          => (string) $servico->MsgErro,
            );
        }
        sort($frete);

        return $frete;
    }
}

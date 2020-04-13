<?php
/**
 *
 */
final class CancelarNfse extends AbstractNfseWs 
{
    /**
     * Define o nome do método a ser consumido no webservice
     * @return String - $this->method
     */
    private $method = 'CancelarNfse';

    /**
     * Retorna o método
     * @return String - $this->method
     */
    protected function getMethod()
    {
        return $this->method;
    }

    /**
     * Método para retornar os campos obrigatórios no XML
     * @return array - campos obrigatórios
     */
    protected function getRequiredFields()
    {
       // return ['Numero', 'Cnpj', 'InscricaoMunicipal', 'Cnpj', 'CodigoCancelamento'];
    }    

    /**
     * Método que retorna o layout
     * @return array $layout     
     */
    public function getLayout() {
        return [
            'CancelarNfse' => [
                '@attributes' => ['xmlns' => "http://www.e-governeapps2.com.br/"],
                'CancelarNfseEnvio' => [
                    'Pedido' => [
                        'InfPedidoCancelamento' => [
                            'IdentificacaoNfse' => [
                                'Numero' => null,
                                'Cnpj' => null,
                                'InscricaoMunicipal' => null
                            ],
                            'CodigoCancelamento' => null
                        ]
                    ],
                    'Signature' => [
                        '@attributes' => ['Id' => null]
                    ]
                ]
            ]
        ];
    }

}

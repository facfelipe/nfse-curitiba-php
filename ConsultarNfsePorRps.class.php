<?php
/**
 *
 */
final class ConsultarNfsePorRps extends AbstractNfseWs
{
    /**
     * Define o nome do método a ser consumido no webservice
     * @return String - $this->method
     */
    private $method = 'ConsultarNfsePorRps';

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
        return ['Numero', 'Serie', 'Tipo', 'Cnpj', 'InscricaoMunicipal'];
    }

    /**
     * Método que retorna o layout
     * @return array $layout     
     */
    public function getLayout()
    {
        return [
            'ConsultarNfsePorRps' => [
                '@attributes' => ['xmlns' => "http://www.e-governeapps2.com.br/"],
                'ConsultarNfseRpsEnvio' => [
                    'IdentificacaoRps' => [
                        'Numero' => null,
                        'Serie' => null,
                        'Tipo' => null,
                    ],
                    'Prestador' => [
                        'Cnpj' => null,
                        'InscricaoMunicipal' => null
                    ]
                ]
            ]
        ];
    }

}

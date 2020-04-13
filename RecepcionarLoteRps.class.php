<?php
/**
 *
 */
final class RecepcionarLoteRps extends AbstractNfseWs
{
    /**
     * Define o nome do método a ser consumido no webservice
     * @return String - $this->method
     */
    private $method = 'RecepcionarLoteRps';

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
       return ['NaturezaOperacao'];
    }

    /**
     * Sobreescreve a validação padrão
     * @param array $required - variáveis obrigatórias
     * @param array $required - variáveis passadas     
     * @throws RuntimeException - caso falte alguma
     * @return void
     */
    protected function validate($required, $vars)
    {
    	parent::validate($required, $vars);

    	if(!is_numeric($vars['NaturezaOperacao'])) {    	
    		throw new Exception('NaturezaOperacao inválida, deve ser um valor numérico');
    	}
    }        

    /**
     * Método que retorna o layout
     * @return array $layout
     */
    public function getLayout() 
    {

		$id = '000000001';

		$listaRps = [	
				['Rps' => [
					'InfRps' => [
						'@attributes' => ['Id' => $id ],
						'IdentificacaoRps' => [
		                	'Numero' => $id,
		                	'Serie' => 1,
		                	'Tipo' => 0
						],
						'DataEmissao' => null,
						'NaturezaOperacao' => 1,
						'RegimeEspecialTributacao' => 0,
						'OptanteSimplesNacional' => 1,
						'IncentivadorCultural' => 2,
						'Status' => 1,
						'Servico' => [
							'Valores' => [
								'ValorServicos' => 0.00,
								'ValorDeducoes' => 0.00,
								'ValorPis' => 0.00,
								'ValorCofins' => 0.00,
								'ValorInss' => 0.00,
								'ValorIr' => 0.00,
								'ValorCsll' => 0.00,
								'IssRetido' => null,
								'ValorIss' => 0.00,
								'ValorIssRetido' => 0.00,
								'OutrasRetencoes' => 0.00,
								'BaseCalculo' => 0.00,
								'Aliquota' => 0.00,
								'ValorLiquidoNfse' =>00.00,
								'DescontoIncondicionado' => 0.00,
								'DescontoCondicionado' => 0.00
							],
							'ItemListaServico' => null,
							'CodigoCnae' => null,
							'Discriminacao' => null,
							'CodigoMunicipio' => null
						],
						'Prestador' => [
					  		'Cnpj' => null, 
							'InscricaoMunicipal' => null
		                ],
						'Tomador' => [  
						    'RazaoSocial' => null,
						    'Endereco' => []    
						],
						'Signature' => null
					]				
				]
			]
		];


		return [
			'RecepcionarLoteRps' => [
				'@attributes' => ['xmlns' =>"http://www.e-governeapps2.com.br/"],
				'EnviarLoteRpsEnvio' => [
					'LoteRps' => [
						'@attributes' => ['Id' => $id],
						'NumeroLote' => $id,
						'Cnpj' => null,
						'InscricaoMunicipal' => null,
						'QuantidadeRps' => 1,
						'ListaRps' => $listaRps					
					],
					'Signature' => null //$signature
				]
			]
		];

    }	
	
}

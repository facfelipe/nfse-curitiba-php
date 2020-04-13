<?php
/**
 * Classe abstrata de comunicação com o webservice de emissão de NF-e de Serviço
 * contendo as definições de todos os métodos essenciais a todas as requisições e a implementação dos métodos comuns
 *
 * Exemplo de uso:
 *
 *      $client = new RecepcionarLoteRps();
 *
 *      $client->setOptions([
 *          'local_cert' => $certFile // o Certificado.pem
 *          'passphrase' => $passphrase // senha do certificado
 *      ])->setSigner(new Signer([
 *          'private_key' => $privateKey,
 *          'public_key'  => $publicKey
 *      ]));
 *     
 *      $client->debug = true;
 *
 *      $client->buildXML([
 *          Cnpj => $cnpj,
 *           ...
 *      ]); 
 *
 *      $result = $client->request();
 *
 *
 * @author Felipe Augusto Carretta
 * @version 1.0
 */
abstract class AbstractNfseWs extends SoapClient
{
    /**
     * Constante para a definição da URL do WSDL
     */
    const WSDL_URL = 'https://isscuritiba.curitiba.pr.gov.br/Iss.NfseWebService/nfsews.asmx?WSDL';

    /**
     * Constante do nome do arquivo de WSDL
     */
    const WSDL_FILE = 'nfsews.asmx.xml';

    /**
     * Armazena a quantidade de novas tentativas de requisição a serem feitas
     * @var int $retry
     */
    private $retry = 0;

    /**
     * Armazena a instância da classe de assinatura de XML
     * @var SignXml $Signer
     */
    private $Signer = null;

    /**
     * Armazena as opções de requisição padrão
     * @var array $options
     */
    protected $options = [];

    /**
     * XML de requisição
     * @var string $xml
     */
    protected $xml = null;

    /**
     * Flag para debugar a requisição
     * @var bool $debug
     */
    public $debug = false;

    /**
     * Declaração do método responsavel por retornar o nome do método a ser chamado no webservice
     */
    abstract protected function getMethod();

    /**
     * Declaração do método responsavel por retornar a estrutura do layout
     * @return array - layout 
     */
    abstract protected function getLayout();

    /**
     * Declaração do método responsavel por retornar os campos obrigatórios a serem enviados na requisição
     */
    abstract protected function getRequiredFields();    

    /**
     * Construtor, seta as opções do SoapClient
     * @param array $options 
     * @return void
     */
    public function __construct($options = [])
    {
        $this->setOptions($options);  
    }

    /**
     * Inicia o SoapClient
     * @return void
     */
    public function initSoapClient()
    {
        parent::__construct(
            $this->getWsdl(), $this->getOptions()
        );
    }

    /**
     * Seta as opções de requisição
     * @param array $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Retorna as opções fazendo um merge das definidas estaticamente com as recebidas 
     * @return array - com as opções de requisição
     */
    private function getOptions()
    {
        $options = array(
            'local_cert' => null, // Serão carregados em tempo de execução
            'passphrase' => null,
            'trace' => 1,
            'exceptions' => 1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'soap_version' => SOAP_1_2,
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'authentication'=> SOAP_AUTHENTICATION_BASIC  , //SOAP_AUTHENTICATION_DIGEST,       
            'stream_context' => stream_context_create(
                array(
                    'ssl' => array(
                        //'ciphers' => 'RC4-SHA',
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                        'crypto_method' =>  STREAM_CRYPTO_METHOD_SSLv23_CLIENT,                        
                    ),
                    'https' => array(
                        'curl_verify_ssl_peer'  => false,
                        'curl_verify_ssl_host'  => false
                    ),
                   // 'connection_timeout' => 60
                )
            )       
        );

        return array_merge($options, $this->options);
    }    

    /**
     * Seta a instância da classe de assinatura de XML
     * @param Signer $Signer
     * @return self
     */
    public function setSigner(Signer $Signer)
    {
        $this->Signer = $Signer;
        return $this;
    }   

    /**
     * Retorna o caminho do WSDL
     * @return String - $this->wsdlUrl
     */
    protected function getWsdl()
    {
        // Monta o diretório do WSDL
        $wsdl_dir = '..'.DIRECTORY_SEPARATOR . 'wsdl' . DIRECTORY_SEPARATOR;

        // Monta o caminho do WSDL
        $wsdl_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $wsdl_dir . self::WSDL_FILE;

        // Verifica se o arquivo WSDL existe
        if (!is_file($wsdl_path)) {
            throw new RuntimeException('WSDL file not found: ' . $wsdl_path);
        }

        // Verifica se tem permissão para ler o arquivo WSDL
        if (!is_readable($wsdl_path)) {
            throw new RuntimeException('WSDL path is not readable: ' . $wsdl_path);
        }

        return $wsdl_path;
    }

    /**
     * Executa a limpeza de caracteres inválidos no XML
     * @param string - $xml
     * @return string - $xml limpo
     */
    protected function cleanXML($xml) 
    {
        $xml = str_replace("º", " ", $xml);
        $xml  = trim(iconv(iconv_get_encoding('internal_encoding'), "UTF-8//TRANSLIT", $xml));
        return $xml;
    }

    /**
     * Seta o XML de requisição
     * @param string - $xml
     * @return self
     */
    public function setXML($xml)
    {
        $this->xml = $xml;
        return $this;
    }    

    /**
     * Retorna o XML de requisição
     * @return $xml
     */
    public function getXML()
    {
        return $this->xml;
    }

    /**
     * Método responsável por chamar a classe que assina o XML
     * @param string - XML de entrada
     * @return xml - XML object assinado
     */
    protected function assign($xmlStr)
    {   
        // Faz a assinatura se a classe foi passada
        if ($this->Signer) {
            return $this->Signer->doAssignment($xmlStr)->getXML();
        } else {
            return $xmlStr;
        }
    }

    /**
     * Método para debug
     * @return dados de requisição
     */
    private function debug($var, $msg = '')
    {
        if($this->debug) {
            echo '<pre>', "{$msg}: ", '<br>', (is_string($var) ? htmlentities($var) : print_r($var)), '</pre>';
        }
    }

    /**
     * Método que sobreescreve a requisição padrão do SoapClient para poder manipular o XML de requisição
     * @return dados de requisição
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $this->debug($request, 'XML de Requisição');

        return parent::__doRequest($request, $location, $action, $version,
                $one_way);
    }

    /**
     * Envia a requisição a um método do WebService
     * @param SoapVar $soapXML - o XML de requisição
     * @param int $try - númeto da tentativa
     * @throws SoapFault | RuntimeException
     * @return array - retorna a resposta
     */
    private function sendRequest(SoapVar $soapXML, $try = 0)
    {
        try {

            // Recupera o método a ser chamado
            $method = $this->getMethod();

            // Envia a requisição
            return $this->$method($soapXML);

        }  catch(SoapFault $e){

            // Faz novas tentativas se parametrizado em $this->retry
      
            if($this->retry > $try) {
                return $this->sendRequest($soapXML, ++$try);
            } else {
                throw $e;
            }

        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }        
    }

    /**
     * Método responsável por iniciar o processo da requisição ao WebService
     * @param string $xml - opcional, passar o XML na hora da requisição
     * @return a resposta da requisição ou lança a excessão em caso de falha
     */
    public function request($xml = null)
    {
        // Inicia o cliente Soap
        $this->initSoapClient();

        // Carrega o XML
        $xml = (null === $xml) ? $this->getXML() : $xml;

        // Faz a assinatura
        $xml = $this->assign($xml);

        // Coloca o XML em uma variável válida do Soap
        $soapXML = new SoapVar($xml, XSD_ANYXML);

        // Envia a requisição
        $response = $this->sendRequest($soapXML);

        // Trata a resposta
        return $this->response($response);        
    }

    /**
     * Método responsável por tratar a resposta da requisição
     * @return object
     */
    private function response($response)
    {
        $this->debug($response, 'Retorno');

        if (is_object($response)) {

            // Index padrão das mensagens de retorno
            $resultIndex = $this->getMethod().'Result';

            if (isset($response->$resultIndex)) {
                $response = $response->$resultIndex->ListaMensagemRetorno->MensagemRetorno;
            }
        }

        return $response;
    }

    /**
     * Retorna o XML da última requisição
     * @return XML request
     */
    public function getLastRequest()
    {
        return $this->__getLastRequest();
    }


    /**
     * Valida se estão foram passadas para o layout todas as variáveis requeridas
     * @param array $required - variáveis obrigatórias
     * @param array $required - variáveis passadas     
     * @throws InvalidArgumentException - caso falte alguma
     * @return void
     */
    protected function validate($required, $vars)
    {
        foreach ( (array) $required as $field) {
            if (!array_key_exists($field, $vars)) {             
                throw new InvalidArgumentException(sprintf('$vars["%s"] é obrigatório', $field));
            }
        }
    }    

    /**
     * Método responsável por bindar as variáveis no layout
     * @param array &$layout
     * @param array $vars
     * @return void
     */
    protected function bind(&$layout, $vars)
    {
        foreach ($layout as $key => &$value) {

            if (isset($vars[$key])) {
                $value = $vars[$key];
            } else {
                if (is_array($value)) {
                    $this->bind($value, $vars);
                }                    
            }
        }
    }

    /**
     * Função recursiva que converte um array para XML, suporta valores e atributos
     * @param array - os dados de requisição
     * @param string - prefixo das tags
     * @param string - a chave dos valores que devem ser inseridos como atributos
     * @return string $xml - o XML
     */
    public function arrayToXML($array, $tagPrefix = '', $attrKey = '@attributes')
    {
        $xml = '';

        foreach ((array) $array as $key => $value) {

            // Só adiciona tag em chaves não númericas
            if ($open = !is_int($key)) {
                $xml .= '<'.$tagPrefix.$key;
            }

            // Lê e adiciona os atributos
            if (is_array($value) and isset($value[$attrKey])) {

                $attrs = [];
                foreach ($value[$attrKey] as $attr => $valueAttr) {
                    $attrs[] = sprintf('%s="%s"', $attr, $valueAttr);
                }

                $xml .= ' '.join(' ', $attrs);

                // Adicionado ao xml, remove os atributos do array de valores
                unset($value[$attrKey]);
            }

            // Fecha a tag de abertura
            if ($open) {
                $xml .= '>';
            }

            // Adiciona o valor ou chama novamente a função se ele for um array
            $xml .= is_array($value) ? $this->arrayToXML($value) : $value;

            // Adiciona a tag de fechamento
            if ($open) {
                $xml .= '</'.$tagPrefix.$key.'>';
            }
        }

        return $xml;
    }    

    /**
     * Costrói o XML de requisição 
     * @param array $vars - variáveis
     * @param bool $useLayout - flag para não usar um layout, no caso de passar a estrutura do XML pronta
     * @return self
     */
    public function buildXML($vars = [], $useLayout = true)
    {
        // Valida se foi passado os campos obrigatórios
        $this->validate($this->getRequiredFields(), $vars);

        if($useLayout) {
            // Recupera o layout
            $xmlArr = $this->getLayout();

            // Adiciona as variáveis
            $this->bind($xmlArr, $vars);
        }

        // Converte para XML
        $xml = $this->arrayToXML($xmlArr);

        // Limpa
        $xml = $this->cleanXML($xml);

        // Seta o XML
        return $this->setXML($xml);
    }

}
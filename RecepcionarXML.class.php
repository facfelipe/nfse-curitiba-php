<?php
/**
 *
 */
final class RecepcionarXML  extends AbstractNfseWs
{
    /**
     * Define o nome do método a ser consumido no webservice
     * @return String - $this->method
     */
    private $method = 'RecepcionarXml';

    /**
     * Retorna o método
     * @return String - $this->method
     */
    protected function getMethod()
    {
        return $this->method;
    }
    
}

<?php
/**
 * Classe para os procedimentos de assinatura dos XML's
 */
class Signer
{
    /**
     * Armazena as chaves públicas e privadas
     * @var array $keys
     */
    private $keys = [
        'private_key' => null,
        'public_key' => null
    ];

    /**
     * Armazena o XML assinado
     * @var string $xml
     */
    private $xml = null;

    /**
     * Método construtor
     * @param array - chaves públicas e privadas
     * @return void
     */
    public function __construct($keys)
    {
        if (!isset($keys['private_key'])) {
            throw new RuntimeException('Chave privada não informada. File: '.__FILE__.':'.__LINE__);
        }

        if (!isset($keys['public_key'])) {
            throw new RuntimeException('Chave pública não informada'.__FILE__.':'.__LINE__);
        }

        return $this->keys = $keys;
    }

    /**
     * Assina o XML
     * @param string - XML de entrada
     * @return xml - XML object assinado
     */
    public function doAssignment($xmlString)
    {
        $xmldoc                    = new DOMDocument();
        $xmldoc->preservWhiteSpace = FALSE;
        $xmldoc->formatOutput      = FALSE;
        $xmldoc->loadXML($xmlString, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);

        $root = $xmldoc->documentElement;

        $l = $xmldoc->getElementsByTagName("Signature")->length;

        for ($i = 0; $i < $l; $i++) {
            $node = $xmldoc->getElementsByTagName("Signature")->item($i);

            if ($node) {
                $node    = $xmldoc->getElementsByTagName("Signature")->item($i)->parentNode;

                $xmldoc->getElementsByTagName($node->nodeName)->item(0)->removeChild($xmldoc->getElementsByTagName("Signature")->item($i));

                $id = trim($node->getAttribute("Id"));
                if (!$id) {
                    foreach ($node->childNodes as $verId) {
                        $id = trim($verId->getAttribute("Id"));
                        if ($id) {
                            break;
                        }
                    }
                }

                $childNode  = $node->childNodes->item(0);
                if ($childNode) {
                    $data          = $childNode->C14N(FALSE, FALSE, NULL, NULL);
                    $hashValue      = hash('sha1', $data, TRUE);
                    $digValue       = base64_encode($hashValue);
                    $Signature      = $xmldoc->createElementNS('http://www.w3.org/2000/09/xmldsig#',
                        'Signature');
                    $childNode->appendChild($Signature);
                    $SignedInfo     = $xmldoc->createElement('SignedInfo');
                    $Signature->appendChild($SignedInfo);
                    $newNode        = $xmldoc->createElement('CanonicalizationMethod');
                    $SignedInfo->appendChild($newNode);
                    $newNode->setAttribute('Algorithm',
                        'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
                    $newNode        = $xmldoc->createElement('SignatureMethod');
                    $SignedInfo->appendChild($newNode);
                    $newNode->setAttribute('Algorithm',
                        'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
                    $Reference      = $xmldoc->createElement('Reference');
                    $SignedInfo->appendChild($Reference);
                    $Reference->setAttribute('URI', '#'.$id);
                    $Transforms     = $xmldoc->createElement('Transforms');
                    $Reference->appendChild($Transforms);
                    $newNode        = $xmldoc->createElement('Transform');
                    $Transforms->appendChild($newNode);
                    $newNode->setAttribute('Algorithm', 
                        'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
                    $newNode        = $xmldoc->createElement('Transform');
                    $Transforms->appendChild($newNode);
                    $newNode->setAttribute('Algorithm',
                        'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
                    $newNode        = $xmldoc->createElement('DigestMethod');
                    $Reference->appendChild($newNode);
                    $newNode->setAttribute('Algorithm',
                        'http://www.w3.org/2000/09/xmldsig#sha1');
                    $newNode        = $xmldoc->createElement('DigestValue',
                        $digValue);
                    $Reference->appendChild($newNode);
                    $data          = $SignedInfo->C14N(FALSE, FALSE, NULL, NULL);
                    $signature      = '';
                    $resp           = openssl_sign($data, $signature,
                        $this->keys['private_key']);
                    $signatureValue = base64_encode($signature);
                    $newNode        = $xmldoc->createElement('SignatureValue',
                        $signatureValue);
                    $Signature->appendChild($newNode);
                    $KeyInfo        = $xmldoc->createElement('KeyInfo');
                    $Signature->appendChild($KeyInfo);
                    $X509Data       = $xmldoc->createElement('X509Data');
                    $KeyInfo->appendChild($X509Data);
                    $newNode        = $xmldoc->createElement('X509Certificate',
                        $this->keys['public_key']);
                    $X509Data->appendChild($newNode);
                }
            }
        }

        $this->xml = $xmldoc->saveXML();

        $this->xml = str_replace(array('<?xml version="1.0"?>', "\r\n", "\n", "\r"),
            '', $this->xml);

        return $this;
    }

    /**
     * Retorna o XML 
     * @return XML
     */
    public function getXML()
    {
        return $this->xml;
    }

}

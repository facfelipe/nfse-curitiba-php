<?php

require_once('autoload_nfse.php');

try {        

	$nfse = new RecepcionarLoteRps();

	$nfse->setOptions([
		'local_cert' => $certFile,
       	'passphrase' => $passphrase
    ])->setSigner(new Signer([
		'private_key' => $privateKey,
		'public_key'  => $publicKey
	]));
	
	$nfse->debug = true;

	$xmlArr = $nfse->getXML(); // Ou crie seu array

	// A lógica de preenchimento do array de dados vai aqui...

	$result = $nfse->request($xmlArr);


} catch(SoapFault $e){	
	echo 'SoapFault: ' . $e->faultcode . ' - ' . $e->faultstring;
} catch(RuntimeException $e){	
	echo 'RuntimeException: ' .  $e->getMessage();
} catch(Exception $e){
	echo 'Exception: ' . $e->getMessage();
} finally {
    exit(1);
}

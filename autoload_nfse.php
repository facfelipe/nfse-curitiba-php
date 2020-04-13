<?php
/**
 *  Arquivo responsávelo pelo autoload das classes de NFSE
 */
function __autoload($className)
{
    $nfseDir = dirname(__FILE__) . DIRECTORY_SEPARATOR;

    if (!class_exists($className)) {

        $classFile = $nfseDir . $className . '.class.php';

        if (is_file($classFile)) {
            require_once($classFile);
        } else {

            $classFile = $nfseDir . 'lib' . DIRECTORY_SEPARATOR . $className . '.class.php';

            if (is_file($classFile)) {
                require_once($classFile);
            }
        }
    }
}

__autoload('Signer');
__autoload('AbstractNfseWs');

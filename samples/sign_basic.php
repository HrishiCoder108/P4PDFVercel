<?php
//include the autoloader
require_once('../vendor/autoload.php');
//if manual installation has been used comment line that requires the autoload and uncomment this line:
//require_once('../init.php');

use P4Pdf\SignTask;
use P4Pdf\Sign\Receivers\Signer;
use P4Pdf\Sign\Elements\ElementSignature;
$signTask = new SignTask("project_public_key", "private_secret_key");

// We first upload the file that we are going to use
$file = $signTask->addFile('/path/to/file');

// Add signers and their elements;
$signatureElement = new ElementSignature();
$signatureElement->setPosition(20, -20)
                 ->setPages("1")
                 ->setSize(40);

// Create a signer
$signer = new Signer("name","signer@email.com");

// Assign the signer an element to be signed
$signer->addElements($file, $signatureElement);

$signTask->addReceiver($signer);
$signature = $signTask->execute()->result;
var_dump($signature);
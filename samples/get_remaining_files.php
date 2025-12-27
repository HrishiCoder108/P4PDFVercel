<?php
//include the autoloader
require_once('../vendor/autoload.php');
//if manual installation has been used comment line that requires the autoload and uncomment this line:
//require_once('../init.php');

use P4Pdf\P4Pdf;


// you can call task class directly
// to get your key pair, please visit https://developer.p4pdf.com/user/projects
$p4pdf = new P4Pdf('project_public_id', 'project_secret_key');


//get remaining files
$remainingFiles = $p4pdf->getRemainingFiles();


//print your remaining files
echo $remainingFiles;

//only start new process if you have enough files
if($remainingFiles>0) {
    //start the task
    $myTask = $p4pdf->newTask('merge');
}
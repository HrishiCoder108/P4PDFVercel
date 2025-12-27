<?php

namespace P4Pdf\Sign\Receivers;

use P4Pdf\Sign\Receivers\ReceiverAbstract;

class Witness extends ReceiverAbstract
{
    public function __construct(string $name, string $email)
    {
        $this->setType("viewer");
        parent::__construct($name,$email);
    }
}
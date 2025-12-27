<?php

namespace P4Pdf\Sign\Receivers;

use P4Pdf\Sign\Receivers\ReceiverAbstract;

class Validator extends ReceiverAbstract
{
    public function __construct(string $name, string $email)
    {
        $this->setType("validator");
        parent::__construct($name,$email);
    }
}
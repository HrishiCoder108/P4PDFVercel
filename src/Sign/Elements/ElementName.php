<?php

namespace P4Pdf\Sign\Elements;


class ElementName extends ElementAbstract
{
    public function __construct()
    {
        $this->setType("name");
    }
}
<?php

namespace P4Pdf\Sign\Elements;


class ElementSignature extends ElementAbstract
{
    /**
     * @var integer
     */
    public $size = 28;
    
    public function __construct()
    {
        $this->setType("signature");
    }

}
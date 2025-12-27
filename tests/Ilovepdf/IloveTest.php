<?php

namespace Tests\Ilovepdf;

use Ilovepdf\CompressTask;
use Ilovepdf\Ilovepdf;
use PHPUnit\Framework\TestCase;

/**
 * Base class for Stripe test cases, provides some utility methods for creating
 * objects.
 */
class IloveTest extends TestCase
{
    public $publicKey = 'project_public_0e3954d5a23025e9bde0614772624b9f_BKBIHd3994b8921c7724780451816478b89ba';
    public $secretKey = 'secret_key_999ef60f237f2f5e009eb796f716418d__jbVfd268a55ed5af1d854977a74b6df9c19e';


    public $publicKeyTest = "project_public_0e3954d5a23025e9bde0614772624b9f_BKBIHd3994b8921c7724780451816478b89ba";
    public $secretKeyTest = "secret_key_999ef60f237f2f5e009eb796f716418d__jbVfd268a55ed5af1d854977a74b6df9c19e";

    /**
     * @test
     */
    public function testIlovepdfCreateWithParams()
    {

        $ilovepdf = new Ilovepdf($this->publicKey, $this->secretKey);
        $ilovepdfTest = new Ilovepdf($this->publicKeyTest, $this->secretKeyTest);

        $this->assertEquals($ilovepdf->getPublicKey(), $this->publicKey);
        $this->assertEquals($ilovepdfTest->getPublicKey(), $this->publicKeyTest);
    }

    /**
     * @test
     */
    public function testIlovepdfEmptyParams()
    {
        $ilovepdf = new Ilovepdf();
        $ilovepdfTest = new Ilovepdf();

        $ilovepdf->setApiKeys($this->publicKey, $this->secretKey);
        $ilovepdfTest->setApiKeys($this->publicKeyTest, $this->secretKeyTest);


        $this->assertEquals($ilovepdf->getPublicKey(), $this->publicKey);
        $this->assertEquals($ilovepdfTest->getPublicKey(), $this->publicKeyTest);
    }
}

<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Security\Encoder;

use Contao\CoreBundle\Security\Encoder\ContaoLegacyPasswordEncoder;
use Contao\CoreBundle\Tests\TestCase;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

/**
 * Tests the ContaoLegacyPasswordEncoder class.
 */
class ContaoLegacyPasswordEncoderTest extends TestCase
{
    /**
     * @var ContaoLegacyPasswordEncoder
     */
    private $encoder;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->encoder = new ContaoLegacyPasswordEncoder();
    }

    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Security\Encoder\ContaoLegacyPasswordEncoder', $this->encoder);
    }

    /**
     * Tests the encodePassword method.
     */
    public function testEncodePassword()
    {
        $raw = random_bytes(16);
        $salt = random_bytes(8);

        $this->assertTrue(sha1($salt.$raw) === $this->encoder->encodePassword($raw, $salt));
    }

    /**
     * Tests for the BadCredentialsException for too long password.
     */
    public function testBadCredentialsException()
    {
        $raw = random_bytes(BasePasswordEncoder::MAX_PASSWORD_LENGTH + 1);
        $salt = random_bytes(8);

        $this->expectException('Symfony\Component\Security\Core\Exception\BadCredentialsException');

        $this->encoder->encodePassword($raw, $salt);
    }

    /**
     * Tests the isPasswordValidMethod.
     */
    public function testIsPasswordValid()
    {
        $raw = random_bytes(16);
        $long = random_bytes(BasePasswordEncoder::MAX_PASSWORD_LENGTH + 1);
        $salt = random_bytes(8);
        $encodedTrue = sha1($salt.$raw);
        $encodedFalse = '';

        $this->assertTrue($this->encoder->isPasswordValid($encodedTrue, $raw, $salt));
        $this->assertFalse($this->encoder->isPasswordValid($encodedFalse, $raw, $salt));
        $this->assertFalse($this->encoder->isPasswordValid($encodedTrue, $long, $salt));
        $this->assertFalse($this->encoder->isPasswordValid($encodedFalse, $long, $salt));
    }
}

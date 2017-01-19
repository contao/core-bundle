<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Test\Doctrine\DBAL\Types;

use Contao\CoreBundle\Doctrine\DBAL\Types\BinaryStringType;
use Contao\CoreBundle\Test\TestCase;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Tests the BinaryStringType class.
 *
 * @author Andreas Schempp <https://github.com/aschempp>
 */
class BinaryStringTypeTest extends TestCase
{
    /**
     * @var BinaryStringType
     */
    private $type;

    public static function setUpBeforeClass()
    {
        Type::addType(BinaryStringType::NAME, BinaryStringType::class);
    }

    protected function setUp()
    {
        $this->type = Type::getType(BinaryStringType::NAME);
    }


    /**
     * Tests the object instantiation.
     */
    public function testInstantiation()
    {
        $this->assertInstanceOf('Contao\CoreBundle\Doctrine\DBAL\Types\BinaryStringType', $this->type);
    }

    /**
     * Tests getSqlDeclaration returns binary definition for fixed length fields.
     */
    public function testGetSQLDeclarationWithFixedLength()
    {
        $fieldDefinition = ['fixed' => true];

        $platform = $this->getMock(AbstractPlatform::class);

        $platform
            ->expects($this->once())
            ->method('getBinaryTypeDeclarationSQL')
        ;

        $platform
            ->expects($this->never())
            ->method('getBlobTypeDeclarationSQL')
        ;

        $this->type->getSQLDeclaration($fieldDefinition, $platform);
    }

    /**
     * Tests getSqlDeclaration returns blob definition for variable length fields.
     */
    public function testGetSQLDeclarationWithVariableLength()
    {
        $fieldDefinition = ['false' => true];

        $platform = $this->getMock(AbstractPlatform::class);

        $platform
            ->expects($this->never())
            ->method('getBinaryTypeDeclarationSQL')
        ;

        $platform
            ->expects($this->once())
            ->method('getBlobTypeDeclarationSQL')
        ;

        $this->type->getSQLDeclaration($fieldDefinition, $platform);
    }

    /**
     * Tests the name.
     */
    public function testName()
    {
        $this->assertEquals(BinaryStringType::NAME, $this->type->getName());
    }

    /**
     * Tests the custom type requires an SQL hint.
     */
    public function testRequiresSQLCommentHint()
    {
        $platform = $this->getMock(AbstractPlatform::class);

        $this->assertTrue($this->type->requiresSQLCommentHint($platform));
    }
}

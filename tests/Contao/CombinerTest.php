<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Contao;

use Contao\Combiner;
use Contao\Config;
use Contao\CoreBundle\Asset\ContaoContext;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @group contao3
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CombinerTest extends ContaoTestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $fs = new Filesystem();
        $fs->mkdir(static::getTempDir().'/assets/css');
        $fs->mkdir(static::getTempDir().'/system/tmp');
        $fs->mkdir(static::getTempDir().'/web');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        \define('TL_ERROR', 'ERROR');
        \define('TL_ROOT', $this->getTempDir());

        $context = $this->createMock(ContaoContext::class);

        $context
            ->method('getStaticUrl')
            ->willReturn('')
        ;

        $this->container = new ContainerBuilder();
        $this->container->setParameter('contao.web_dir', $this->getTempDir().'/web');
        $this->container->set('contao.assets.assets_context', $context);

        System::setContainer($this->container);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf('Contao\Combiner', new Combiner());
    }

    public function testCombinesCssFiles(): void
    {
        file_put_contents($this->getTempDir().'/file1.css', 'file1 { background: url("foo.bar") }');
        file_put_contents($this->getTempDir().'/web/file2.css', 'web/file2');
        file_put_contents($this->getTempDir().'/file3.css', 'file3');
        file_put_contents($this->getTempDir().'/web/file3.css', 'web/file3');

        $mtime = filemtime($this->getTempDir().'/file1.css');

        $combiner = new Combiner();
        $combiner->add('file1.css');
        $combiner->addMultiple(['file2.css', 'file3.css']);

        $this->assertSame(
            [
                'file1.css|'.$mtime,
                'file2.css|screen|'.$mtime,
                'file3.css|screen|'.$mtime,
            ],
            $combiner->getFileUrls()
        );

        $combinedFile = $combiner->getCombinedFile();

        $this->assertRegExp('/^assets\/css\/file1\.css\,file2\.css\,file3\.css-[a-z0-9]+\.css$/', $combinedFile);

        $this->assertStringEqualsFile(
            $this->getTempDir().'/'.$combinedFile,
            "file1 { background: url(\"../../foo.bar\") }\n@media screen{\nweb/file2\n}\n@media screen{\nfile3\n}\n"
        );

        Config::set('debugMode', true);

        $hash = substr(md5((string) $mtime), 0, 8);

        $this->assertSame(
            'file1.css?v='.$hash.'"><link rel="stylesheet" href="file2.css?v='.$hash.'" media="screen"><link rel="stylesheet" href="file3.css?v='.$hash.'" media="screen',
            $combiner->getCombinedFile()
        );
    }

    public function testFixesTheFilePaths(): void
    {
        $class = new \ReflectionClass(Combiner::class);
        $method = $class->getMethod('fixPaths');
        $method->setAccessible(true);

        $css = <<<'EOF'
test1 { background: url(foo.bar) }
test2 { background: url("foo.bar") }
test3 { background: url('foo.bar') }
EOF;

        $expected = <<<'EOF'
test1 { background: url(../../foo.bar) }
test2 { background: url("../../foo.bar") }
test3 { background: url('../../foo.bar') }
EOF;

        $this->assertSame(
            $expected,
            $method->invokeArgs($class->newInstance(), [$css, ['name' => 'file.css']])
        );
    }

    public function testHandlesSpecialCharactersWhileFixingTheFilePaths(): void
    {
        $class = new \ReflectionClass(Combiner::class);
        $method = $class->getMethod('fixPaths');
        $method->setAccessible(true);

        $css = <<<'EOF'
test1 { background: url(foo.bar) }
test2 { background: url("foo.bar") }
test3 { background: url('foo.bar') }
EOF;

        $expected = <<<'EOF'
test1 { background: url("../../\"test\"/foo.bar") }
test2 { background: url("../../\"test\"/foo.bar") }
test3 { background: url('../../"test"/foo.bar') }
EOF;

        $this->assertSame(
            $expected,
            $method->invokeArgs($class->newInstance(), [$css, ['name' => 'web/"test"/file.css']])
        );

        $expected = <<<'EOF'
test1 { background: url("../../'test'/foo.bar") }
test2 { background: url("../../'test'/foo.bar") }
test3 { background: url('../../\'test\'/foo.bar') }
EOF;

        $this->assertSame(
            $expected,
            $method->invokeArgs($class->newInstance(), [$css, ['name' => "web/'test'/file.css"]])
        );

        $expected = <<<'EOF'
test1 { background: url("../../(test)/foo.bar") }
test2 { background: url("../../(test)/foo.bar") }
test3 { background: url('../../(test)/foo.bar') }
EOF;

        $this->assertSame(
            $expected,
            $method->invokeArgs($class->newInstance(), [$css, ['name' => 'web/(test)/file.css']])
        );
    }

    public function testIgnoresDataUrlsWhileFixingTheFilePaths(): void
    {
        $class = new \ReflectionClass(Combiner::class);
        $method = $class->getMethod('fixPaths');
        $method->setAccessible(true);

        $css = <<<'EOF'
test1 { background: url('data:image/svg+xml;utf8,<svg id="foo"></svg>') }
test2 { background: url("data:image/svg+xml;utf8,<svg id='foo'></svg>") }
EOF;

        $this->assertSame(
            $css,
            $method->invokeArgs($class->newInstance(), [$css, ['name' => 'file.css']])
        );
    }

    public function testCombinesScssFiles(): void
    {
        file_put_contents($this->getTempDir().'/file1.scss', '$color: red; @import "file1_sub";');
        file_put_contents($this->getTempDir().'/file1_sub.scss', 'body { color: $color }');
        file_put_contents($this->getTempDir().'/file2.scss', 'body { color: green }');

        $mtime1 = filemtime($this->getTempDir().'/file1.scss');
        $mtime2 = filemtime($this->getTempDir().'/file2.scss');

        $combiner = new Combiner();
        $combiner->add('file1.scss');
        $combiner->add('file2.scss');

        $this->assertSame(
            [
                'assets/css/file1.scss.css|'.$mtime1,
                'assets/css/file2.scss.css|'.$mtime2,
            ],
            $combiner->getFileUrls()
        );

        $this->assertStringEqualsFile(
            $this->getTempDir().'/'.$combiner->getCombinedFile(),
            "body{color:red}\nbody{color:green}\n"
        );

        Config::set('debugMode', true);

        $hash1 = substr(md5((string) $mtime1), 0, 8);
        $hash2 = substr(md5((string) $mtime2), 0, 8);

        $this->assertSame(
            'assets/css/file1.scss.css?v='.$hash1.'"><link rel="stylesheet" href="assets/css/file2.scss.css?v='.$hash2,
            $combiner->getCombinedFile()
        );
    }

    public function testCombinesJsFiles(): void
    {
        file_put_contents($this->getTempDir().'/file1.js', 'file1();');
        file_put_contents($this->getTempDir().'/web/file2.js', 'file2();');

        $mtime1 = filemtime($this->getTempDir().'/file1.js');
        $mtime2 = filemtime($this->getTempDir().'/web/file2.js');

        $combiner = new Combiner();
        $combiner->add('file1.js');
        $combiner->add('file2.js');

        $this->assertSame(
            [
                'file1.js|'.$mtime1,
                'file2.js|'.$mtime2,
            ],
            $combiner->getFileUrls()
        );

        $combinedFile = $combiner->getCombinedFile();

        $this->assertRegExp('/^assets\/js\/file1\.js\,file2\.js-[a-z0-9]+\.js$/', $combinedFile);
        $this->assertStringEqualsFile($this->getTempDir().'/'.$combinedFile, "file1();\nfile2();\n");

        Config::set('debugMode', true);

        $hash1 = substr(md5((string) $mtime1), 0, 8);
        $hash2 = substr(md5((string) $mtime2), 0, 8);

        $this->assertSame('file1.js?v='.$hash1.'"></script><script src="file2.js?v='.$hash2, $combiner->getCombinedFile());
    }
}

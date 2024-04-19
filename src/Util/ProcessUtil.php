<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Util;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\PhpSubprocess;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Service\ResetInterface;

class ProcessUtil implements ResetInterface
{
    private string|null $phpBinary = null;

    public function __construct(private readonly string $consolePath)
    {
    }

    /**
     * Creates a GuzzleHttp/Promise for a Symfony Process instance.
     *
     * @param bool $start automatically calls Process::start() if true
     */
    public function createPromise(Process $process, bool $start = true): PromiseInterface
    {
        $promise = new Promise(
            static function () use ($process, &$promise): void {
                $process->wait();

                if ($process->isSuccessful()) {
                    $promise->resolve($process->getOutput());
                } else {
                    $promise->reject($process->getErrorOutput() ?: $process->getOutput());
                }
            },
        );

        if ($start) {
            $process->start();
        }

        return $promise;
    }

    public function createSymfonyConsoleProcess(string $command, string ...$commandArguments): Process
    {
        // Use PhpSubprocess introduced in Symfony 6.4 to respect command line arguments
        // used to invoke the current process.
        return new PhpSubprocess([$this->getPhpBinary(), $this->getConsolePath(), $command, ...$commandArguments]);
    }

    public function reset(): void
    {
        $this->phpBinary = null;
    }

    public function getConsolePath(): string
    {
        return $this->consolePath;
    }

    public function getPhpBinary(): string
    {
        if (null === $this->phpBinary) {
            $executableFinder = new PhpExecutableFinder();
            $this->phpBinary = $executableFinder->find();
        }

        return $this->phpBinary;
    }
}

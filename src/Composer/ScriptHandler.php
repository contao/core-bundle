<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Composer;

use Composer\Script\Event;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Sets up the Contao environment in a Symfony app.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ScriptHandler
{
    const RANDOM_SECRET_NAME = 'CONTAO_RANDOM_SECRET';

    /**
     * Adds the Contao directories.
     *
     * @param Event $event The event object
     */
    public static function addDirectories(Event $event)
    {
        self::executeCommand('contao:install', $event);
    }

    /**
     * Generates the symlinks.
     *
     * @param Event $event The event object
     */
    public static function generateSymlinks(Event $event)
    {
        self::executeCommand('contao:symlinks', $event);
    }

    /**
     * Sets the environment variable for the random secret.
     *
     * @param Event $event The event object
     */
    public static function generateRandomSecret(Event $event)
    {
        $extra = $event->getComposer()->getPackage()->getExtra();

        if (!isset($extra['incenteev-parameters']) || !self::canGenerateSecret($extra['incenteev-parameters'])) {
            return;
        }

        if (!function_exists('random_bytes')) {
            self::loadRandomCompat($event);
        }

        putenv(static::RANDOM_SECRET_NAME.'='.bin2hex(random_bytes(32)));
    }

    /**
     * Executes a command.
     *
     * @param string $cmd   The command
     * @param Event  $event The event object
     *
     * @throws \RuntimeException If the PHP executable cannot be found or the command cannot be executed
     */
    private static function executeCommand($cmd, Event $event)
    {
        $phpFinder = new PhpExecutableFinder();

        if (false === ($phpPath = $phpFinder->find())) {
            throw new \RuntimeException('The php executable could not be found.');
        }

        $ansi = '';
        if ($event->getIO()->isDecorated()) {
            $ansi = '--ansi';
        }

        $process = new Process(sprintf('%s app/console %s %s', $phpPath, $ansi, $cmd));

        $process->run(
            function ($type, $buffer) use ($event) {
                $event->getIO()->write($buffer, false);
            }
        );

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(sprintf('An error occurred while executing the "%s" command.', $cmd));
        }
    }

    /**
     * Checks if there is at least one config file defined but none of the files exists.
     *
     * @param array $config The incenteev-parameters configuration
     *
     * @return bool True if there is at least one config file defined but none of the files exists
     */
    private static function canGenerateSecret(array $config)
    {
        if (isset($config['file'])) {
            return !is_file($config['file']);
        }

        foreach ($config as $v) {
            if (is_array($v) && isset($v['file']) && is_file($v['file'])) {
                return false;
            }
        }

        return !empty($config);
    }

    /**
     * Loads the random_compat library.
     *
     * @param Event $event The event object
     */
    private static function loadRandomCompat(Event $event)
    {
        $composer = $event->getComposer();

        $package = $composer
            ->getRepositoryManager()
            ->getLocalRepository()
            ->findPackage('paragonie/random_compat', '*')
        ;

        if (null === $package) {
            return;
        }

        $autoload = $package->getAutoload();

        if (empty($autoload['files'])) {
            return;
        }

        $path = $composer->getInstallationManager()->getInstaller('library')->getInstallPath($package);

        foreach ($autoload['files'] as $file) {
            include_once $path.'/'.$file;
        }
    }
}

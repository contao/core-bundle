<?php

/**
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

error_reporting(E_ALL);

// Define a custom System class via eval() so it does not interfere with the IDE
eval(<<<HEREDOC
namespace Contao;

class System
{
    public static function getReferer()
    {
        return '/foo/bar';
    }
}
HEREDOC
);
class_alias('\Contao\System', 'System');

// Define a custom Frontend class via eval() so it does not interfere with the IDE
eval(<<<HEREDOC
namespace Contao;

use Symfony\Component\HttpFoundation\Response;

class Frontend
{
    public static function indexPageIfApplicable(Response \$objResponse)
    {
        return true;
    }

    public static function getResponseFromCache()
    {
        return new Response();
    }
}
HEREDOC
);
class_alias('\Contao\Frontend', 'Frontend');

// Define a custom Dbafs class via eval() so it does not interfere with the IDE
eval(<<<HEREDOC
namespace Contao;

class Dbafs
{
    public static function syncFiles()
    {
        return 'sync.log';
    }
}
HEREDOC
);

// Define a custom Automator class via eval() so it does not interfere with the IDE
eval(<<<HEREDOC
namespace Contao;

class Automator
{
    public function checkForUpdates() {}
    public function purgeSearchTables() {}
    public function purgeUndoTable() {}
    public function purgeVersionTable() {}
    public function purgeSystemLog() {}
    public function purgeImageCache() {}
    public function purgeScriptCache() {}
    public function purgePageCache() {}
    public function purgeSearchCache() {}
    public function purgeInternalCache() {}
    public function purgeTempFolder() {}
    public function generateXmlFiles() {}
    public function purgeXmlFiles() {}
    public function generateSitemap() {}
    public function rotateLogs() {}
    public function generateSymlinks() {}
    public function generateInternalCache() {}
    public function generateConfigCache() {}
    public function generateDcaCache() {}
    public function generateLanguageCache() {}
    public function generateDcaExtracts() {}
    public function generatePackageCache() {}

}
HEREDOC
);

// Define a custom Config class via eval() so it does not interfere with the IDE
eval(<<<HEREDOC
namespace Contao;

class Config
{
    private static \$cache = [];

    private static \$instance;

    public static function getInstance()
    {
        if (null === static::\$instance)
        {
            static::\$instance = new static();
        }

        return static::\$instance;
    }

    public static function clear(\$data = array())
    {
        static::\$cache = \$data;
    }

    public static function set(\$key, \$value)
    {
        static::\$cache[\$key] = \$value;
    }

    public static function get(\$key)
    {
        if (isset(static::\$cache[\$key])) {
            return static::\$cache[\$key];
        }

        return null;
    }

    public static function has(\$key)
    {
        return isset(static::\$cache[\$key]);
    }

    public static function preload() {}

    public static function isComplete()
    {
        return true;
    }
}
HEREDOC
);

// Define a custom Environment class via eval() so it does not interfere with the IDE
eval(<<<HEREDOC
namespace Contao;

class Environment
{
    private static \$cache = [];

    public static function clear(\$data = array())
    {
        static::\$cache = \$data;
    }

    public static function set(\$key, \$value)
    {
        static::\$cache[\$key] = \$value;
    }

    public static function get(\$key)
    {
        if (isset(static::\$cache[\$key])) {
            return static::\$cache[\$key];
        }

        return null;
    }

    public static function has(\$key)
    {
        return isset(static::\$cache[\$key]);
    }
}
HEREDOC
);
class_alias('\Contao\Environment', 'Environment');

require_once __DIR__ . '/../contao/library/Contao/Input.php';
class_alias('\Contao\Input', 'Input');
require_once __DIR__ . '/../contao/library/Contao/RequestToken.php';
class_alias('\Contao\RequestToken', 'RequestToken');


$include = function ($file) {
    return file_exists($file) ? include $file : false;
};

if (
    false === ($loader = $include(__DIR__ . '/../vendor/autoload.php'))
    && false === ($loader = $include(__DIR__ . '/../../../autoload.php'))
) {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL
        . 'curl -sS https://getcomposer.org/installer | php' . PHP_EOL
        . 'php composer.phar install' . PHP_EOL;

    exit(1);
}

/** @var Composer\Autoload\ClassLoader $loader */
$loader->addPsr4('Contao\\CoreBundle\\Test\\', __DIR__);

return $loader;

<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Controller\FragmentRegistry;

class InvalidConfigurationException extends \InvalidArgumentException
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @return ConfigurationInterface|null
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param ConfigurationInterface $configuration
     *
     * @return InvalidConfigurationException
     */
    public function setConfiguration(ConfigurationInterface $configuration = null)
    {
        $this->configuration = $configuration;

        return $this;
    }
}

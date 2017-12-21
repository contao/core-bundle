<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Session;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Lazily initializes the session in case anyone uses the legacy
 * direct access via $_SESSION and just expecting it was started before.
 */
class LegacySessionAccess implements \ArrayAccess, \Countable
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * LegacySessionAccess constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $this->ensureSessionStarted();

        return $this->session->has($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->ensureSessionStarted();

        return $this->session->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->ensureSessionStarted();

        $this->session->set($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->ensureSessionStarted();

        $this->session->remove($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->ensureSessionStarted();

        return count($this->session->all());
    }

    /**
     * Ensures a session is initialized as soon as someone accesses
     * $_SESSION and we can store certain attributes accordingly.
     */
    private function ensureSessionStarted()
    {
        var_dump(debug_backtrace(0, 5));
        @trigger_error('Accessing $_SESSION directly is deprecated and support will be dropped with Contao 5.0. Use the Symfony request instead to work with the session.', E_USER_DEPRECATED);

        if (!$this->session->isStarted()) {
            $this->session->start();
        }

        $_SESSION['BE_DATA'] = $this->session->getBag('contao_backend');
        $_SESSION['FE_DATA'] = $this->session->getBag('contao_frontend');
    }
}

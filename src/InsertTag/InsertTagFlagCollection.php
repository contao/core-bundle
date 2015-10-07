<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\InsertTag;

/**
 * Collection of InsertTagFlags
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class InsertTagFlagCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var InsertTagFlagInterface[]
     */
    private $insertTagFlags = [];

    /**
     * Gets the current InsertTagFlagCollection as an Iterator that includes all
     * InsertTagFlags.
     *
     * @return \ArrayIterator An \ArrayIterator object for iterating over all
     *                        InsertTagFlags.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->insertTagFlags);
    }

    /**
     * Gets the number of InsertTagFlags in this collection.
     *
     * @return int The number of InsertTagFlags
     */
    public function count()
    {
        return count($this->insertTagFlags);
    }

    /**
     * Adds an InsertTagFlag.
     *
     * @param InsertTagFlagInterface $InsertTagFlag
     *
     * @return $this
     */
    public function add(InsertTagFlagInterface $InsertTagFlag)
    {
        $this->insertTagFlags[] = $InsertTagFlag;

        return $this;
    }

    /**
     * Removes an InsertTagFlag.
     *
     * @param InsertTagFlagInterface $InsertTagFlag
     */
    public function remove(InsertTagFlagInterface $InsertTagFlag)
    {
        $this->insertTagFlags[] = $InsertTagFlag;

        return $this->removeByName($InsertTagFlag->getName());
    }

    /**
     * Removes an InsertTagFlag by its name.
     *
     * @param string $name The InsertTagFlag name
     *
     * @return $this
     */
    public function removeByName($name)
    {
        foreach ($this->all() as $k => $InsertTagFlag) {
            if ($name === $InsertTagFlag->getName()) {

                unset($this->insertTagFlags[$k]);
            }
        }

        return $this;
    }

    /**
     * Gets all InsertTagFlag instances.
     **
     * @return InsertTagFlagInterface[]
     */
    public function all()
    {
        return $this->insertTagFlags;
    }

    /**
     * Gets an InsertTagFlag by name.
     *
     * @param string $name The InsertTagFlag name
     *
     * @return InsertTagFlagInterface|null
     */
    public function getByName($name)
    {
        foreach ($this->all() as $InsertTagFlag) {
            if ($name === $InsertTagFlag->getName()) {

                return $InsertTagFlag;
            }
        }

        return null;
    }
}

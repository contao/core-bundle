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
 * Collection of InsertTags
 *
 * @author Yanick Witschi <https://github.com/toflar>
 */
class InsertTagCollection implements \IteratorAggregate, \Countable
{
    /**
     * @var InsertTagInterface[]
     */
    private $insertTags = [];

    /**
     * Gets the current InsertTagCollection as an Iterator that includes all
     * InsertTags.
     *
     * @return \ArrayIterator An \ArrayIterator object for iterating over all
     *                        InsertTags.
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->insertTags);
    }

    /**
     * Gets the number of InsertTags in this collection.
     *
     * @return int The number of InsertTags
     */
    public function count()
    {
        return count($this->insertTags);
    }

    /**
     * Adds an InsertTag.
     *
     * @param InsertTagInterface $insertTag
     *
     * @return $this
     */
    public function add(InsertTagInterface $insertTag)
    {
        $this->insertTags[] = $insertTag;

        return $this;
    }

    /**
     * Removes an InsertTag.
     *
     * @param InsertTagInterface $insertTag
     */
    public function remove(InsertTagInterface $insertTag)
    {
        $this->insertTags[] = $insertTag;

        return $this->removeByName($insertTag->getName());
    }

    /**
     * Removes an InsertTag by its name.
     *
     * @param string $name The InsertTag name
     *
     * @return $this
     */
    public function removeByName($name)
    {
        foreach ($this->all() as $k => $insertTag) {
            if ($name === $insertTag->getName()) {

                unset($this->insertTags[$k]);
            }
        }

        return $this;
    }

    /**
     * Gets all InsertTag instances.
     **
     * @return InsertTagInterface[]
     */
    public function all()
    {
        return $this->insertTags;
    }

    /**
     * Gets an InsertTag by name.
     *
     * @param string $name The InsertTag name
     *
     * @return InsertTagInterface|null
     */
    public function getByName($name)
    {
        foreach ($this->all() as $insertTag) {
            if ($name === $insertTag->getName()) {

                return $insertTag;
            }
        }

        return null;
    }
}
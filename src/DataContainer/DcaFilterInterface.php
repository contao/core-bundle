<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\DataContainer;

/**
 * DCA filter interface.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 *
 * @deprecated Deprecated since Contao 4.4.1, to be removed in Contao 5.
 */
interface DcaFilterInterface
{
    /**
     * Returns the filter array.
     *
     * @return array
     *
     * @see DataContainer::setDcaFilter()
     * @see DC_Folder::setDcaFilter()
     */
    public function getDcaFilter();
}

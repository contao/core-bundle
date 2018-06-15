<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\EventListener\InsertTagFlag;

use Contao\CoreBundle\Event\InsertTagFlagEvent;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class DefaultInsertTagFlagsListener
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * @param InsertTagFlagEvent $event
     */
    public function onInsertTagFlag(InsertTagFlagEvent $event): void
    {
        $response = $event->getResponse();
        $content = $response->getContent();

        /** @var System $system */
        $system = $this->framework->getAdapter(System::class);

        switch ($flag = $event->getFlag()) {
            case 'addslashes':
            case 'standardize':
            case 'ampersand':
            case 'specialchars':
            case 'nl2br':
            case 'nl2br_pre':
            case 'strtolower':
            case 'utf8_strtolower':
            case 'strtoupper':
            case 'utf8_strtoupper':
            case 'ucfirst':
            case 'lcfirst':
            case 'ucwords':
            case 'trim':
            case 'rtrim':
            case 'ltrim':
            case 'utf8_romanize':
            case 'urlencode':
            case 'rawurlencode':
                $content = $flag($content);
                break;

            case 'encodeEmail':
                $content = \StringUtil::$flag($content);
                break;

            case 'number_format':
                $content = $system->getFormattedNumber($content, 0);
                break;

            case 'currency_format':
                $content = $system->getFormattedNumber($content, 2);
                break;

            case 'readable_size':
                $content = $system->getReadableSize($content);
                break;

            // HOOK: pass unknown flags to callback functions
            default:
                if (isset($GLOBALS['TL_HOOKS']['insertTagFlags']) && \is_array($GLOBALS['TL_HOOKS']['insertTagFlags'])) {
                    foreach ($GLOBALS['TL_HOOKS']['insertTagFlags'] as $callback) {
                        @trigger_error(sprintf(
                            'The hook insertTagFlags is deprecated and will be removed in 5.0. Use the InsertTagFlag event instead. (%s::%s).',
                            $callback[0],
                            $callback[1]
                        ), E_USER_DEPRECATED);

                        $varValue = $system->importStatic($callback[0])->{$callback[1]}(
                            $flag,
                            $event->getInsertTag(),
                            $content,
                            [$flag],
                            false,
                            ['', $event->getInsertTag() . '::' . $event->getParameters()],
                            [],
                            0,
                            0
                        );

                        // Replace the tag and stop the loop
                        if (false !== $varValue) {
                            $content = $varValue;
                            break;
                        }
                    }
                }
        }

        $response->setContent($content);
    }
}

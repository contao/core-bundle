<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Controller\InsertTag;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class TranslationController extends AbstractInsertTagController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Request $request
     * @param string  $parameters
     * @param array   $flags
     *
     * @return Response
     */
    protected function getResponse(Request $request, string $parameters, array $flags): Response
    {
        $chunks = explode('::', $parameters);
        $parameters = isset($chunks[2]) ? explode(':', $chunks[2]) : [];

        // It makes no sense to cache translations in shared cache, this is set by default
        // so it requires no action by us here.
       return new Response($this->translator->trans($chunks[0], $parameters, $chunks[1] ?? null));
    }
}

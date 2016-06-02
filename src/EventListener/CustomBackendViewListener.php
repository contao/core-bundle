<?php

namespace Contao\CoreBundle\EventListener;

use Contao\BackendRoute;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CustomBackendViewListener
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        $scope = $request->attributes->get('_scope');
        $isCustomBackendView = $request->attributes->get('_custom_backend_view');

        if ('backend' !== $scope) {
            return;
        }

        if (!$isCustomBackendView) {
            return;
        }

        $backendRoute = new BackendRoute();
        $backendTemplate = $backendRoute->getBaseTemplate();

        $backendTemplate->main = $event->getResponse()->getContent();

        $event->setResponse($backendRoute->run());
    }
}
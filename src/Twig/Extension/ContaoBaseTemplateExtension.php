<?php

namespace Contao\CoreBundle\Twig\Extension;

use Contao\BackendRoute;
use Symfony\Component\HttpFoundation\RequestStack;

class ContaoBaseTemplateExtension extends \Twig_Extension
{
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('contao_base_template_wrapper', [$this, 'wrapWithBaseTemplate'])
        ];
    }

    public function wrapWithBaseTemplate(array $blocks = [])
    {
        $scope = $this->requestStack->getCurrentRequest()->attributes->get('_scope');

        if ('backend' !== $scope) {
            return '';
        }

        $backendRoute = new BackendRoute();
        $backendTemplate = $backendRoute->getBaseTemplate();

        foreach ($blocks as $key => $content) {
            $backendTemplate->{$key} = $content;
        }

        $response = $backendRoute->run();

        return $response->getContent();
    }

    public function getName()
    {
        return 'base_template_wrapper';
    }

}
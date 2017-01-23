<?php

namespace Contao\CoreBundle\Twig\Extension;

use Contao\BackendRoute;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContaoBaseTemplateExtension extends \Twig_Extension
{
    private $requestStack;
    private $contaoFramework;

    public function __construct(RequestStack $requestStack, ContaoFrameworkInterface $contaoFramework)
    {
        $this->requestStack = $requestStack;
        $this->contaoFramework = $contaoFramework;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('contao_base_template', [$this, 'contaoBaseTemplate'])
        ];
    }

    public function contaoBaseTemplate(array $blocks = [])
    {
        $scope = $this->requestStack->getCurrentRequest()->attributes->get('_scope');

        if ('backend' !== $scope) {
            return '';
        }

        /** @var BackendRoute $backendRoute */
        $backendRoute = $this->contaoFramework->createInstance(BackendRoute::class);
        $backendTemplate = $backendRoute->getBaseTemplate();

        foreach ($blocks as $key => $content) {
            $backendTemplate->{$key} = $content;
        }

        $response = $backendRoute->run();

        return $response->getContent();
    }

    public function getName()
    {
        return 'contao_base_template';
    }

}
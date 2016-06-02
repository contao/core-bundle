<?php

namespace Contao;

class BackendRoute extends BackendMain
{
    public function __construct()
    {
        parent::__construct();

        $this->Template = new \BackendTemplate('be_main');
    }

    public function run()
    {
        $packages = $this->getContainer()->getParameter('kernel.packages');

        $this->Template->version = $packages['contao/core-bundle'];

        // Ajax request
        if ($_POST && \Environment::get('isAjaxRequest'))
        {
            $this->objAjax = new \Ajax(\Input::post('action'));
            $this->objAjax->executePreActions();
        }

        return $this->output();
    }

    public function getBaseTemplate()
    {
        return $this->Template;
    }
}
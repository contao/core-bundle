<?php

namespace Contao\CoreBundle\Asset;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\PageModel;
use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContaoContext implements ContextInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $field;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var PageModel|null
     */
    private $page;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param RequestStack             $requestStack
     * @param string                   $field
     * @param bool                     $debug
     */
    public function __construct(ContaoFrameworkInterface $framework, RequestStack $requestStack, string $field, bool $debug = false)
    {
        $this->framework = $framework;
        $this->requestStack = $requestStack;
        $this->field = $field;
        $this->debug = $debug;
    }

    /**
     * Sets the current page model.
     *
     * @param PageModel|null $page
     */
    public function setPage(?PageModel $page)
    {
        $this->page = $page;
    }

    /**
     * Gets the current page model.
     *
     * @return PageModel|null
     */
    public function getPage(): ?PageModel
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath()
    {
        $request = $this->requestStack->getCurrentRequest();
        $host = $this->getFieldValue();

        if ($this->debug || '' === $host || null === $request) {
            return '';
        }

        return sprintf(
            '%s://%s%s',
            $this->isSecure() ? 'https://' : 'http://',
            preg_replace('@https?://@', '', $host),
            $request->getBasePath()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure()
    {
        if (null === $this->page) {
            $request = $this->requestStack->getCurrentRequest();

            if (null === $request) {
                return false;
            }

            return $request->isSecure();
        }

        return (bool) $this->page->loadDetails()->rootUseSSL;
    }

    /**
     * @return string
     */
    private function getFieldValue(): string
    {
        if (null === $this->page) {
            /** @var Config $config */
            $config = $this->framework->createInstance(Config::class);

            return (string) $config->get($this->field);
        }

        return (string) $this->page->{$this->field};
    }
}

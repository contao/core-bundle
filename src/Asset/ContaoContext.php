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
     * {@inheritdoc}
     */
    public function getBasePath()
    {
        $page = $this->getPage();
        $request = $this->requestStack->getCurrentRequest();

        if ($this->debug || null === $request || '' === ($host = $this->getFieldValue($page))) {
            return '';
        }

        return sprintf(
            '%s://%s%s',
            $this->isSecure() ? 'https' : 'http',
            preg_replace('@https?://@', '', $host),
            $request->getBasePath()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isSecure()
    {
        $page = $this->getPage();

        if (null === $page) {
            $request = $this->requestStack->getCurrentRequest();

            if (null === $request) {
                return false;
            }

            return $request->isSecure();
        }

        return (bool) $page->loadDetails()->rootUseSSL;
    }

    /**
     * Gets the current page model.
     *
     * @return PageModel|null
     */
    private function getPage(): ?PageModel
    {
        if (isset($GLOBALS['objPage']) && $GLOBALS['objPage'] instanceof PageModel) {
            return $GLOBALS['objPage'];
        }

        return null;
    }

    /**
     * Gets field value from page model or global config.
     *
     * @param PageModel|null $page
     *
     * @return string
     */
    private function getFieldValue(?PageModel $page): string
    {
        if (null === $page) {
            /** @var Config $config */
            $config = $this->framework->createInstance(Config::class);

            return (string) $config->get($this->field);
        }

        return (string) $page->{$this->field};
    }
}

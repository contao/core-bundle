<?php

namespace Contao\CoreBundle\Routing;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Input;
use Contao\PageModel;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class InputEnhancer implements RouteEnhancerInterface
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var bool
     */
    private $prependLocale;

    /**
     * @var bool
     */
    private $useAutoItem;

    /**
     * @var Input
     */
    private $inputAdapter;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param bool                     $prependLocale
     * @param bool                     $useAutoItem
     */
    public function __construct(ContaoFrameworkInterface $framework, bool $prependLocale, bool $useAutoItem)
    {
        $this->framework = $framework;
        $this->prependLocale = $prependLocale;
        $this->useAutoItem = $useAutoItem;

        $this->inputAdapter = $this->framework->getAdapter(Input::class);
    }

    /**
     * {@inheritdoc}
     */
    public function enhance(array $defaults, Request $request)
    {
        if (!isset($defaults['pageModel']) || !$defaults['pageModel'] instanceof PageModel) {
            return $defaults;
        }

        $this->framework->initialize();

        if ($this->prependLocale) {
            $this->inputAdapter->setGet('language', $defaults['_locale']);
        }

        if (!isset($defaults['parameters'])) {
            return $defaults;
        }

        $fragments = explode('/', substr($defaults['parameters'], 1));

        // Add the second fragment as auto_item if the number of fragments is even
        if ($this->useAutoItem && 0 === \count($fragments) % 2) {
            array_insert($fragments, 1, array('auto_item'));
        }

        for ($i = 1, $c = \count($fragments); $i < $c; $i += 2) {
            // Skip key value pairs if the key is empty (see #4702)
            if ($fragments[$i] == '') {
                continue;
            }

            // Abort if there is a duplicate parameter (duplicate content) (see #4277)
            if (isset($_GET[$fragments[$i]])) {
                throw new ResourceNotFoundException('Duplicate parameter "'.$fragments[$i].'" in path.');
            }

            // Abort if the request contains an auto_item keyword (duplicate content) (see #4012)
            if ($this->useAutoItem && \in_array($fragments[$i], $GLOBALS['TL_AUTO_ITEM'], true)) {
                throw new ResourceNotFoundException('"'.$fragments[$i].'" is an auto_item keyword (duplicate content)');
            }

            $this->inputAdapter->setGet(urldecode($fragments[$i]), $fragments[$i + 1], true);
        }

        return $defaults;
    }
}

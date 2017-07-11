<?php

namespace Contao\CoreBundle\Picker;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class PickerFactory
{
    /**
     * @var FactoryInterface
     */
    private $menuFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var PickerProviderInterface[]
     */
    private $providers = [];

    /**
     * Constructor.
     *
     * @param FactoryInterface $menuFactory
     * @param RouterInterface  $router
     * @param RequestStack     $requestStack
     */
    public function __construct(FactoryInterface $menuFactory, RouterInterface $router, RequestStack $requestStack)
    {
        $this->menuFactory = $menuFactory;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * Adds picker providers to the factory.
     *
     * @param PickerProviderInterface $provider
     */
    public function addProvider(PickerProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * @param PickerConfig  $config
     *
     * @return PickerInterface|null
     */
    public function create(PickerConfig $config)
    {
        $providers = array_filter(
            $this->providers,
            function (PickerProviderInterface $provider) use ($config) {
                return $provider->supportsContext($config->getContext());
            }
        );

        if (empty($providers)) {
            return null;
        }

        return new Picker(
            $this->menuFactory,
            $providers,
            $config
        );
    }

    /**
     * @param string $payload
     *
     * @return PickerInterface|null
     */
    public function createFromPayload($payload)
    {
        $data = @json_decode(base64_decode($payload), true);

        if (null === $data) {
            return null;
        }

        return $this->create(PickerConfig::jsonUnserialize($data));
    }

    /**
     * Gets picker URL for given context and configuration.
     *
     * @param string $context
     * @param array  $extras
     * @param string $value
     *
     * @return string
     */
    public function getInitialUrl($context, array $extras = [], $value = '')
    {
        $supportsContext = array_reduce(
            $this->providers,
            function ($carry, PickerProviderInterface $provider) use ($context) {
                return true === $carry || $provider->supportsContext($context);
            },
            false
        );

        if (!$supportsContext) {
            return '';
        }

        $extrasString = base64_encode(json_encode($extras));

        return $this->router->generate(
            'contao_backend_picker',
            ['context' => $context, 'extras' => $extrasString, 'value' => $value]
        );
    }
}

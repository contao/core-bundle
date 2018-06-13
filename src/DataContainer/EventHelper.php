<?php

namespace Contao\CoreBundle\DataContainer;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;

class EventHelper
{
    /**
     * @var System
     */
    private $system;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->system = $framework->getAdapter(System::class);
    }

    /**
     * Triggers a callback whilte handling singleton and service classes.
     *
     * @param array|callable $callback
     * @param array          $arguments
     *
     * @return mixed
     */
    public function trigger($callback, array $arguments = [])
    {
        if (\is_array($callback)) {
            return call_user_func_array(
                [$this->system->importStatic($callback[0]), $callback[1]],
                $arguments
            );
        }

        if (\is_callable($callback)) {
            return call_user_func_array($callback, $arguments);
        }

        throw new \InvalidArgumentException(sprintf('Cannot trigger "%s"', var_export($callback, true)));
    }

    /**
     * Triggers an array of callback.
     *
     * @param array         $callbacks
     * @param array         $arguments
     * @param mixed|null    $returnArgument
     * @param callable|null $returnIf
     *
     * @return mixed
     */
    public function triggerAll(array $callbacks, array $arguments = [], $returnArgument = null, callable $returnIf = null)
    {
        $result = null;

        foreach ($callbacks as $callback) {
            $result = $this->trigger($callback, $arguments);

            if (null !== $returnIf && true === $returnIf($result)) {
                return $result;
            }

            if (null !== $returnArgument) {
                $arguments[$returnArgument] = $result;
            }
        }

        return $result;
    }
}

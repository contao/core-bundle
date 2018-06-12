<?php
declare(strict_types=1);

namespace Contao\CoreBundle\DataContainer\User;

class User
{
    /** @var array */
    private $elements;

    /**
     * Return all content elements as array
     *
     * @return array
     */
    public function onGetContentElements(): array
    {
        if (\is_array($this->elements)) {
            return $this->elements;
        }

        $this->elements = [];

        // Parse modules
        foreach ((array) $GLOBALS['BE_MOD'] as $modules) {
            foreach ((array) $modules as $moduleName => $moduleConfig) {

                // Skip modules without tl_content table
                if (!\in_array('tl_content', (array) $moduleConfig['tables'], true)) {
                    continue;
                }

                $moduleGroup = $GLOBALS['TL_LANG']['MOD'][$moduleName][0];

                // Parse elements
                foreach ((array) $GLOBALS['TL_CTE'] as $elementGroup => $elementItems) {
                    foreach ((array) $elementItems as $element => $class) {
                        $this->elements[$moduleGroup][$moduleName . '.' . $element] = sprintf(
                            '<span style="color:#b3b3b3">[%s]</span> %s',
                            $GLOBALS['TL_LANG']['CTE'][$elementGroup],
                            $GLOBALS['TL_LANG']['CTE'][$element][0]
                        );
                    }
                }
            }
        }

        return $this->elements;
    }
}

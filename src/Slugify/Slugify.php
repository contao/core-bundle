<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Slugify;

use Cocur\Slugify\Slugify as CocurSlugify;

/**
 * Provides the contao.slugify service.
 *
 * @author Yanick Witschi <https://github.com/Toflar>
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class Slugify implements SlugifyInterface
{
    /**
     * @var CocurSlugify
     */
    private $slugify;

    /**
     * @var array
     */
    private $rulesets = [
        'default',
        'azerbaijani',
        'burmese',
        'hindi',
        'georgian',
        'norwegian',
        'vietnamese',
        'ukrainian',
        'latvian',
        'finnish',
        'greek',
        'czech',
        'arabic',
        'turkish',
        'polish',
        'german',
        'russian',
    ];

    /**
     * @var array
     */
    private $mapper = [
        'ar' => 'arabic',
        'de_AT' => 'austrian',
        'az' => 'azerbaijani',
        'bg' => 'bulgarian',
        'my' => 'burmese',
        'hr' => 'croatian',
        'cs' => 'czech',
        'eo' => 'esperanto',
        'fi' => 'finnish',
        'ka' => 'georgian',
        'de' => 'german',
        'el' => 'greek',
        'hi' => 'hindi',
        'lv' => 'latvian',
        'no' => 'norwegian',
        'pl' => 'polish',
        'ru' => 'russian',
        'sv' => 'swedish',
        'tr' => 'turkish',
        'uk' => 'ukrainian',
        'vi' => 'vietnamese',
    ];

    /**
     * Constructor.
     *
     * @param CocurSlugify $slugify
     */
    public function __construct(CocurSlugify $slugify)
    {
        $this->slugify = $slugify;
    }

    /**
     * {@inheritdoc}
     */
    public function slugify($string, $language = null)
    {
        if (null === $language) {
            foreach ($this->rulesets as $ruleset) {
                $this->slugify->activateRuleSet($ruleset);
            }
        } else {
            $ruleset = $this->getRulesetForLanguage($language);

            if (null !== $ruleset) {
                $this->slugify->activateRuleSet($ruleset);
            }
        }

        return $this->slugify->slugify($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getRulesetForLanguage($language)
    {
        if (null === $language) {
            return null;
        }

        if (isset($this->mapper[$language])) {
            return $this->mapper[$language];
        }

        // Shorten e.g. de_DE to de
        $lang = explode('_', $language)[0];

        return isset($this->mapper[$lang]) ? $this->mapper[$lang] : null;
    }
}

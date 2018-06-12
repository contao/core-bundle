<?php
declare(strict_types=1);

namespace Contao\CoreBundle\DataContainer\Content;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Buttons
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Connection */
    private $connection;

    /** @var Access */
    private $access;

    /**
     * Content constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param Connection            $connection
     * @param Access                $access
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        Connection $connection,
        Access $access
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->connection   = $connection;
        $this->access       = $access;
    }


    /**
     * Handle the toggle visibility action.
     *
     * @param DataContainer $dc
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function onHandleToggleAction(DataContainer $dc): void
    {
        if (null !== Input::get('cid')) {
            $this->toggleVisibility(
                (int) Input::get('cid'),
                '1' === Input::get('state'),
                $dc
            );
            Controller::redirect(Controller::getReferer());
        }
    }

    /**
     * Toggle the visibility of an element
     *
     * @param int           $elementId
     * @param bool          $visible
     * @param DataContainer $dc
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function toggleVisibility(int $elementId, bool $visible, DataContainer $dc = null): void
    {
        if (null === $user = $this->getUser()) {
            return;
        }

        // Set the ID and action
        Input::setGet('id', $elementId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $elementId; // see #8043
        }

        // Trigger the onload callback
        //$this->triggerOnloadCallback($dc);

        // Check the field access
        if (!$user->hasAccess('tl_content::invisible', 'alexf')) {
            throw new AccessDeniedException("Not enough permissions to show/hide content element ID$elementId.");
        }

        // Set the current record
        if ($dc) {
            $row = $this->connection
                ->executeQuery('SELECT * FROM tl_content WHERE id=?', [$elementId])
                ->fetch(\PDO::FETCH_OBJ);

            if (false !== $row) {
                $dc->activeRecord = $row;
            }
        }

        $versions = new Versions('tl_content', $elementId);
        $versions->initialize();

        // Reverse the logic (elements have invisible=1)
        $visible = !$visible;

        // Trigger the save callback
        $this->triggerSaveCallback($visible, $dc);

        // Update the database
        $time = time();
        $this->connection
            ->executeQuery(
                "UPDATE tl_content SET tstamp=$time, invisible='" . ($visible ? '1' : '') . "' WHERE id=?",
                [$elementId]
            );
        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->invisible = ($visible ? '1' : '');
        }

        // trigger the onsubmit callback
        $this->triggerOnSubmitCallback($dc);

        $versions->create();
    }

//    /**
//     * @param DataContainer $dc
//     */
//    private function triggerOnloadCallback(DataContainer $dc): void
//    {
//        if (\is_array($GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'])) {
//            foreach ($GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'] as $callback) {
//                if (\is_array($callback)) {
//                    $objCallback = System::importStatic($callback[0]);
//                    $objCallback->{$callback[1]}($dc);
//                } elseif (\is_callable($callback)) {
//                    $callback($dc);
//                }
//            }
//        }
//    }

    /**
     * @param bool          $visible
     * @param DataContainer $dc
     */
    private function triggerSaveCallback(bool $visible, DataContainer $dc): void
    {
        if (\is_array($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_content']['fields']['invisible']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $objCallback = System::importStatic($callback[0]);
                    $visible     = $objCallback->{$callback[1]}($visible, $dc);
                } elseif (\is_callable($callback)) {
                    $visible = $callback($visible, $dc);
                }
            }
        }
    }

    /**
     * @param DataContainer $dc
     */
    private function triggerOnSubmitCallback(DataContainer $dc): void
    {
        if (\is_array($GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'] as $callback) {
                if (\is_array($callback)) {
                    $objectCallback = System::importStatic($callback[0]);
                    $objectCallback->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }
    }

    /**
     * Returns general button markup dependent on element access settings.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function onGetButtonMarkup(
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        if (null === $user = $this->getUser()) {
            return '';
        }

        // Hide button if element is not allowed for current user
        if (!$this->access->isAllowedElement($row['type'], $user)) {
            return '';
        }

        // Build button markup
        $href  .= '&amp;id=' . $row['id'];
        $image = Image::getHtml($icon, $label);
        return $this->getButtonMarkup($href, $title, $attributes, $image);
    }

    /**
     * Returns the "delete" button markup dependent on element access settings.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function onGetDeleteButtonMarkup(
        array $row,
        string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        if (null === $user = $this->getUser()) {
            return '';
        }

        // Hide button if element is not allowed for current user
        if (!$this->access->isAllowedElement($row['type'], $user)) {
            return '';
        }

        // Disable button if element is aliased
        if ($this->connection
                ->executeQuery("SELECT id FROM tl_content WHERE cteAlias=? AND type='alias'", [$row['id']])
                ->rowCount() > 0) {
            return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
        }

        // Build button markup
        $href  .= '&amp;id=' . $row['id'];
        $image = Image::getHtml($icon, $label);
        return $this->getButtonMarkup($href, $title, $attributes, $image);
    }

    /**
     * Return the "toggle visibility" button markup dependent on element access settings.
     * Execute toggle action if requested.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function onGetToggleButtonMarkup(
        array $row,
        ?string $href,
        string $label,
        string $title,
        string $icon,
        string $attributes
    ): string {
        if (null === $user = $this->getUser()) {
            return '';
        }

        // Hide button if element is not allowed for current user
        if (!$this->access->isAllowedElement($row['type'], $user)) {
            return '';
        }

        // Check if user has permissions to the visibility property
        if (!$user->hasAccess('tl_content::invisible', 'alexf')) {
            return '';
        }

        // Build button markup
        $href       .= '&amp;id=' . Input::get('id') . '&amp;cid=' . $row['id'] . '&amp;state=' . $row['invisible'];
        $attributes = 'data-tid="cid"' . $attributes;
        $image      = Image::getHtml(
            $row['invisible'] ? 'invisible.svg' : $icon,
            $label,
            'data-state="' . ($row['invisible'] ? 0 : 1) . '"'
        );

        return $this->getButtonMarkup($href, $title, $attributes, $image);
    }

    /**
     * @param string $href
     * @param string $title
     * @param string $attributes
     * @param string $image
     *
     * @return string
     */
    private function getButtonMarkup(string $href, string $title, string $attributes, string $image): string
    {
        return sprintf(
            '<a href="%s" title="%s"%s>%s</a> ',
            Backend::addToUrl($href),
            StringUtil::specialchars($title),
            $attributes,
            $image
        );
    }

    /**
     * @return BackendUser|null
     */
    private function getUser(): ?BackendUser
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return null;
        }

        return $token->getUser();
    }

    /**
     * @deprecated This is only a proxy function for legacy support.
     *             Calling it won't be supported in the future.
     *
     * @param                    $id
     * @param                    $visible
     * @param DataContainer|null $dc
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function onToggleVisibility($id, $visible, DataContainer $dc = null): void
    {
        $this->toggleVisibility((int) $id, $visible, $dc);
    }
}

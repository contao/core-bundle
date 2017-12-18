<?php

namespace Contao\CoreBundle\Security\Authentication;

use Contao\FrontendUser;
use Contao\PageModel;
use Contao\StringUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\ParameterBagUtils;

class FrontendAuthenticationSuccessHandler extends AuthenticationSuccessHandler
{
    /**
     * @var TokenInterface
     */
    private $user;

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $this->user = $token->getUser();

        return parent::onAuthenticationSuccess($request, $token);
    }

    /**
     * {@inheritdoc}
     */
    protected function determineTargetUrl(Request $request)
    {
        if (!$this->user instanceof FrontendUser) {
            return parent::determineTargetUrl($request);
        }

        if (ParameterBagUtils::getRequestParameterValue($request, 'redirectBack')
            && ($targetUrl = ParameterBagUtils::getRequestParameterValue($request, 'redirect'))
        ) {
            return $targetUrl;
        }

        /** @var PageModel $pageModelAdapter */
        $pageModelAdapter = $this->framework->getAdapter(PageModel::class);
        $groupPage = $pageModelAdapter->findFirstActiveByMemberGroups(StringUtil::deserialize($this->user->groups, true));

        if ($groupPage instanceof PageModel) {
            return $groupPage->getAbsoluteUrl();
        }

        return parent::determineTargetUrl($request);
    }
}

<?php

/**
 * Written by Jeroen, highly inspired by https://symfony.com/doc/current/security/guard_authentication.html.
 */

namespace Mautic\ApiBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class IpWhitelistAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * All hosts that are allowed to connect without authentication.
     */
    private $apiHosts;

    public function __construct(EntityManagerInterface $em,
                                CoreParametersHelper $coreParametersHelper)
    {
        $this->em       = $em;
        $this->apiHosts = $coreParametersHelper->getParameter('api_hosts');
    }

    public function getCredentials(Request $request)
    {
        $ip = $request->getClientIp();
        if ($this->apiHosts && array_key_exists($ip, $this->apiHosts)) {
            return ['userName' => $this->apiHosts[$ip]];
        }

        return null;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $userName = $credentials['userName'];

        if (null === $userName) {
            return;
        }

        return $this->em->getRepository(User::class)->findOneBy(['username' => $userName]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // return true to cause authentication success, as we only get a user
        // when the ip is valid
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            // you might translate this message
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}

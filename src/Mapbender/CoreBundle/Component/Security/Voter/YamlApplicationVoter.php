<?php

namespace Mapbender\CoreBundle\Component\Security\Voter;

use Mapbender\CoreBundle\Entity\Application;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class YamlApplicationVoter extends Voter
{
    /** @var AccessDecisionManagerInterface */
    protected $accessDecisionManager;

    public function __construct(AccessDecisionManagerInterface $accessDecisionManager)
    {
        $this->accessDecisionManager = $accessDecisionManager;
    }

    protected function supports($attribute, $subject)
    {
        // only vote for VIEW on Yaml-defined Application instances
        return $attribute === 'VIEW' && is_object($subject) && ($subject instanceof Application) && $subject->getSource() === Application::SOURCE_YAML;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case 'VIEW':
                if ($subject->isPublished()) {
                    return $this->voteViewPublished($subject, $token);
                } else {
                    return $this->voteViewUnpublished($subject, $token);
                }
            default:
                throw new \LogicException("Unsupported grant attribute " . print_r($attribute, true));
        }
    }

    /**
     * Decide on view grant for published Application.
     *
     * @param Application $subject guaranteed to be Yaml-based (see supports)
     * @param TokenInterface $token
     * @return bool true for grant, false for deny (cannot abstain here)
     */
    protected function voteViewPublished(Application $subject, TokenInterface $token)
    {
        $appRoles = $this->getApplicationRoles($subject);
        if ($token instanceof AnonymousToken) {
            return $subject->isPublished() || in_array('IS_AUTHENTICATED_ANONYMOUSLY', $appRoles);
        }
        foreach ($token->getRoles() as $tokenRole) {
            if (in_array($tokenRole->getRole(), $appRoles)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Decide on view grant for unpublished Application.
     *
     * @param Application $subject guaranteed to be Yaml-based (see supports)
     * @param TokenInterface $token
     * @return bool true for grant, false for deny (cannot abstain here)
     */
    protected function voteViewUnpublished(Application $subject, TokenInterface $token)
    {
        // Legacy quirks mode: forward to (nonsensical for static files) EDIT grant on OID (=user has grant to EDIT all Applications globally)
        // @todo: EDIT on statically defined applications should logically always deny
        $aclTarget = ObjectIdentity::fromDomainObject($subject);
        return $this->accessDecisionManager->decide($token, array('EDIT'), $aclTarget);
    }

    /**
     * Should return role identifier strings for given $application.
     * Override this for completely special sauce VIEW-grant logic
     *
     * @param Application $application guaranteed to be Yaml-based (see supports)
     * @return string[]
     */
    protected function getApplicationRoles(Application $application)
    {
        // @todo: get this (unpersistable) information out of the entity, into a separate container parameter map
        return $application->getYamlRoles() ?: array();
    }
}

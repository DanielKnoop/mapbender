<?php

namespace FOM\UserBundle;

use FOM\UserBundle\Component\Menu\SecurityMenu;
use FOM\UserBundle\DependencyInjection\Compiler\CollectAclClassesPass;
use FOM\UserBundle\DependencyInjection\Compiler\ForwardUserEntityClassPass;
use Mapbender\ManagerBundle\Component\Menu\RegisterMenuRoutesPass;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use FOM\UserBundle\DependencyInjection\Factory\SspiFactory;
use Mapbender\ManagerBundle\Component\ManagerBundle;

/**
 * FOMUserBundle - provides user management
 *
 * @author Christian Wygoda
 */
class FOMUserBundle extends ManagerBundle
{
    public function build(ContainerBuilder $container)
    {
        /** @var SecurityExtension $extension */
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SspiFactory());

        $configLocator = new FileLocator(__DIR__ . '/Resources/config');
        $xmlLoader = new XmlFileLoader($container, $configLocator);
        $xmlLoader->load('security.xml');
        $container->addResource(new FileResource($xmlLoader->getLocator()->locate('security.xml')));
        $xmlLoader->load('services.xml');
        $container->addResource(new FileResource($xmlLoader->getLocator()->locate('services.xml')));
        $xmlLoader->load('commands.xml');
        $container->addResource(new FileResource($xmlLoader->getLocator()->locate('commands.xml')));
        $xmlLoader->load('controllers.xml');
        $container->addResource(new FileResource($xmlLoader->getLocator()->locate('controllers.xml')));

        $this->addMenu($container);
        $container->addCompilerPass(new ForwardUserEntityClassPass('fom.user_entity', 'FOM\UserBundle\Entity\User'));
        $container->addCompilerPass(new CollectAclClassesPass('fom.user.acl_classes'));
    }

    protected function addMenu(ContainerBuilder $container)
    {
        $securityItem = SecurityMenu::create('mb.terms.security', 'fom_user_security_index')
            ->setWeight(100)
        ;
        $container->addCompilerPass(new RegisterMenuRoutesPass($securityItem));
    }

    public function getACLClasses()
    {
        return array(
            'Symfony\Component\Security\Acl\Domain\Acl' => 'fom.user.userbundle.classes.acls',
            'FOM\UserBundle\Entity\User' => 'fom.user.userbundle.classes.users',
            'FOM\UserBundle\Entity\Group' => 'fom.user.userbundle.classes.groups',
        );
    }
}

<?php

namespace PHPMaker2025\ucarsip;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\ResolveChildDefinitionsPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveReferencesToAliasesPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveTaggedIteratorArgumentPass;
use Symfony\Component\DependencyInjection\Compiler\DecoratorServicePass;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SecurityContainerFactory
{
    /**
     * Create Security container
     *
     * @param Psr\Container\ContainerInterface $c Container
     * @return Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function create(ContainerInterface $c)
    {
        $debug = Config('DEBUG');
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', $debug);
        $container->setParameter('kernel.secret', $c->get('encryption.key'));
        $container->setParameter('kernel.environment', IsProduction() ? 'production' : 'development');

        // Load services configuration
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__));
        $files = Config("SERVICES_CONFIG_FILES");
        array_walk($files, fn($file) => $loader->load($file)); // services.php

        // Add compiler passes first
        $registerListenersPass = (new RegisterListenersPass())->setHotPathEvents([
            KernelEvents::REQUEST,
            KernelEvents::RESPONSE,
            KernelEvents::FINISH_REQUEST,
        ]);
        $container->addCompilerPass($registerListenersPass, PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new ResolveTaggedIteratorArgumentPass());

        // Load security configuration
        $container->registerExtension(new SecurityExtension());

        // Use security bundle
        $bundle = new SecurityBundle();
        $bundle->build($container); // Note: Must be registered after RegisterListenersPass

        // Set only required passes for better performance, comment out for debugging
        $container->getCompilerPassConfig()->setOptimizationPasses([
            new ResolveChildDefinitionsPass(),
            new DecoratorServicePass(),
            new ResolveReferencesToAliasesPass(),
        ]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);

        // Load security configuration
        $loader->load('security.php');

        // Dispatch Security container compiling event
        DispatchEvent(new SecurityContainerCompilingEvent($container), SecurityContainerCompilingEvent::NAME);

        // Compile
        $container->compile();

        // Set services
        $container->set('user.profile', $c->get('user.profile'));
        $container->set('app.language', $c->get('app.language'));
        $container->set('app.security', $c->get('app.security'));
        $container->set('app.session', $c->get('app.session'));
        if ($debug) {
            $container->set('logger', $c->get('app.logger'));
        }

        // Dispatch Security container compiled event
        DispatchEvent(new SecurityContainerCompiledEvent($container), SecurityContainerCompiledEvent::NAME);
        return $container;
    }
}

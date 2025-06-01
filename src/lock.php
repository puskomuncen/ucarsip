<?php

namespace PHPMaker2025\ucarsip;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Lock\LockFactory;
// use Symfony\Component\Lock\Store\CombinedStore;
// use Symfony\Component\Lock\Strategy\ConsensusStrategy;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;

return static function (ContainerConfigurator $container) {
    $container->services()
        // ->set('lock.store.combined.abstract', CombinedStore::class)->abstract()
        //     ->args([abstract_arg('List of stores'), service('lock.strategy.majority')])

        // ->set('lock.strategy.majority', ConsensusStrategy::class)
        ->set('lock.factory.abstract', LockFactory::class)->abstract()
            ->args([abstract_arg('Store')])
            ->call('setLogger', [service('logger')->ignoreOnInvalid()])
            // ->tag('monolog.logger', ['channel' => 'lock'])
    ;
};

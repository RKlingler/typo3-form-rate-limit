<?php

use Brotkrueml\FormRateLimit\Command\CleanUpExpiredStorageEntriesCommand;
use Brotkrueml\FormRateLimit\EventListener\PreventLanguagePackDownload;
use Brotkrueml\FormRateLimit\Extension;
use Brotkrueml\FormRateLimit\RateLimiter\FormRateLimitFactory;
use Brotkrueml\FormRateLimit\RateLimiter\Storage\FileStorage;
use Brotkrueml\FormRateLimit\RateLimiter\Storage\FileStorageCleaner;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Core\Environment;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function(ContainerConfigurator $configurator) {
    $services = $configurator->services();
    $services
        ->defaults()
        ->autoconfigure()
        ->autowire()
        ->private();

    $services
        ->load('Brotkrueml\\FormRateLimit\\', '../Classes/*')
        ->exclude('../Classes/{Domain/Dto,Extension.php}');

    $storagePath = Environment::getVarPath() . '/' . Extension::KEY;

    $services->set(FileStorage::class)
        ->arg('$storagePath', $storagePath);

    $services->set(FileStorageCleaner::class)
        ->arg('$storagePath', $storagePath);

    $services->set(FormRateLimitFactory::class)
        ->arg('$storage', service(FileStorage::class));

    $services->set(CleanUpExpiredStorageEntriesCommand::class)
        ->tag('console.command', [
            'command' => 'formratelimit:cleanupexpiredstorageentries',
            'description' => 'Clean up expired storage entries of form_rate_limit extension',
        ]);

    $services->set(PreventLanguagePackDownload::class)
        ->tag('event.listener', [
            'identifier' => 'form-rate-limit/prevent-language-pack-download',
        ]);
};

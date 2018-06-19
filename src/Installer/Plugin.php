<?php
namespace Communiacs\Sw\Composer\Installer;


use Communiacs\Sw\Composer\Plugin\Config;
use Communiacs\Sw\Composer\Plugin\PluginImplementation;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Class Plugin
 * @package Communiacs\Sw\Composer\Installer
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var PluginImplementation
     */
    private $pluginImplementation;

    /**
     * @var array
     */
    private $handledEvents = [];

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => ['listen'],
            ScriptEvents::POST_AUTOLOAD_DUMP => ['listen']
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $io->write('Activating the plugin');

        $this->ensureComposerConstraints($io);
        $pluginConfig = Config::load($composer);
        $composer
            ->getInstallationManager()
            ->addInstaller(
                new CoreInstaller($io, $composer, $pluginConfig)
            );

        $composer->getEventDispatcher()->addSubscriber($this);
    }

    /**
     * Listens to Composer events.
     *
     * This method is very minimalist on purpose. We want to load the actual
     * implementation only after updating the Composer packages so that we get
     * the updated version (if available).
     *
     * @param Event $event The Composer event.
     */
    public function listen(Event $event)
    {
        if (!empty($this->handledEvents[$event->getName()])) {
            return;
        }
        $this->handledEvents[$event->getName()] = true;
        // Plugin has been uninstalled
        if (!file_exists(__FILE__) || !file_exists(dirname(__DIR__) . '/Plugin/PluginImplementation.php')) {
            return;
        }

        // Load the implementation only after updating Composer so that we get
        // the new version of the plugin when a new one was installed
        if (null === $this->pluginImplementation) {
            $this->pluginImplementation = new PluginImplementation($event);
        }

        switch ($event->getName()) {
            case ScriptEvents::PRE_AUTOLOAD_DUMP:
                $this->pluginImplementation->preAutoloadDump();
                break;
            case ScriptEvents::POST_AUTOLOAD_DUMP:
                $this->pluginImplementation->postAutoloadDump();
                break;
        }
    }

    /**
     * @param IOInterface $io
     */
    private function ensureComposerConstraints(IOInterface $io)
    {
        if (
        !interface_exists('Composer\\Installer\\BinaryPresenceInterface')
        ) {
            $io->writeError('');
            $io->writeError(sprintf('<error>Composer version (%s) you are using is too low. Please upgrade Composer to 1.2.0 or higher!</error>',
                Composer::VERSION));
            $io->writeError('<error>Shopware installers plugin will be disabled!</error>');
            throw new \RuntimeException('Shopware Installer disabled!', 1469105842);
        }
    }
}

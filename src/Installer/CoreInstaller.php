<?php
namespace Communiacs\Sw\Composer\Installer;

use Communiacs\Sw\Composer\Plugin\Config;
use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;

/**
 * Class CoreInstaller
 * @package Communiacs\Sw\Composer\Installer
 */
class CoreInstaller extends LibraryInstaller
{

    protected $installDir;

    public function __construct(
        IOInterface $io,
        Composer $composer,
        Config $pluginConfig = null
    ) {
        parent::__construct($io, $composer);

        $pluginConfig = $pluginConfig ?: Config::load($composer);
        $this->installDir = $this->filesystem->normalizePath($pluginConfig->get('web-dir'));
    }

    public function supports($packageType)
    {
        return ($packageType === 'shopware-core');
    }

    /**
     * Returns the installation path of a package
     *
     * @param PackageInterface $package
     * @return string path
     */
    public function getInstallPath(PackageInterface $package)
    {
        return $this->installDir;
    }

}
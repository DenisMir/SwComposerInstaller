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

    /**
     * Shopware installation directory
     *
     * @var string
     */
    protected $installDir;

    public function __construct(
        IOInterface $io,
        Composer $composer,
        Config $pluginConfig
    ) {
        parent::__construct($io, $composer, 'shopware-core');
        $this->installDir = $this->filesystem->normalizePath($pluginConfig->get('web-dir'));
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
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

    /**
     * Composer exclude directories
     *
     * @var array
     */
    protected $composerExcludes;

    public function __construct(
        IOInterface $io,
        Composer $composer,
        Config $pluginConfig
    ) {
        parent::__construct($io, $composer, 'shopware-core');
        $this->installDir = $this->filesystem->normalizePath($pluginConfig->get('web-dir'));
        $this->composerExcludes = $pluginConfig->get('exclude-from-composer');
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

    /**
     * Installs the code
     * @param PackageInterface $package
     */
    protected function installCode(PackageInterface $package)
    {
        $this->io->writeError('<info>Shopware Installer: Installing the code</info>', true, IOInterface::QUIET);

        $backupBaseDir = $this->installDir . '_backup';

        // create backup dir if not existing
        if(! file_exists($backupBaseDir)){
            mkdir($backupBaseDir);
        }

        // backup files
        foreach($this->composerExcludes as $file){
            $this->moveComposerExcludes($this->installDir . '/' . $file, $backupBaseDir . '/' . $file );
        }

        parent::installCode($package);


        // restore files
        foreach($this->composerExcludes as $file){
            $this->moveComposerExcludes($backupBaseDir . '/' . $file, $this->installDir . '/' . $file );
        }

        // remove backup dir
        $this->rrmdir($this->installDir . '_backup');
    }

    /**
     * Updates the code
     *
     * @param PackageInterface $initial
     * @param PackageInterface $target
     */
    protected function updateCode(PackageInterface $initial, PackageInterface $target)
    {
        $this->io->writeError('<info>Shopware Installer: Updating the code</info>', true, IOInterface::QUIET);

        $backupBaseDir = $this->installDir . '_backup';

        // create backup dir if not existing
        if(! file_exists($backupBaseDir)){
            mkdir($backupBaseDir);
        }

        // backup files
        foreach($this->composerExcludes as $file){
            $this->moveComposerExcludes($this->installDir . '/' . $file, $backupBaseDir . '/' . $file );
        }

        $this->io->writeError('<info>Shopware Installer: Updating - Backed up files</info>', true, IOInterface::QUIET);

        parent::updateCode($initial, $target);

        $this->io->writeError('<info>Shopware Installer: Updating - Files updated</info>', true, IOInterface::QUIET);

        // restore files
        foreach($this->composerExcludes as $file){
            $this->moveComposerExcludes($backupBaseDir . '/' . $file, $this->installDir . '/' . $file );
        }

        // remove backup dir
        $this->rrmdir($this->installDir . '_backup');

        $this->io->writeError('<info>Shopware Installer: Updating - Restored files</info>', true, IOInterface::QUIET);
    }

    /**
     * Moves composer excludes
     * from source to destination
     *
     * @param $source
     * @param $dest
     */
    protected function moveComposerExcludes($source, $dest) {
        if( is_file($source)){
            copy($source, $dest);
            unlink($source);
        } else if(is_dir($source)) {
            if(is_dir($dest)) {
                $this->rrmdir($dest);
            }
            mkdir($dest, 0777, true);
            $dir = new \DirectoryIterator($source);
            foreach($dir as $fileInfo){
                if(!$fileInfo->isDot()) {
                    // recursive call because of subdirs
                    $this->moveComposerExcludes($source . '/' . $fileInfo->getFilename(), $dest . '/' . $fileInfo->getFilename());
                }
            }
            $this->rrmdir($source);
        }
    }

    /**
     * Remove directory
     * @param $dir
     */
    protected function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        $this->rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }
}
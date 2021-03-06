<?php
declare(strict_types = 1);
/**
 * @copyright 2017 Hostnet B.V.
 */
namespace Hostnet\Component\CodeSniffer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Hostnet\Component\Path\Path;

/**
 * This small composer installer plugin hooks into the post-autoload-dump
 * and configures the Hostnet standard for PHPCS
 *
 * @author Stefan Lenselink <slenselink@hostnet.nl>
 */
class Installer implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var IOInterface
     **/
    private $io;

    /**
     * Activate this plugin by storing the bin_dir.
     *
     * @see \Composer\Plugin\PluginInterface::activate()
     * @param Composer    $composer the composer instance to pull the config of the bin-dir from.
     * @param IOInterface $io the io interface to write / read output input.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * The 'logical'-operation of this Installer.
     * PHPCS does not define constants for the config options,
     * doing so ourself would only lead to outdated intel.
     *
     * @see https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options
     */
    public function execute()
    {
        if ($this->io->isVerbose()) {
            $this->io->write('Configured phpcs to use Hostnet standard');
        }
        self::configure();
    }

    /**
     * Ugly Composer static call to hook in to the post-autoload-dump event.
     *
     * @return array holding the post-autoload-dump hook and function to execute.
     */
    public static function getSubscribedEvents()
    {
        return [ 'post-autoload-dump' => 'execute' ];
    }

    /**
     * Configuration for standalone use in a system wide installation senario
     */
    public static function configureAsRoot()
    {
        $vendor_dir = Path::VENDOR_DIR . '/hostnet/phpcs-tool/src/Hostnet';
        if (!file_exists($vendor_dir)) {
            self::configure();
            mkdir($vendor_dir, 0777, true);
            symlink(__DIR__ . '/../../Sniffs', $vendor_dir . '/Sniffs');
            copy(__DIR__ . '/../../ruleset.xml', $vendor_dir . '/ruleset.xml');
        }
    }

    /**
     * Configure the Hostnet code style
     */
    public static function configure()
    {
        $file = Path::VENDOR_DIR . '/squizlabs/php_codesniffer/CodeSniffer.conf';
        if (!file_exists($file)) {
            copy(__DIR__ . '/CodeSniffer.conf.php', $file);
        }
    }
}

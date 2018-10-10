<?php declare(strict_types=1);
/*
 * This file is part of the WP Starter package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WeCodeMore\WpStarter\Tests;

use Composer;
use WeCodeMore\WpStarter\Config;
use WeCodeMore\WpStarter\Util;
use WeCodeMore\WpStarter\WpCli;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::setUp();
    }

    /**
     * @return string
     */
    protected function fixturesPath(): string
    {
        return getenv('TESTS_FIXTURES_PATH');
    }

    /**
     * @return string
     */
    protected function packagePath(): string
    {
        return getenv('PACKAGE_PATH');
    }

    /**
     * @param array $extra
     * @param string $vendorDir
     * @param string $binDir
     * @return Config\Validator
     */
    protected function makeValidator(
        array $extra = [],
        string $vendorDir = __DIR__,
        string $binDir = __DIR__
    ): Config\Validator {

        $config = \Mockery::mock(Composer\Config::class);
        $config->shouldReceive('get')->with('vendor-dir')->andReturn($vendorDir);
        $config->shouldReceive('get')->with('bin-dir')->andReturn($binDir);
        $composer = \Mockery::mock(Composer\Composer::class);
        $composer->shouldReceive('getConfig')->andReturn($config);
        $composer->shouldReceive('getPackage->getExtra')->andReturn($extra);

        $filesystem = new Composer\Util\Filesystem();

        $paths = new Util\Paths($composer, $filesystem);

        return new Config\Validator($paths, $filesystem);
    }

    /**
     * @param mixed ...$objects
     * @return Util\Locator
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    protected function makeLocator(...$objects): Util\Locator
    {
        $reflection = new \ReflectionClass(Util\Locator::class);
        /** @var Util\Locator $locator */
        $locator = $reflection->newInstanceWithoutConstructor();

        static $supportedObjects;
        $supportedObjects or $supportedObjects = [
            Composer\Config::class,
            Composer\IO\IOInterface::class,
            Composer\Util\Filesystem::class,
            Composer\Util\RemoteFilesystem::class,
            Config\Config::class,
            Util\Filesystem::class,
            Util\Paths::class,
            Util\Io::class,
            Util\UrlDownloader::class,
            Util\FileBuilder::class,
            Util\OverwriteHelper::class,
            Util\Salter::class,
            Util\LanguageListFetcher::class,
            WpCli\PharInstaller::class,
        ];

        $closure = function (...$objects) use ($supportedObjects) {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->objects = [];
            foreach ($objects as $object) {
                foreach ($supportedObjects as $supportedObject) {
                    if (is_a($object, $supportedObject)) {
                        /** @noinspection PhpUndefinedFieldInspection */
                        $this->objects[$supportedObject] = $object;
                        break;
                    }
                }
            }
        };

        // phpcs:enable

        \Closure::bind($closure, $locator, Util\Locator::class)(...$objects);

        return $locator;
    }

    /**
     * @return Util\Paths
     */
    protected function makePaths(): Util\Paths
    {
        $root = $this->fixturesPath() . '/paths-root';

        $cwd = getcwd();
        chdir($root);

        $config = \Mockery::mock(Composer\Config::class);
        $config->shouldReceive('get')->with('vendor-dir')->andReturn("{$root}/vendor");
        $config->shouldReceive('get')->with('bin-dir')->andReturn("{$root}/vendor/bin");
        $composer = \Mockery::mock(Composer\Composer::class);
        $composer->shouldReceive('getConfig')->andReturn($config);
        $composer->shouldReceive('getPackage->getExtra')->andReturn(
            [
                'wordpress-install-dir' => 'public/wp',
                'wordpress-content-dir' => 'public/wp-content',
            ]
        );

        $paths = new Util\Paths($composer, new Composer\Util\Filesystem());

        chdir($cwd);

        return $paths;
    }
}

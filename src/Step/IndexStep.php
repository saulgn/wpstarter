<?php declare(strict_types=1);
/*
 * This file is part of the WP Starter package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WeCodeMore\WpStarter\Step;

use WeCodeMore\WpStarter\Config\Config;
use WeCodeMore\WpStarter\Util\Locator;
use WeCodeMore\WpStarter\Util\Paths;

/**
 * Steps that generates index.php in root folder.
 */
final class IndexStep implements FileCreationStepInterface, BlockingStep
{
    const NAME = 'build-index';

    /**
     * @var \WeCodeMore\WpStarter\Util\FileBuilder
     */
    private $builder;

    /**
     * @var \WeCodeMore\WpStarter\Util\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $error = '';

    /**
     * @param Locator $locator
     */
    public function __construct(Locator $locator)
    {
        $this->builder = $locator->fileBuilder();
        $this->filesystem = $locator->filesystem();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return self::NAME;
    }

    /**
     * @param Config $config
     * @param Paths $paths
     * @return bool
     */
    public function allowed(Config $config, Paths $paths): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    public function targetPath(Paths $paths): string
    {
        return $paths->wpParent('index.php');
    }

    /**
     * @param Config $config
     * @param Paths $paths
     * @return int
     */
    public function run(Config $config, Paths $paths): int
    {
        $from = $paths->wpParent();
        $to = $paths->wp('index.php');

        $indexPath = $this->filesystem->composerFilesystem()->findShortestPath($from, $to);

        $build = $this->builder->build($paths, 'index.php', ['BOOTSTRAP_PATH' => $indexPath]);

        if (!$this->filesystem->save($build, $this->targetPath($paths))) {
            $this->error = 'Error creating index.php.';

            return self::ERROR;
        }

        return self::SUCCESS;
    }

    /**
     * @return string
     */
    public function error(): string
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function success(): string
    {
        return '<comment>index.php</comment> saved successfully.';
    }
}

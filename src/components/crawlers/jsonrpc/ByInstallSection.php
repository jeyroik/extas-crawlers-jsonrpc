<?php
namespace extas\components\crawlers\jsonrpc;

use extas\components\crawlers\CrawlerDispatcher;
use extas\components\plugins\init\InitSection;
use extas\components\plugins\install\InstallSection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ByInstallSection
 *
 * @package extas\components\crawlers\jsonrpc
 * @author jeyroik@gmail.com
 */
class ByInstallSection extends CrawlerDispatcher
{
    public const NAME = 'by.install.section';
    public const OPTION__PATH = 'path-jsonrpc-install';
    public const OPTION__PREFIX = 'prefix-jsonrpc-install';

    /**
     * @return array
     */
    public function __invoke(): array
    {
        $path = $this->getPathValue();
        $prefix = $this->getPrefixValue();

        $finder = new Finder();
        $finder->name($prefix . '*.php');
        $plugins = [];

        foreach ($finder->in($path)->files() as $file) {
            /**
             * @var $file SplFileInfo
             */
            preg_match('/^namespace\s(.*?);$/m', $file->getContents(), $nsMatches);
            preg_match('/^class\s(.*?)\s/m', $file->getContents(), $classMatches);

            $plugin = $this->getPlugin($nsMatches, $classMatches);
            $this->filterPlugin($plugin, $plugins);
        }

        return $plugins;
    }

    /**
     * @return string
     */
    protected function getPrefixValue(): string
    {
        return $this->getInputOption(static::OPTION__PREFIX, 'Install');
    }

    /**
     * @return string
     */
    protected function getPathValue(): string
    {
        return $this->getInputOption(static::OPTION__PATH, getcwd());
    }

    /**
     * @param $plugin
     * @param array $plugins
     */
    protected function filterPlugin($plugin, array &$plugins): void
    {
        if ($plugin instanceof InstallSection || $plugin instanceof InitSection) {
            $plugins[] = $plugin;
        }
    }

    /**
     * @param $nsMatches
     * @param $classMatches
     * @return mixed|null
     */
    protected function getPlugin($nsMatches, $classMatches)
    {
        $plugin = null;

        try {
            if (isset($nsMatches[1], $classMatches[1])) {
                $className = $nsMatches[1] . '\\' . $classMatches[1];
                $plugin = new $className();
            }
        } catch (\Exception $e) {

        }

        return $plugin;
    }
}

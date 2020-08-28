<?php
namespace extas\components\crawlers\jsonrpc;

use extas\components\crawlers\Crawler;
use extas\components\crawlers\CrawlerDispatcher;
use extas\components\packages\CrawlerExtas;
use extas\interfaces\plugins\IPluginInstall;
use extas\interfaces\repositories\IRepository;
use extas\interfaces\samples\parameters\ISampleParameter;

/**
 * Class ByDynamicPlugins
 *
 * @package extas\components\crawlers\jsonrpc
 * @author jeyroik <jeyroik@gmail.com>
 */
class ByDynamicPlugins extends CrawlerDispatcher
{
    public const NAME = 'dynamic.plugins';
    public const OPTION__PATH = 'path-jsonrpc-dynamic-plugins';
    public const OPTION__PREFIX = 'prefix-jsonrpc-dynamic-plugins';

    public const FIELD__ENTITY_NAME = 'entity_name';
    public const FIELD__REPOSITORY = 'repository';
    public const FIELD__ITEM_CLASS = 'item_class';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function __invoke(): array
    {
        $serviceCrawler = new CrawlerExtas([
            CrawlerExtas::FIELD__INPUT => $this->getInput(),
            CrawlerExtas::FIELD__OUTPUT => $this->getOutput(),
            CrawlerExtas::FIELD__CRAWLER => new Crawler([
                Crawler::FIELD__PARAMETERS => [
                    'package_name' => [
                        ISampleParameter::FIELD__NAME => 'package_name',
                        ISampleParameter::FIELD__VALUE => $this->getPackageName()
                    ],
                    'run_after' => [
                        ISampleParameter::FIELD__NAME => 'run_after',
                        ISampleParameter::FIELD__VALUE => false
                    ]
                ]
            ]),
            CrawlerExtas::FIELD__PATH => getcwd()
        ]);
        $packages = $serviceCrawler();

        return $this->extractDynamicPlugins($packages);
    }

    /**
     * @return string
     */
    protected function getPackageName(): string
    {
        return $this->getInputOption(static::OPTION__PREFIX, 'extas') . '.json';
    }

    /**
     * @param array $packages
     * @return array
     * @throws \ReflectionException
     */
    protected function extractDynamicPlugins(array $packages): array
    {
        $pluginsList = [];

        foreach ($packages as $package) {
            if (isset($package['plugins_install'])) {
                $pluginsList = array_merge($pluginsList, $package['plugins_install']);
            }
        }

        return $this->collectItemData($pluginsList);
    }

    /**
     * @param array $plugins
     * @return array
     * @throws \ReflectionException
     */
    protected function collectItemData(array $plugins): array
    {
        $info = [];

        foreach ($plugins as $plugin) {
            $repoName = $plugin[IPluginInstall::FIELD__REPOSITORY] ?? '';
            if (!$repoName) {
                $this->writeLn(['Missed repository']);
                continue;
            }

            $repo = $this->$repoName();
            $params = $this->getDefaultProperties($repo);
            $info[] = [
                'item_class' => $params['itemClass'] ?? '',
                'entity_name' => $plugin['name'] ?? '',
                'repository' => $repo
            ];
        }

        return $info;
    }

    /**
     * @param IRepository $repository
     * @return array
     * @throws \ReflectionException
     */
    protected function getDefaultProperties(IRepository $repository): array
    {
        if (method_exists($repository, 'getDefaultProperties')) {
            return $repository->getDefaultProperties();
        }

        $repository = new \ReflectionClass($repository);

        return $repository->getDefaultProperties();
    }
}

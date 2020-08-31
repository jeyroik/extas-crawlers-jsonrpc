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
        $this->commentLn([
            '[crawler][by dynamic plugins] Found ' . count($packages) . ' packages',
            '[crawler][by dynamic plugins] Extracting details:',
        ]);

        $result = $this->extractDynamicPlugins($packages);
        $this->commentLn(['[crawler][by dynamic plugins] Prepared ' . count($result) . ' items']);

        return $result;
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
        $index = 0;
        foreach ($packages as $name => $package) {
            $index++;
            if (isset($package['plugins_install'])) {
                $pluginsList = array_merge($pluginsList, $package['plugins_install']);
                $this->commentLn([$index . '. [OK] Found "plugin_install" in the "' . $name . '"']);
            } else {
                $this->infoLn([$index . '. [X] Missed "plugins_install" in the "' . $name . '"']);
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
                $this->infoLn(['Missed repository']);
                continue;
            }

            $repo = $this->$repoName();
            $params = $this->getDefaultProperties($repo);
            $info[] = [
                'item_class' => $params['class'] ?? '',
                'entity_name' => $plugin['name'] ?? '',
                'repository' => $repoName
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

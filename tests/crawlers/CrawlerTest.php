<?php
namespace tests\crawlers;

use extas\components\console\TSnuffConsole;
use extas\components\crawlers\jsonrpc\ByDocComment;
use extas\components\crawlers\jsonrpc\ByDynamicPlugins;
use extas\components\crawlers\jsonrpc\ByInstallSection;

use extas\components\items\SnuffItem;
use extas\components\repositories\TSnuffRepositoryDynamic;
use tests\crawlers\misc\DocCommentNotADefaultPluginWith;
use tests\crawlers\misc\DocCommentOperationWith;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use tests\crawlers\misc\InstallTestSection;
use tests\crawlers\misc\Repository;

/**
 * Class CrawlerTest
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class CrawlerTest extends TestCase
{
    use TSnuffConsole;
    use TSnuffRepositoryDynamic;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
        $this->createSnuffDynamicRepositories([
            ['snuffRepository', 'name', SnuffItem::class]
        ]);
        $this->registerSnuffRepos([
            'propRepo' => Repository::class
        ]);
    }

    protected function tearDown(): void
    {
        $this->deleteSnuffDynamicRepositories();
    }

    public function testCrawlByInstallSection()
    {
        $crawler = new ByInstallSection([
            ByInstallSection::FIELD__INPUT => $this->getTestInput(),
            ByInstallSection::FIELD__OUTPUT => $this->getOutput()
        ]);
        $plugins = $crawler();
        $this->assertCount(1, $plugins);
        $plugin = array_shift($plugins);
        $this->assertEquals(get_class($plugin), InstallTestSection::class);

        $crawler = new ByInstallSection([
            ByInstallSection::FIELD__INPUT => $this->getTestInput(
                ByInstallSection::OPTION__PREFIX,
                ByInstallSection::OPTION__PATH,
                'PluginInstallMy'
            ),
            ByInstallSection::FIELD__OUTPUT => $this->getOutput()
        ]);
        $plugins = $crawler();
        $this->assertEmpty($plugins);
    }

    public function testCrawlByDocComment()
    {
        $crawler = new ByDocComment([
            ByDocComment::FIELD__INPUT => $this->getTestInput(
                ByDocComment::OPTION__DOC_PREFIX,
                ByDocComment::OPTION__DOC_PATH
            ),
            ByDocComment::FIELD__OUTPUT => $this->getOutput()
        ]);
        $operations = $crawler();
        $this->assertEmpty($operations, 'Found doc-comments operations in src');

        $crawler = new ByDocComment([
            ByDocComment::FIELD__INPUT => $this->getTestInput(
                ByDocComment::OPTION__DOC_PREFIX,
                ByDocComment::OPTION__DOC_PATH,
                'DocComment'
            ),
            ByDocComment::FIELD__OUTPUT => $this->getOutput()
        ]);

        $operations = $crawler();
        $this->assertCount(
            2,
            $operations,
            'Incorrect operations count found:' . print_r($operations, true)
        );
        $plugin = array_shift($operations);
        $foundMap = [DocCommentOperationWith::class, DocCommentNotADefaultPluginWith::class];
        $this->assertTrue(
            in_array(get_class($plugin), $foundMap),
            'Incorrect operation instance: ' . get_class($plugin)
        );
    }

    public function testByDynamicPlugins()
    {
        $crawler = new ByDynamicPlugins([
            ByDynamicPlugins::FIELD__INPUT => $this->getTestInput(
                ByDynamicPlugins::OPTION__PREFIX,
                ByDynamicPlugins::OPTION__PATH,
                'dyn.test'
            ),
            ByDynamicPlugins::FIELD__OUTPUT => $this->getOutput()
        ]);
        $operations = $crawler();
        $this->assertCount(
            2,
            $operations, 'Incorrect operations found: ' . print_r($operations, true)
        );
    }

    public function testByDynamicPluginsMissed()
    {
        $output = $this->getOutput(true);
        $crawler = new ByDynamicPlugins([
            ByDynamicPlugins::FIELD__INPUT => $this->getTestInput(
                ByDynamicPlugins::OPTION__PREFIX,
                ByDynamicPlugins::OPTION__PATH,
                'dyn.empty'
            ),
            ByDynamicPlugins::FIELD__OUTPUT => $output
        ]);
        $operations = $crawler();
        $outputText = $output->fetch();
        $this->assertStringContainsString(
            '[X] Missed "plugins_install" in the "test/empty"',
            $outputText,
            'Incorrect output: ' . $outputText
        );
    }

    /**
     *
     * @param string $prefixName
     * @param string $pathName
     * @param string $prefix
     * @return InputInterface
     */
    protected function getTestInput(
        string $prefixName = ByInstallSection::OPTION__PREFIX,
        string $pathName = ByInstallSection::OPTION__PATH,
        string $prefix = 'InstallTest'
    ): InputInterface
    {
        return $this->getInput([
            $pathName => getcwd() . '/tests',
            $prefixName => $prefix
        ]);
    }
}

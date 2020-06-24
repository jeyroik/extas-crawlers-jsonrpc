<?php
namespace tests\crawlers;

use extas\components\console\TSnuffConsole;
use extas\components\crawlers\jsonrpc\ByDocComment;
use extas\components\crawlers\jsonrpc\ByInstallSection;
use extas\components\plugins\init\InitGenerators;
use extas\components\plugins\install\InstallJsonRpcOperations;

use tests\DocCommentNotADefaultPluginWith;
use tests\DocCommentOperationWith;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class CrawlerTest
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class CrawlerTest extends TestCase
{
    use TSnuffConsole;

    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
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
        $this->assertTrue(in_array(
            get_class($plugin),
            [
                InstallJsonRpcOperations::class,
                InitGenerators::class
            ]
        ));

        $crawler = new ByInstallSection([
            ByInstallSection::FIELD__INPUT => $this->getTestInput(
                ByInstallSection::OPTION__PREFIX,
                ByInstallSection::OPTION__PATH,
                'PluginInstallMy',
                '/tests'
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
                'DocComment',
                '/tests'
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

    /**
     *
     * @param string $prefixName
     * @param string $pathName
     * @param string $prefix
     * @param string $path
     * @return InputInterface
     */
    protected function getTestInput(
        string $prefixName = ByInstallSection::OPTION__PREFIX,
        string $pathName = ByInstallSection::OPTION__PATH,
        string $prefix = 'InstallJson',
        string $path = '/src/components'
    ): InputInterface
    {
        return $this->getInput([
            $pathName => getcwd() . $path,
            $prefixName => $prefix
        ]);
    }
}

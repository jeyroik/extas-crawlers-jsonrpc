<?php
namespace tests\crawlers;

use extas\components\console\TSnuffConsole;
use extas\components\crawlers\jsonrpc\ByDocComment;
use extas\components\crawlers\jsonrpc\ByInstallSection;

use tests\DocCommentNotADefaultPluginWith;
use tests\DocCommentOperationWith;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use tests\InstallTestSection;

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

<?php
namespace tests\crawlers\misc;

use extas\components\crawlers\jsonrpc\ByDocComment;

/**
 * Class Repository
 *
 * @package tests\crawlers\misc
 * @author jeyroik <jeyroik@gmail.com>
 */
class Repository extends \extas\components\repositories\Repository
{
    /**
     * @return string[]
     */
    public function getDefaultProperties(): array
    {
        return [
            'itemClass' => ByDocComment::class,
            'name' => 'test'
        ];
    }
}

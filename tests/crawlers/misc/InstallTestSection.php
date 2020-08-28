<?php
namespace tests\crawlers\misc;

use extas\components\items\SnuffItem;
use extas\components\plugins\install\InstallSection;

/**
 * Class InstallTestSection
 *
 * @package tests
 * @author jeyroik@gmail.com
 */
class InstallTestSection extends InstallSection
{
    protected string $selfSection = 'tests';
    protected string $selfName = 'test';
    protected string $selfRepositoryClass = 'tests';
    protected string $selfUID = 'name';
    protected string $selfItemClass = SnuffItem::class;
}

<?php

declare(strict_types=1);
/**
 * This file is part of MoChat.
 * @link     https://mo.chat
 * @document https://mochat.wiki
 * @contact  group@mo.chat
 * @license  https://github.com/mochat-cloud/mochat/blob/master/LICENSE
 */
namespace HyperfTest\Utils;

use League\Flysystem\Filesystem;
use MoChat\App\Utils\FilesystemExt;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FilesystemExtTest extends TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var FilesystemExt|mixed
     */
    private $filesystemExt;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->filesystemExt = make(FilesystemExt::class);
        $this->filesystem = make(Filesystem::class);
        parent::__construct($name, $data, $dataName);
    }

    /**
     * 获取文件完整URL.
     * @throws \League\Flysystem\FileExistsException ...
     * @throws \League\Flysystem\FileNotFoundException ...
     */
    public function testGetFullUrl(): void
    {
        $path = sprintf('test/testFile_%s.png', time());

        ## 写入文件
        $file = 'https://www.baidu.com/img/flexible/logo/pc/result.png';
        $this->filesystem->write($path, file_get_contents($file));

        ## 获取完整URL
        $fullUrl = $this->filesystemExt->getFullUrl($path);
        if ($this->filesystemExt->getAdapterName() === 'local') {
            $root = $this->filesystemExt->getConfig()['root'];
            $fullUrl = str_replace(config('framework.app_domain', ''), $root, $fullUrl);
        }

        $res = fopen($fullUrl, 'rb');

        ## 删除文件
        $this->filesystem->delete($path);

        self::assertNotFalse($res, 'FilesystemExt::获取完整路径失败');
    }
}

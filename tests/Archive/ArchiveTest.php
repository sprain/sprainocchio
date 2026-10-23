<?php

namespace Sprainocchio\Tests\Archive;

use PHPUnit\Framework\TestCase;
use Sprainocchio\Archive\Archive;

class ArchiveTest extends TestCase
{
    public function testPaginatesPosts() : void
    {
        $archive = (new Archive(2))->setPosts(['a', 'b', 'c', 'd', 'e']);

        $this->assertSame(3, $archive->getNumberOfPages());
        $this->assertSame(['a', 'b'], $archive->getPostsOfPage(1));
        $this->assertSame(['e'], $archive->getPostsOfPage(3));
        $this->assertSame([], $archive->getPostsOfPage(4));
    }

    public function testHasNoPagesWithoutPosts() : void
    {
        $this->assertSame(0, (new Archive())->getNumberOfPages());
    }

    public function testBuildsDirectoryName() : void
    {
        $this->assertSame('', (new Archive())->getDirName());
        $this->assertSame('archive' . DIRECTORY_SEPARATOR, (new Archive())->setPath('archive')->getDirName());
        $this->assertSame(
            'tags' . DIRECTORY_SEPARATOR . 'ueber-uns',
            (new Archive())->setPath('tags')->setName('Über uns')->getDirName()
        );
    }
}

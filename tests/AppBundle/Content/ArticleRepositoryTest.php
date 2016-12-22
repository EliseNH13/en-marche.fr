<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Content\ArticleRepository;
use AppBundle\Content\Model\HomeItem;
use League\CommonMark\CommonMarkConverter;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class ArticleRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetValidHomeItems()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('home.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/home_valid.yml'));

        /** @var CommonMarkConverter $commonMarkMock */
        $commonMarkMock = $this->getMockBuilder(CommonMarkConverter::class)->getMock();

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, $commonMarkMock, new Logger('enmarche_tests', [$loggerHandler]));

        $items = $repository->getHomeItems();

        $this->assertCount(2, $items);
        $this->assertEmpty($loggerHandler->getRecords());

        $this->assertInstanceOf(HomeItem::class, $items['block1']);
        $this->assertFalse($items['block1']->hasVideoIcon());
        $this->assertEquals('link1', $items['block1']->getLink());
        $this->assertEquals('image1', $items['block1']->getImage());
        $this->assertInstanceOf(HomeItem::class, $items['block2']);
        $this->assertTrue($items['block2']->hasVideoIcon());
        $this->assertEquals('link2', $items['block2']->getLink());
        $this->assertEquals('image2', $items['block2']->getImage());
    }

    public function testGetInvalidYamlHomeItems()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('home.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/home_invalid_yaml.yml'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $this->assertEmpty($repository->getHomeItems());
        $this->assertTrue($loggerHandler->hasCriticalThatContains('Home configuration can not be read'));
    }

    public function testGetInvalidTypeHomeItems()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('home.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/home_invalid_type.yml'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $this->assertEmpty($repository->getHomeItems());
        $this->assertTrue($loggerHandler->hasCriticalThatContains('Home configuration can not be read'));
    }

    public function testGetInexistentHomeItems()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $this->assertEmpty($repository->getHomeItems());
        $this->assertTrue($loggerHandler->hasCriticalThatContains('Home configuration can not be read'));
    }

    public function testGetValidArticle()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('articles/slug-foo-bar/metadata.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_valid_metadata.yml'));
        $filesystem->write('articles/slug-foo-bar/content.md', file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_valid_content.md'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $article = $repository->getArticle('slug-foo-bar');

        $this->assertEmpty($loggerHandler->getRecords());
        $this->assertEquals('« Les outre-mer sont l’un des piliers de notre richesse culturelle. »', $article->getTitle());
        $this->assertEquals('Emmanuel Macron s’est rendu du 17 au 21 décembre 2016 en Guadeloupe, Martinique et Guyane.', $article->getDescription());
        $this->assertEquals(file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_valid_content_expected.html'), $article->getContent());
    }

    public function testGetInvalidMetadataArticle()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $filesystem->write('articles/slug-foo-bar/metadata.yml', file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_invalid_metadata.yml'));
        $filesystem->write('articles/slug-foo-bar/content.md', file_get_contents(__DIR__.'/../../Fixtures/filesystem/article_valid_content.md'));

        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $article = $repository->getArticle('slug-foo-bar');

        $this->assertFalse($article);
        $this->assertTrue($loggerHandler->hasCriticalThatContains('Article can not be parsed'));
    }

    public function testGetInexistentArticle()
    {
        $filesystem = new Filesystem(new MemoryAdapter());
        $loggerHandler = new TestHandler();

        $repository = new ArticleRepository($filesystem, new CommonMarkConverter(), new Logger('enmarche_tests', [$loggerHandler]));

        $article = $repository->getArticle('inexistent');

        $this->assertNull($article);
        $this->assertEmpty($loggerHandler->getRecords());
    }
}

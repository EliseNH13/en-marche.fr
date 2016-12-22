<?php

namespace AppBundle\Content;

use AppBundle\Content\Model\Article;
use AppBundle\Content\Model\HomeItem;
use League\CommonMark\CommonMarkConverter;
use League\Flysystem\Filesystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class ArticleRepository
{
    const PATH_HOME = 'home.yml';
    const PATH_ARTICLES = 'articles';
    const PATH_METADATA = 'metadata.yml';
    const PATH_CONTENT = 'content.md';

    private $filesystem;
    private $commonMarkConverter;
    private $logger;

    public function __construct(Filesystem $filesystem, CommonMarkConverter $commonMarkConverter, LoggerInterface $logger)
    {
        $this->filesystem = $filesystem;
        $this->commonMarkConverter = $commonMarkConverter;
        $this->logger = $logger;
    }

    /**
     * Parse the YAML home configuration file and return the home items.
     *
     * Returns an empty array if an error occured during YAML parsing.
     *
     * @return HomeItem[]
     */
    public function getHomeItems(): array
    {
        try {
            return $this->mapHomeItems(Yaml::parse($this->filesystem->read(self::PATH_HOME)));
        } catch (\Exception $exception) {
            $this->logger->critical('Home configuration can not be read', [
                'exception' => $exception,
            ]);

            return [];
        }
    }

    /**
     * Parse the YAML and Mardown for a given article slug.
     *
     * Returns null if the article does not exists.
     * Returns false if and error occured during the parsing.
     *
     * @param string $slug
     *
     * @return Article|null|false
     */
    public function getArticle($slug)
    {
        try {
            $metadataRaw = $this->filesystem->read(self::PATH_ARTICLES.'/'.$slug.'/'.self::PATH_METADATA);
            $contentRaw = $this->filesystem->read(self::PATH_ARTICLES.'/'.$slug.'/'.self::PATH_CONTENT);
        } catch (\Exception $exception) {
            return;
        }

        try {
            return $this->mapArticle(Yaml::parse($metadataRaw), $contentRaw);
        } catch (\Exception $exception) {
            $this->logger->critical('Article can not be parsed', [
                'slug' => $slug,
                'exception' => $exception,
            ]);

            return false;
        }
    }

    private function mapHomeItems(array $rawData)
    {
        $items = [];

        foreach ($rawData as $key => $rawItem) {
            $items[$key] = new HomeItem((bool) $rawItem['videoIcon'], (string) $rawItem['link'], (string) $rawItem['image']);
        }

        return $items;
    }

    private function mapArticle(array $metadataRaw, $contentRaw)
    {
        return new Article(
            isset($metadataRaw['title']) ? $metadataRaw['title'] : '',
            isset($metadataRaw['description']) ? $metadataRaw['description'] : '',
            isset($metadataRaw['date']) ? $metadataRaw['date'] : '',
            $this->commonMarkConverter->convertToHtml($contentRaw)
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Class\Interwebz;
use App\Entity\RedditChannel;
use App\Entity\RedditPost;
use App\Repository\RedditChannelRepository;
use App\Repository\RedditPostRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;

final class RedditReader
{
    public function __construct(
        private EntityManagerInterface $em,
        private RedditChannelRepository $channelRepository,
        private RedditPostRepository $postRepository,
    ) {
    }

    private function fetch(string $uri): SimpleXMLElement
    {
        return simplexml_load_file($uri);
    }

    private function getChannelUri(
        string $channelName,
        string $sorting = 'top',
        string $time = 'month'
    ): string {
        $uri = 'https://www.reddit.com/r/' . $channelName;
        $haveSorting = strlen($sorting) > 0;
        if ($haveSorting === true) {
            $uri .= '/' . $sorting;
        }
        $uri .= '.rss';
        if ($haveSorting === true && strlen($time) > 0) {
            $uri .= '?t=' . $time;
        }
        return $uri;
    }

    public function refreshAllChannels(): void
    {
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            $this->refreshChannelIfNeeded($channel);
        }
        $this->postRepository->cleanup();
    }

    public function getChannelDataForView(RedditChannel $channel)
    {
        $posts = $this->postRepository->findBy(
            criteria: [
                'channel' => $channel,
                'seen' => false,
            ],
            orderBy: [
                'published' => 'DESC'
            ],
        );
        return [
            'name' => $channel->getName(),
            'id' => $channel->getId(),
            'posts' => $posts,
        ];
    }

    private function refreshChannelIfNeeded(RedditChannel $channel): void
    {
        $now = new \DateTime('now');
        $hour = new DateInterval('PT1H');
        $hourAgo = clone ($now);
        $hourAgo->sub($hour);
        $lastFetched = $channel->getLastFetch();
        if ($lastFetched < $hourAgo) {
            foreach ($channel->getCoverage() as $time) {
                $uri = $this->getChannelUri(channelName: $channel->getName(), time: $time);
                $data = $this->fetch($uri);
                $castedData = Interwebz::simpleXmlToArray($data);
                if (array_key_exists(key: 'entry', array: $castedData)) {
                    $posts = $castedData['entry'];
                    foreach ($posts as $post) {
                        $uri = $post['link']['@attributes']['href'];
                        $persistedPost = $this->postRepository->findOneBy(['uri' => $uri, 'channel' => $channel]);
                        if (is_null($persistedPost)) {
                            $persistedPost = new RedditPost();
                            $persistedPost->setChannel($channel);
                            $persistedPost->setUri($uri);
                        }
                        $persistedPost->setTitle($post['title']);
                        $published = new DateTime($post['published']);
                        $persistedPost->setPublished($published);
                        $persistedPost->setTouched($now);
                        $this->em->persist($persistedPost);
                        $this->em->flush();
                    }
                }
            }
            $channel->setLastFetch($now);
            $this->em->persist($channel);
            $this->em->flush();
            $this->em->refresh($channel);
        }
    }
}

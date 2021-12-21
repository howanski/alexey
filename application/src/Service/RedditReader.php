<?php

declare(strict_types=1);

namespace App\Service;

use App\Class\Interwebz;
use App\Entity\RedditChannel;
use App\Entity\RedditPost;
use App\Message\AsyncJob;
use App\Repository\RedditChannelRepository;
use App\Repository\RedditPostRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;
use Symfony\Component\Messenger\MessageBusInterface;

final class RedditReader
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
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
        $this->postRepository->cleanup();
        $channels = $this->channelRepository->findAll();
        foreach ($channels as $channel) {
            $message = new AsyncJob(
                jobType: AsyncJob::TYPE_UPDATE_CRAWLER_CHANNEL,
                payload: ['id' => $channel->getId()],
            );
            $this->bus->dispatch($message);
        }
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

    public function refreshChannelById(int $id): void
    {
        $channel = $this->channelRepository->find($id);
        if ($channel instanceof RedditChannel) {
            $this->refreshChannelIfNeeded($channel);
        }
    }

    private function refreshChannelIfNeeded(RedditChannel $channel): void
    {
        $now = new \DateTime('now');
        $hour = new DateInterval('PT1H');
        $month = new DateInterval('P1M');
        $hourAgo = clone ($now);
        $monthAgo = clone ($now);
        $hourAgo->sub($hour);
        $monthAgo->sub($month);
        $lastFetched = $channel->getLastFetch();
        if ($lastFetched < $hourAgo) {
            if ($lastFetched < $monthAgo) {
                $coverage = ['year', 'month', 'week', 'all'];
            } else {
                $coverage = ['week'];
            }
            foreach ($coverage as $time) {
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
                        if (array_key_exists(key: 'author', array: $post)) {
                            if (array_key_exists(key: 'name', array: $post['author'])) {
                                $persistedPost->setUser(strval($post['author']['name']));
                            }
                        }

                        $published = new DateTime($post['published']);
                        $persistedPost->setPublished($published);
                        $persistedPost->setTouched($now);
                        $this->em->persist($persistedPost);
                        $this->em->flush();
                        if (
                            strlen($persistedPost->getThumbnail()) < 1
                            && $persistedPost->getSeen() === false
                        ) {
                            $message = new AsyncJob(
                                jobType: AsyncJob::TYPE_UPDATE_CRAWLER_POST,
                                payload: ['id' => $persistedPost->getId()],
                            );
                            $this->bus->dispatch($message);
                        }
                    }
                }
            }
            $channel->setLastFetch($now);
            $this->em->persist($channel);
            $this->em->flush();
        }
    }

    public function updatePostThumbnail(int $postId): void
    {
        $post = $this->postRepository->find($postId);
        if ($post instanceof RedditPost) {
            $uri = $post->getUri();
            $details = Interwebz::simpleXmlToArray($this->fetch($uri . '.rss'));
            if (array_key_exists(key: 'entry', array: $details)) {
                $details = $details['entry'];
                if (array_key_exists(key: 0, array: $details)) {
                    $details = $details[0];
                    if (array_key_exists(key: 'content', array: $details)) {
                        $details = $details['content'];
                        $post->setThumbnail($details);
                        $this->em->persist($post);
                        $this->em->flush();
                    }
                }
            }
        }
    }
}

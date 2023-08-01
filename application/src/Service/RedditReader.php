<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RedditChannel;
use App\Entity\RedditPost;
use App\Entity\User;
use App\Message\AsyncJob;
use App\Repository\RedditChannelRepository;
use App\Repository\RedditPostRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Rennokki\RedditApi\App;
use Rennokki\RedditApi\Reddit;
use Symfony\Component\Messenger\MessageBusInterface;

final class RedditReader
{
    public const REDDIT_USERNAME = 'REDDIT_USERNAME';
    private App $app;

    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        private RedditChannelRepository $channelRepository,
        private RedditPostRepository $postRepository,
        private SimpleSettingsService $simpleSettingsService,
    ) {
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

    public function getChannelDataForView(RedditChannel $channel, int $limit = 100)
    {
        $posts = $this->postRepository->getUnseen(
            channel: $channel,
            limit: $limit,
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
            $this->app = Reddit::app(
                'howanski/alexey',
                '2.0',
                'web',
                $this->simpleSettingsService->getSettings([self::REDDIT_USERNAME], $channel->getUser())[self::REDDIT_USERNAME],
            );

            $this->refreshChannelIfNeeded(channel: $channel);
        }
    }

    private function refreshChannelIfNeeded(RedditChannel $channel): void
    {
        $now = new \DateTime('now');
        $halfDay = new DateInterval('PT12H');
        $month = new DateInterval('P1M');

        $monthAgo = clone ($now);
        $halfDayAgo = clone ($now);

        $halfDayAgo->sub($halfDay);
        $monthAgo->sub($month);

        $lastFetched = $channel->getLastFetch();

        if ($lastFetched < $halfDayAgo) {
            if ($lastFetched < $monthAgo) {
                $coverage = ['year', 'month', 'week', 'all'];
            } else {
                $coverage = ['week'];
            }
            foreach ($coverage as $time) {
                $subreddit = Reddit::subreddit(
                    $channel->getName(),
                    $this->app
                );

                $subreddit
                    ->sort('top')
                    ->time($time);

                $posts = $subreddit->get();

                foreach ($posts as $post) {
                    $uri = 'https://old.reddit.com' . $post['permalink'];
                    $persistedPost = $this->postRepository->findOneBy(['uri' => $uri, 'channel' => $channel]);
                    if (is_null($persistedPost)) {
                        $persistedPost = new RedditPost();
                        $persistedPost->setChannel($channel);
                        $persistedPost->setUri($uri);
                    }
                    $persistedPost->setTitle($post['title']);

                    $userName = strval($post['author']);
                    if ($this->isUserBanned($userName, $channel->getUser())) {
                        continue;
                    }
                    $persistedPost->setUser($userName);
                    $published = date_create()->setTimestamp((int)$post['created_utc']);
                    $persistedPost->setPublished($published);
                    $persistedPost->setTouched($now);
                    $details = '';
                    if (array_key_exists(key: 'preview', array: $post)) {
                        if (array_key_exists(key: 'images', array: $post['preview'])) {
                            if (array_key_exists(key: 0, array: $post['preview']['images'])) {
                                if (array_key_exists(key: 'source', array: $post['preview']['images'][0])) {
                                    if (array_key_exists(key: 'url', array: $post['preview']['images'][0]['source'])) {
                                        $details = '<img style="max-width:100%;" src="' . $post['preview']['images'][0]['source']['url'] . '">';
                                    }
                                }
                            }
                        }
                    }
                    $persistedPost->setThumbnail($details);
                    $this->em->persist($persistedPost);
                    $this->em->flush();
                }
            }
            $channel->setLastFetch($now);
            $this->em->persist($channel);
            $this->em->flush();
        }
    }

    private function isUserBanned(string $userName, User $user): bool
    {
        // TODO: performance
        $userName = str_replace(search: '/u/', replace: '', subject: $userName);
        foreach ($user->getRedditBannedPosters() as $poster) {
            if ($poster->getUsername() === $userName) {
                return true;
            }
        }
        return false;
    }
}

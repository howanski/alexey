<?php

// TODO: split controller into smaller chunks & maybe it's time to start using voters?
declare(strict_types=1);

namespace App\Controller;

use App\Entity\RedditBannedPoster;
use App\Entity\RedditChannel;
use App\Entity\RedditChannelGroup;
use App\Entity\RedditPost;
use App\Form\RedditBannedUserType;
use App\Form\RedditChannelGroupType;
use App\Form\RedditChannelType;
use App\Message\AsyncJob;
use App\Repository\RedditChannelGroupRepository;
use App\Repository\RedditChannelRepository;
use App\Repository\RedditPostRepository;
use App\Service\AlexeyTranslator;
use App\Service\RedditReader;
use App\Service\SimpleSettingsService;
use DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/crawler')]
final class CrawlerController extends AlexeyAbstractController
{
    #[Route('/{filter}', name: 'crawler_index')]
    public function index(
        RedditReader $reader,
        RedditChannelRepository $repository,
        RedditChannelGroupRepository $groupRepository,
        string $filter = '*',
    ): Response {
        $user = $this->alexeyUser();
        $myRedditChannels = $repository->getMyChannels(user: $user, filter: $filter);
        $feeds = [];
        foreach ($myRedditChannels as $channel) {
            $feeds[] = $reader->getChannelDataForView($channel, 1);
        }
        $batchUnlinkOlderThan = new DateTime('now');
        return $this->render('crawler/index.html.twig', [
            'feeds' => $feeds,
            'filter' => $filter,
            'touchStamp' => $batchUnlinkOlderThan->getTimestamp(),
            'groups' => $groupRepository->getMine($user),
        ]);
    }

    #[Route('/reddit/post/dismiss/{id}', name: 'crawler_reddit_post_dismiss', methods: ['POST'])]
    public function dismiss(int $id)
    {
        $post = $this->fetchEntityById(className: RedditPost::class, id: $id);
        $user = $this->alexeyUser();
        if ($user === $post->getChannel()->getUser()) {
            $post->setSeen(true);
            $this->em->persist($post);
            $this->em->flush();
        }
        return new JsonResponse('ok');
    }

    #[Route(
        path: '/reddit/post/dismiss-channel/{id}/{touchStamp}',
        name: 'crawler_reddit_channel_dismiss',
        methods: ['POST'],
    )]
    public function dismissAll(int $id, int $touchStamp)
    {
        $channel = $this->fetchEntityById(className: RedditChannel::class, id: $id);
        $user = $this->alexeyUser();
        if ($user === $channel->getUser()) {
            $timeBorder = new DateTime();
            $timeBorder->setTimestamp($touchStamp);
            /** @var RedditPost $post */
            foreach ($channel->getPosts() as $post) {
                if ($post->getTouched() < $timeBorder) {
                    $post->setSeen(true);
                    $this->em->persist($post);
                }
            }
            $this->em->flush();
        }
        return new JsonResponse('ok');
    }

    #[Route('/reddit/channel/new', name: 'crawler_reddit_channel_new')]
    public function add(
        Request $request,
        MessageBusInterface $bus,
        AlexeyTranslator $translator,
    ) {
        $user = $this->alexeyUser();
        $channel = new RedditChannel();
        $channel->setUser($user);
        $form = $this->createForm(RedditChannelType::class, $channel, ['user' => $user, 'isNew' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($channel);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));
            $message = new AsyncJob(
                jobType: AsyncJob::TYPE_UPDATE_CRAWLER_CHANNEL,
                payload: ['id' => $channel->getId()],
            );
            $bus->dispatch($message);
            $filter = '*';
            if ($channel->getChannelGroup() instanceof RedditChannelGroup) {
                $filter = $channel->getChannelGroup()->getName();
            }
            return $this->redirectToRoute('crawler_index', ['filter' => $filter], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crawler/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reddit/channel/edit/{id}', name: 'crawler_reddit_channel_edit')]
    public function edit(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
    ) {
        $channel = $this->fetchEntityById(className: RedditChannel::class, id: $id);
        $user = $this->alexeyUser();
        if (false === ($user === $channel->getUser())) {
            return $this->redirectToRoute('crawler_index', ['filter' => '*'], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(RedditChannelType::class, $channel, ['user' => $user, 'isNew' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($channel);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute('crawler_index', ['filter' => '*'], Response::HTTP_SEE_OTHER);
        }

        $filter = '*';
        if ($channel->getChannelGroup() instanceof RedditChannelGroup) {
            $filter = $channel->getChannelGroup()->getName();
        }

        return $this->renderForm('crawler/edit.html.twig', [
            'form' => $form,
            'channel' => $channel,
            'activeFilter' => $filter,
        ]);
    }

    #[Route('/reddit/channel/drop/{id}/{filter}', name: 'crawler_reddit_channel_drop')]
    public function dropChannel(string $filter, int $id)
    {
        $channel = $this->fetchEntityById(className: RedditChannel::class, id: $id);
        $user = $this->alexeyUser();
        if ($user === $channel->getUser()) {
            $this->em->remove($channel);
            $this->em->flush();
        }

        return $this->redirectToRoute('crawler_index', ['filter' => $filter], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reddit/post/preview/{id}', name: 'crawler_reddit_post_preview')]
    public function thumbnail(int $id)
    {
        $post = $this->fetchEntityById(className: RedditPost::class, id: $id);
        $user = $this->alexeyUser();
        if ($user === $post->getChannel()->getUser()) {
            return $this->render('crawler/preview.html.twig', [
                'post' => $post,
            ]);
        } else {
            return $this->redirectToRoute('crawler_index', ['filter' => '*'], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/reddit/channel/table/{id}', name: 'crawler_reddit_channel_table')]
    public function channelTable(
        int $id,
        RedditReader $reader,
        Request $request,
        SimpleSettingsService $simpleSettingsService,
    ): Response {
        $limit = 30;
        $user = $this->alexeyUser();
        $channel = $this->fetchEntityById(className: RedditChannel::class, id: $id);
        if ($request->isXmlHttpRequest() && $user === $channel->getUser()) {
            $channelData = $reader->getChannelDataForView($channel, $limit);
            $autoHide = SimpleSettingsService::UNIVERSAL_TRUTH ===
                $simpleSettingsService->getSettings(
                    [RedditReader::REDDIT_EMPTY_STREAM_AUTOHIDE],
                    $user
                )[RedditReader::REDDIT_EMPTY_STREAM_AUTOHIDE];
            if ($channelData['posts']) {
                $render = $this->renderView(
                    view: 'crawler/channel_table.html.twig',
                    parameters: [
                        'feed' => $channelData,
                        'locale' => $request->getLocale(),
                        'have_more_posts' => (count($channelData['posts']) >= $limit),
                        'filter' => $channel->getChannelGroup() ?
                            $channel->getChannelGroup()->getName() :
                            '*',
                    ],
                );
            } elseif (false === $autoHide) {
                $render = $this->renderView(
                    view: 'table_body.html.twig',
                    parameters: [
                        'tableData' => []
                    ],
                );
            } else {
                $render = 'autoclose';
            }

            return new JsonResponse(data: $render);
        } else {
            return $this->redirectToRoute('crawler_index', ['filter' => '*'], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/reddit/channel/groups', name: 'crawler_reddit_channel_groups')]
    public function groupsList(RedditChannelGroupRepository $repo)
    {
        $user = $this->alexeyUser();
        $bannedUsers = $user->getRedditBannedPosters()->toArray();
        usort($bannedUsers, function (RedditBannedPoster $a, RedditBannedPoster $b) {
            return strtolower($a->getUsername()) <=> strtolower($b->getUsername());
        });
        return $this->render('crawler/groups_list.html.twig', [
            'groups' => $repo->getMine($user),
            'bannedUsers' => $bannedUsers,
        ]);
    }

    #[Route('/reddit/channel/groups/edit/{id}', name: 'crawler_reddit_channel_groups_edit')]
    public function groupsEdit(
        AlexeyTranslator $translator,
        int $id,
        Request $request,
    ) {
        $user = $this->alexeyUser();
        $group = $this->fetchEntityById(className: RedditChannelGroup::class, id: $id);
        if (false === ($user === $group->getUser())) {
            return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(RedditChannelGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($group);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crawler/groups_edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reddit/channel/groups/new', name: 'crawler_reddit_channel_groups_new')]
    public function groupsNew(
        AlexeyTranslator $translator,
        Request $request,
    ) {
        $user = $this->alexeyUser();
        $group = new RedditChannelGroup();

        $group->setUser($user);

        $form = $this->createForm(RedditChannelGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($group);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crawler/groups_edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reddit/channel/groups/delete/{id}', name: 'crawler_reddit_channel_groups_delete')]
    public function groupsDelete(
        AlexeyTranslator $translator,
        int $id,
    ) {
        $user = $this->alexeyUser();
        $group = $this->fetchEntityById(className: RedditChannelGroup::class, id: $id);
        if ($group->getUser() === $user) {
            /** @var RedditChannel $channel */
            foreach ($group->getChannels() as $channel) {
                $channel->setChannelGroup(null);
                $this->em->persist($channel);
            }
            $this->em->flush();
            $this->em->remove($group);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('deleted'));
        }

        return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reddit/channel/banned-users/new/{username}', name: 'crawler_reddit_banned_user_new')]
    public function bannedUserNew(
        AlexeyTranslator $translator,
        Request $request,
        string $username = null,
    ) {
        $user = $this->alexeyUser();
        $bannedPoster = new RedditBannedPoster();

        $bannedPoster->setUser($user);

        if (is_string($username)) {
            $bannedPoster->setUsername($username);
        }

        $form = $this->createForm(RedditBannedUserType::class, $bannedPoster);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($bannedPoster);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            /** @var RedditPostRepository $postRepo */
            $postRepo = $this->em->getRepository(RedditPost::class);
            $postRepo->dropBannedPosterPosts(user: $user, username: $bannedPoster->getUsername());

            return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crawler/banned_user_edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reddit/channel/banned-users/delete/{id}', name: 'crawler_reddit_banned_user_delete')]
    public function bannedUserDelete(
        AlexeyTranslator $translator,
        int $id,
    ) {
        $user = $this->alexeyUser();
        $poster = $this->fetchEntityById(className: RedditBannedPoster::class, id: $id);
        if ($poster->getUser() === $user) {
            $this->em->remove($poster);
            $this->em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('deleted'));
        }

        return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
    }
}

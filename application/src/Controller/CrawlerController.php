<?php

// TODO: split controller into smaller chunks & maybe it's time to start using voters?
declare(strict_types=1);

namespace App\Controller;

use App\Entity\RedditBannedPoster;
use App\Entity\RedditChannel;
use App\Entity\RedditChannelGroup;
use App\Entity\RedditPost;
use App\Entity\User;
use App\Form\RedditBannedUserType;
use App\Form\RedditChannelGroupType;
use App\Form\RedditChannelType;
use App\Message\AsyncJob;
use App\Repository\RedditChannelGroupRepository;
use App\Repository\RedditChannelRepository;
use App\Service\AlexeyTranslator;
use App\Service\RedditReader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/crawler')]
final class CrawlerController extends AbstractController
{
    #[Route('/{filter}', name: 'crawler_index')]
    public function index(
        RedditReader $reader,
        RedditChannelRepository $repository,
        RedditChannelGroupRepository $groupRepository,
        string $filter = '*',
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
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
    public function dismiss(RedditPost $post, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if ($user === $post->getChannel()->getUser()) {
            $post->setSeen(true);
            $em->persist($post);
            $em->flush();
        }
        return new JsonResponse('ok');
    }

    #[Route(
        path: '/reddit/post/dismiss-channel/{id}/{touchStamp}',
        name: 'crawler_reddit_channel_dismiss',
        methods: ['POST'],
    )]
    public function dismissAll(RedditChannel $channel, int $touchStamp, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if ($user === $channel->getUser()) {
            $timeBorder = new DateTime();
            $timeBorder->setTimestamp($touchStamp);
            /** @var RedditPost $post */
            foreach ($channel->getPosts() as $post) {
                if ($post->getTouched() < $timeBorder) {
                    $post->setSeen(true);
                    $em->persist($post);
                }
            }
            $em->flush();
        }
        return new JsonResponse('ok');
    }

    #[Route('/reddit/channel/new', name: 'crawler_reddit_channel_new')]
    public function add(
        Request $request,
        MessageBusInterface $bus,
        EntityManagerInterface $em,
        AlexeyTranslator $translator,
    ) {
        /** @var User $user */
        $user = $this->getUser();
        $channel = new RedditChannel();
        $channel->setUser($user);
        $form = $this->createForm(RedditChannelType::class, $channel, ['user' => $user, 'isNew' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($channel);
            $em->flush();
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
        Request $request,
        RedditChannel $channel,
        EntityManagerInterface $em,
        AlexeyTranslator $translator,
    ) {
        /** @var User $user */
        $user = $this->getUser();
        if (false === ($user === $channel->getUser())) {
            return $this->redirectToRoute('crawler_index', ['filter' => '*'], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(RedditChannelType::class, $channel, ['user' => $user, 'isNew' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($channel);
            $em->flush();
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
    public function dropChannel(RedditChannel $channel, string $filter, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if ($user === $channel->getUser()) {
            $em->remove($channel);
            $em->flush();
        }

        return $this->redirectToRoute('crawler_index', ['filter' => $filter], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reddit/post/preview/{id}', name: 'crawler_reddit_post_preview')]
    public function thumbnail(RedditPost $post)
    {
        $user = $this->getUser();
        if ($user === $post->getChannel()->getUser()) {
            return $this->render('crawler/preview.html.twig', [
                'post' => $post,
            ]);
        } else {
            return $this->redirectToRoute('crawler_index', ['filter' => '*'], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/reddit/channel/table/{id}', name: 'crawler_reddit_channel_table')]
    public function channelTable(RedditChannel $channel, RedditReader $reader, Request $request)
    {
        $limit = 30;
        $user = $this->getUser();
        if ($request->isXmlHttpRequest() && $user === $channel->getUser()) {
            $channelData = $reader->getChannelDataForView($channel, $limit);
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
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('crawler/groups_list.html.twig', [
            'groups' => $repo->getMine($user),
            'bannedUsers' => $user->getRedditBannedPosters(),
        ]);
    }

    #[Route('/reddit/channel/groups/edit/{id}', name: 'crawler_reddit_channel_groups_edit')]
    public function groupsEdit(
        RedditChannelGroup $group,
        AlexeyTranslator $translator,
        EntityManagerInterface $em,
        Request $request,
    ) {
        /** @var User $user */
        $user = $this->getUser();
        if (false === ($user === $group->getUser())) {
            return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(RedditChannelGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($group);
            $em->flush();
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
        EntityManagerInterface $em,
        Request $request,
    ) {
        /** @var User $user */
        $user = $this->getUser();
        $group = new RedditChannelGroup();

        $group->setUser($user);

        $form = $this->createForm(RedditChannelGroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($group);
            $em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crawler/groups_edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reddit/channel/groups/delete/{id}', name: 'crawler_reddit_channel_groups_delete')]
    public function groupsDelete(
        RedditChannelGroup $group,
        AlexeyTranslator $translator,
        EntityManagerInterface $em,
    ) {
        /** @var User $user */
        $user = $this->getUser();
        if ($group->getUser() === $user) {
            /** @var RedditChannel $channel */
            foreach ($group->getChannels() as $channel) {
                $channel->setChannelGroup(null);
                $em->persist($channel);
            }
            $em->flush();
            $em->remove($group);
            $em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('deleted'));
        }

        return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reddit/channel/banned-users/new/{username}', name: 'crawler_reddit_banned_user_new')]
    public function bannedUserNew(
        AlexeyTranslator $translator,
        EntityManagerInterface $em,
        Request $request,
        string $username = null,
    ) {
        /** @var User $user */
        $user = $this->getUser();
        $bannedPoster = new RedditBannedPoster();

        $bannedPoster->setUser($user);

        if (is_string($username)) {
            $bannedPoster->setUsername($username);
        }

        $form = $this->createForm(RedditBannedUserType::class, $bannedPoster);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($bannedPoster);
            $em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('saved'));

            return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crawler/banned_user_edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reddit/channel/banned-users/delete/{id}', name: 'crawler_reddit_banned_user_delete')]
    public function bannedUserDelete(
        RedditBannedPoster $poster,
        AlexeyTranslator $translator,
        EntityManagerInterface $em,
    ) {
        /** @var User $user */
        $user = $this->getUser();
        if ($poster->getUser() === $user) {
            $em->remove($poster);
            $em->flush();
            $this->addFlash(type: 'nord14', message: $translator->translateFlash('deleted'));
        }

        return $this->redirectToRoute('crawler_reddit_channel_groups', [], Response::HTTP_SEE_OTHER);
    }
}

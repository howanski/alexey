<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\RedditChannel;
use App\Entity\RedditPost;
use App\Entity\User;
use App\Form\RedditChannelType;
use App\Message\AsyncJob;
use App\Repository\RedditChannelRepository;
use App\Service\AlexeyTranslator;
use App\Service\RedditReader;
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
        string $filter = '*',
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $myRedditChannels = $repository->getMyChannels(user: $user, filter: $filter);
        $feeds = [];
        foreach ($myRedditChannels as $channel) {
            $feeds[] = $reader->getChannelDataForView($channel);
        }
        return $this->render('crawler/index.html.twig', [
            'feeds' => $feeds,
            'filter' => $filter,
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
        $form = $this->createForm(RedditChannelType::class, $channel);
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
            return $this->redirectToRoute('crawler_index', ['filter' => '*'], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('crawler/new.html.twig', [
            'form' => $form,
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
        $user = $this->getUser();
        if ($request->isXmlHttpRequest() && $user === $channel->getUser()) {
            $render = $this->renderView(
                view: 'crawler/channel_table.html.twig',
                parameters: [
                    'feed' => $reader->getChannelDataForView($channel),
                    'locale' => $request->getLocale(),
                ],
            );

            return new JsonResponse(data: $render);
        } else {
            return $this->redirectToRoute('crawler_index', ['filter' => '*'], Response::HTTP_SEE_OTHER);
        }
    }
}

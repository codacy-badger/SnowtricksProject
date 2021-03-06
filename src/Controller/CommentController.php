<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Snowtrick;
use App\Form\CommentType;
use App\Repository\SnowtrickRepository;
use App\Repository\CommentRepository;
use App\Repository\UserPictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    /**
     * @var SnowtrickRepository
     */
    private $snowtrickRepository;

    /**
     * @var CommentRepository
     */
    private $commentRepository;

    public function __construct(SnowtrickRepository $snowtrickRepository, CommentRepository $commentRepository, EntityManagerInterface $entityManager)
    {
        $this->snowtrickRepository = $snowtrickRepository;
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/new/{id}", name="comment.new"), methods={"GET","POST"})
     * @IsGranted ({"ROLE_ADMIN", "ROLE_MEMBER"})
     * @param Snowtrick
     */
    public function newAction(Security $security, Request $request, Snowtrick $snowtrick)
    {
        $newComment = new Comment;

        $roles = $security->getUser()->getRoles();

        if (in_array("ROLE_ADMIN", $roles)) {
            $newComment->setValidated(true);
        }
        
        $form = $this->createForm(CommentType::class, $newComment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isvalid()) {
            $newComment = $form->getData();
            $newComment->setAuthor($security->getUser());
            $newComment->setSnowtrick($snowtrick);

            $this->entityManager->persist($newComment);
            $this->entityManager->flush();

            return $this->render('loadmore/comment.html.twig', [
                'comment' => $newComment,
                'form' => $form->createView(),
            ]);
        }
        return $this->render('snowtricks/_form.html.twig', [
            'form' => $form->createView(),
            'path' => 'member.comment.new'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="comment.delete")
     * @IsGranted ({"ROLE_ADMIN", "ROLE_MEMBER"})
     * @param Comment
     * @param Request
     * @return Response
     */
    public function deleteCommentAction(Comment $comment, Request $request, Security $security)
    {

        $roles = $security->getUser()->getRoles();
        $username = $security->getUser()->getUsername();
        $token = json_decode($request->getContent(), true)['token']??null;
        if (in_array("ROLE_ADMIN", $roles) || ($comment->getAuthor()->getUsername() == $username)) {
            if ($this->isCsrfTokenValid('delete' . $comment->getId(), $token)) {
                $this->entityManager->remove($comment);
                $this->entityManager->flush();

                return new JsonResponse(array('message' => 'Deleted with success!'), 201); // 200 data //
            }
        }
        return new JsonResponse(null, 400);
    }

    /**
     * @Route("/loadmore", name="comment.loadmore")
     *
     * @param CommentRepository 
     * @param integer $page
     * @return Response
     */
    public function loadMoreAction(CommentRepository $commentRepository, Request $request): Response
    {
        $page = (int) $request->get('page', 1);
        if ($page < 1) {
            $page = 1;
        }

        return $this->render('loadmore/comments.html.twig', [
            'comments' => $commentRepository->findBy(['validated' => true] ,['id' => 'DESC'] ,3 ,($page-1) * 3)]);
    }
}
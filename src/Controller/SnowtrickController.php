<?php

namespace App\Controller;

use App\Entity\Snowtrick;
use App\Entity\Comment;
use App\Form\SnowtrickType;
use App\Repository\SnowtrickRepository;
use App\Repository\CommentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SnowtrickController extends AbstractController
{
    /**
     * @var SnowtrickRepository
     */
    private $repository;

    /**
     * @var CommentRepository
     */
    private $commentRepository;

    public function __construct(SnowtrickRepository $repository, CommentRepository $commentRepository, ObjectManager $em)
    {
        $this->repository = $repository;
        $this->commentRepository = $commentRepository;
        $this->em = $em;
    }

    /**
     * @route("/", name="snowtricks")
     * @return Response
     */
    public function indexAction(): Response
    {
        $snowtricks = $this->repository->findAllVisible();
        return $this->render("snowtricks/index.html.twig", [
            'current_menu' => 'snowtricks',
            'snowtricks' => $snowtricks
        ]);
    }

    /**
     * @Route("/member/snowtrick", name="member.snowtrick.index")
     * @return Response
     */
    public function indexMemberAction()
    {
        $snowtricks = $this->repository->findMyTricks();
        return $this->render('member/snowtricks/index.html.twig', compact("snowtricks"));
    }

    /**
     * @Route("/admin/snowtrick", name="admin.snowtrick.index")
     * @return Response
     */
    public function indexAdminAction()
    {
        $snowtricks = $this->repository->findAllVisibleDesc();
        $snowtricksToValidate = $this->repository->findAllInvisible();
        return $this->render('admin/snowtricks/index.html.twig', compact("snowtricks","snowtricksToValidate"));
    }

    /**
     * @Route("/snowtrick/{id}", name="snowtrick.show")
     * @param Snowtrick
     * @return Response
     */
    public function show(Snowtrick $snowtrick): Response
    {
        $comments = $this->commentRepository->findAllVisibleFromTrick($snowtrick->getId());
        return $this->render('snowtricks/show.html.twig', [
            'current_menu' => 'snowtricks',
            'snowtrick' => $snowtrick,
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/member/snowtrick/create", name="member.snowtrick.new")
     */
    public function newAction(Security $security, Request $request) 
    {
        $snowtrick = new snowtrick();

        $roles = $security->getUser()->getRoles();

        if(in_array("ROLE_ADMIN", $roles)) {
            $snowtrick->setValidated(true);
        } else {
            $snowtrick->setValidated(false);
        }

        $form = $this->createForm(SnowtrickType::class, $snowtrick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isvalid()) {
            $this->em->persist($snowtrick);
            $this->addFlash('success', 'Created with success!');
            $this->em->flush();
            return $this->redirectToRoute("member.snowtrick.index");
        }
        return $this->render('member/snowtricks/new.html.twig', [
            'snowtrick' => $snowtrick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/member/snowtrick/{id}", name="member.snowtrick.edit", methods={"GET","POST"})
     * @param Snowtrick
     * @param Request
     * @return Response
     */
    public function editAction(Security $security, Snowtrick $snowtrick, Request $request)
    {
        $roles = $security->getUser()->getRoles();

        if(in_array("ROLE_ADMIN", $roles)) {
            $snowtrick->setValidated(true);
        } else {
            $snowtrick->setValidated(false);
        }

        $form = $this->createForm(SnowtrickType::class, $snowtrick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isvalid()) {
            $this->em->flush();
            $this->addFlash('success', 'Edited with success!');
            if(in_array("ROLE_ADMIN", $roles)) {
                return $this->redirectToRoute("admin.snowtrick.index");
            } else {
                return $this->redirectToRoute("member.snowtrick.index");
            }
        }

        return $this->render('member/snowtricks/edit.html.twig', [
            'snowtrick' => $snowtrick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/member/snowtrick/{id}", name="member.snowtrick.delete", methods={"DELETE"})
     * @param Snowtrick
     * @param Request
     * @return Response
     */
    public function deleteAction(Snowtrick $snowtrick, Request $request, Security $security) {

        $roles = $security->getUser()->getRoles();
        $username = $security->getUser()->getUsername();

        if(in_array("ROLE_ADMIN", $roles) || in_array("ROLE_MEMBER", $roles) && $snowtrick->getAuthor() == $username ) {
            if($this->isCsrfTokenValid('delete' . $snowtrick->getId(), $request->get('_token'))) {
                $this->em->remove($snowtrick);
                $this->addFlash('success', 'Deleted with success!');
                $this->em->flush();
                return $this->redirectToRoute('admin.snowtrick.index');
            }
        } else {
            $this->addFlash('warning', 'You do not have the previlege to remove this post');
            return $this->redirectToRoute('member.snowtrick.index');
        }
    }
}
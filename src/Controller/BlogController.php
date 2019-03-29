<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\ChangeBlogStatusType;
use App\Repository\BlogRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Blog;
use App\Form\BlogType;
use App\Form\CommentType;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController
{
    /**
     * @Route("/", name="blog_index")
     */
    public function index(Request $request, BlogRepository $blog)
    {
        $repository = $this->getDoctrine()->getRepository(Blog::class);
//        $blogs = $repository->findBy(
////        ['status' => 'active'],
//          ['id'     => 'DESC']
//        );
        $blogs = $repository->findAll();
        return $this->render('blog/index.html.twig', [
            'blogs' => $blogs,
        ]);
    }
    /**
     * @Route("/blog/{id}", name="blog_show" , requirements={"id"="\d+"})
     */
    public function showBlog(Request $request, $id, CommentRepository $commentRepository)
    {

        $comment = new Comment();
        $blog = $this->getDoctrine()
            ->getRepository(Blog::class)
            ->find($id);
        if (!$blog) {
            throw $this->createNotFoundException(
                'No blog found with id: ' . $id
            );
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $blog->addComment($comment);
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            return $this->redirectToRoute('blog_show', ['id' => $blog->getId()]);
        }

        $comments = $commentRepository->findBy(['blog' => $blog]);

        $statusForm = $this->createForm(ChangeBlogStatusType::class, $blog);
        $statusForm->handleRequest($request);
        if($statusForm->isSubmitted() && $statusForm->isValid()){
            $blog->setStatus($statusForm->get('status')->getData());
            $em = $this->getDoctrine()->getManager();
            $em->persist($blog);
            $em->flush();
            $this->addFlash(
                'notice',
                'Status change successful!'
            );
            return $this->redirectToRoute('blog_show', ['id' => $blog->getId()]);

        }


        return $this->render('blog/show_blog.html.twig', [
            'blog'                 => $blog,
            'addCommentForm'       => $form->createView(),
            'changeBlogStatusForm' => $statusForm->createView(),
            'comments'             => $comments
        ]);
    }
    /**
     * @Route("/add", name="add_blog")
     * @return Response
     */
    public function addBlog(Request $request)
    {
        $blog = new Blog;
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $blog = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($blog);
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }
        return $this->render('blog/add_blog_form.html.twig', [
            'addBlogForm' => $form->createView(),
        ]);


    }

//
}

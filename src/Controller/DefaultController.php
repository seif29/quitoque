<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\UploadType;
use App\Service\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_home_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/upload", name="app_library_upload", methods={"GET", "POST"})
     */
    public function upload(Request $request, Parser $parser): Response
    {
        $form = $this->createForm(UploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('file')->getData();
            $books = $parser->csvParser($file);

            $parser->creatObjects($books);

            return $this->redirectToRoute('app_library_index');
        }

        return $this->render('default/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/library", name="app_library_index", methods={"GET"})
     */
    public function library(): Response
    {
        $em = $this->getDoctrine();

        $books = $em->getRepository(Book::class)->findAll();

        return $this->render('default/books.html.twig', [
            'books' => $books
        ]);
    }

    /**
     * @Route("/book/{id}", name="app_library_book", methods={"GET"})
     */
    public function details(Book $book): Response
    {
        return $this->render('default/book_details.html.twig', [
            'book' => $book
        ]);
    }

    /**
     * @Route("/author/{name}", name="app_library_author", methods={"GET"})
     */
    public function booksByAuthor(string $name): Response
    {
        $em = $this->getDoctrine();

        $books = $em->getRepository(Book::class)->findByCriteria(['author' => $name]);

        return $this->render('default/books.html.twig', [
            'books' => $books
        ]);
    }

    /**
     * @Route("/gender/{name}", name="app_library_gender", methods={"GET"})
     */
    public function booksByGender(string $name): Response
    {
        $em = $this->getDoctrine();

        $books = $em->getRepository(Book::class)->findByCriteria(['gender' => $name]);

        return $this->render('default/books.html.twig', [
            'books' => $books
        ]);
    }

    /**
     * @Route("/year/{year}", name="app_library_year", methods={"GET"})
     */
    public function booksByYear(string $year): Response
    {
        $em = $this->getDoctrine();

        $books = $em->getRepository(Book::class)->findByCriteria(['year' => $year]);

        return $this->render('default/books.html.twig', [
            'books' => $books
        ]);
    }

}

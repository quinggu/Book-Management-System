<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Form\BookSearchType;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Bundle\SecurityBundle\Security;

class BookController extends AbstractController
{
    #[Route('/books', name: 'app_book_index', methods: ['GET', 'POST'])]
    public function index(Request $request, BookRepository $bookRepository): Response
    {
        $form = $this->createForm(BookSearchType::class);
        $form->handleRequest($request);

        $query = $form->get('query')->getData();

        if ($query) {
            $books = $bookRepository->findBySearchTerm($query);
        } else {
            $books = $bookRepository->findAll();
        }

        return $this->render('book/index.html.twig', [
            'books' => $books,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/book/new', name: 'app_book_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('user_login');
        }

        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setUser($user);
            /** @var UploadedFile $photo */
            $photo = $form->get('photo')->getData();

            if ($photo) {
                $newFilename = uniqid().'.'.$photo->guessExtension();

                try {
                    $photo->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $this->resizeImage($this->getParameter('uploads_directory').'/'.$newFilename, 200, 200); // Przykład zmniejszenia do 200x200 px
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                    $this->addFlash('error', 'Failed to upload the file.');
                    return $this->render('book/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                // Update the 'photo' property to store the file name instead of its contents
                $book->setPhoto($newFilename);
            }

            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('user_books', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/book/{id}', name: 'app_book_show', methods: ['GET'])]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/book/{id}/edit', name: 'app_book_edit', methods: ['GET'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $photo */
            $photo = $form->get('photo')->getData();

            if ($photo) {
                $newFilename = uniqid().'.'.$photo->guessExtension();

                try {
                    $photo->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $this->resizeImage($this->getParameter('uploads_directory').'/'.$newFilename, 200, 200); // Przykład zmniejszenia do 200x200 px

                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                    $this->addFlash('error', 'Failed to upload the file.');
                    return $this->render('book/edit.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                // Update the 'photo' property to store the file name instead of its contents
                $book->setPhoto($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    private function resizeImage(string $filePath, int $width, int $height): void
    {
        $imagine = new Imagine();
        $image = $imagine->open($filePath);
        $image->resize(new Box($width, $height))
            ->save($filePath);
    }

    #[Route('/book/{id}', name: 'app_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_book_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/{id}/books', name: 'user_books')]
    public function userBooks(Request $request, User $user, BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findBy(['user' => $user]);

        return $this->render('user/books.html.twig', [
            'user' => $user,
            'books' => $books,
        ]);
    }
}

<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Gender;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;

class Parser
{
    const TITLE_INDEX  = 0;
    const AUTHOR_INDEX = 1;
    const YEAR_INDEX   = 2;
    const GENDER_INDEX = 3;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Parser constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * csvParser.
     *
     * @param File $file
     *
     * @return array
     */
    public function csvParser(File $file): array
    {
        $csv = [];
        $file = fopen($file->getPathname(), 'r');
        while (($result = fgetcsv($file)) !== false) {
            $csv[] = explode(';', implode(",", $result));
        }
        fclose($file);

        return $csv;
    }

    /**
     * creatObjects.
     *
     * @param array $data
     */
    public function creatObjects(array $data)
    {
        $gendersArrays = $this->entityManager->getRepository(Gender::class)->findAllAsArray();
        $gendersObjects = $this->entityManager->getRepository(Gender::class)->findAll();
        $authorsArrays = $this->entityManager->getRepository(Author::class)->findAllAsArray();
        $authorsObjects = $this->entityManager->getRepository(Author::class)->findAll();

        for ($i = 1; $i < count($data); $i++) {
            if (empty($this->entityManager->getRepository(Book::class)->findByCriteria([
                'title' => $data[$i][self::TITLE_INDEX],
                'author' => $data[$i][self::AUTHOR_INDEX],
                'year' => $data[$i][self::YEAR_INDEX],
                'gender' => $data[$i][self::GENDER_INDEX],
            ])) ) {
                $book = new Book();
                $book
                    ->setTitle($data[$i][self::TITLE_INDEX])
                    ->setYear($data[$i][self::YEAR_INDEX])
                    ;

                if (($genderKey = array_search($data[$i][self::GENDER_INDEX], array_column($gendersArrays, 'name'))) !== false) {
                    $book->setGender($gendersObjects[$genderKey]);
                } else {
                    $gender = new Gender();
                    $gender
                        ->setName($data[$i][self::GENDER_INDEX]);
                    $this->entityManager->persist($gender);

                    $book->setGender($gender);
                }

                if (($authorKey = array_search($data[$i][self::AUTHOR_INDEX], array_column($authorsArrays, 'name'))) !== false) {
                    $book->setAuthor($authorsObjects[$authorKey]);
                } else {
                    $author = new Author();
                    $author
                        ->setName($data[$i][self::AUTHOR_INDEX]);
                    $this->entityManager->persist($author);

                    $book->setAuthor($author);
                }

                $this->entityManager->persist($book);
            }

        }

        $this->entityManager->flush();
    }
}
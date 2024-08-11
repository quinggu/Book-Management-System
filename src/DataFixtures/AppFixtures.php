<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const BATCH_SIZE = 1000; // Number of records to process in each batch

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'user@example.com']);
        if (!$user) {
            $user = new User();
            $user->setEmail('admin@example.com');
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $manager->persist($user);
            $manager->flush();
        }

        $faker = Factory::create();
        for ($i = 0; $i < 1000000; $i++) {
            $book = new Book();
            $book
                ->setTitle($faker->sentence)
                ->setAuthor($faker->name)
                ->setDescription($faker->text)
                ->setYear($faker->year)
                ->setIsbn($faker->isbn13)
                ->setUser($user);

            $imageFiles = [
                'public/uploads/image1.jpg',
                'public/uploads/image2.jpg',
                'public/uploads/image3.jpg',
                'public/uploads/image4.jpg',
                'public/uploads/image5.jpg',
            ];

            $book->setPhoto(basename($imageFiles[array_rand($imageFiles)]));

            $manager->persist($book);

            if (($i % self::BATCH_SIZE) === 0) {
                $manager->flush();
                // Detach each book entity to free up memory
                foreach ($manager->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
                    $manager->detach($entity);
                }
            }
        }

        $manager->flush();
        $manager->clear();
    }
}
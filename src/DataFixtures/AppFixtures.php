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
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN']);

        // Hashowanie hasła
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'password123'
        );
        $user->setPassword($hashedPassword);

        $manager->persist($user);
        $manager->flush();

        $faker = Factory::create();
//        for ($i = 0; $i < 1000000; $i++) {
        for ($i = 0; $i < 10; $i++) {
            $book = new Book();
            $book->setTitle($faker->sentence);
            $book->setAuthor($faker->name);
            $book->setDescription($faker->text);
            $book->setYear($faker->year);
            $book->setIsbn($faker->isbn13);
            $book->setPhoto($faker->imageUrl);
            $book->setUser($user);

            $manager->persist($book);
        }
        $manager->flush();
    }
}
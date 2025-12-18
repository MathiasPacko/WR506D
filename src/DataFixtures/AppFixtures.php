<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Director;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Actor;
use App\Entity\Movie;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create test user
        $user = new User();
        $user->setEmail('test@test.com');
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'test');
        $user->setPassword($hashedPassword);
        $manager->persist($user);

        $faker = \Faker\Factory::create();
        $faker->addProvider(new \Xylis\FakerCinema\Provider\Person($faker));

        // Create Directors
        $directorsArray = [];
        $directorNames = $faker->actors($gender = null, $count = 50, $duplicates = false);
        foreach ($directorNames as $item) {
            $director = new Director();
            $fullname = $item;
            $names = explode(" ", $fullname);

            $director->setFirstName($names[0] ?? 'Unknown');
            $director->setLastName($names[1] ?? 'Director');

            $dob = $faker->dateTimeBetween('-90 years', '-30 years');
            $director->setDob($dob);

            if ($faker->boolean(20)) {
                $director->setDod(
                    $faker->dateTimeBetween($dob, 'now')
                );
            }
            $directorsArray[] = $director;
            $manager->persist($director);
        }

        // Create Actors
        $actorsArray = [];
        $actors = $faker->actors($gender = null, $count = 190, $duplicates = false);
        foreach ($actors as $item) {
            $actor = new Actor();
            $fullname = $item;
            $names = explode(" ", $fullname);

            $actor->setFirstName($names[0] ?? '');
            $actor->setLastName($names[1] ?? '');

            $actor->setBio($faker->paragraph(6, true));
            $dob = $faker->dateTimeThisCentury();
            $actor->setDob($dob);

            if ($faker->boolean(90)) {
                $actor->setDod(
                    $faker->dateTimeBetween($dob, 'now')
                );
            }
            $actorsArray[] = $actor;
            $manager->persist($actor);
        }


        $fakerMovie = \Faker\Factory::create();
        $fakerMovie->addProvider(new \Xylis\FakerCinema\Provider\Movie($fakerMovie));

        $categoriesArray = [];
        $movies = $fakerMovie->movies(199);

        foreach($movies as $item){
            $movie = new Movie();

            $movie->setName($item);
            $movie->setDescription($fakerMovie->overview);


            $durationMin = 60 * 60;   // 1h
            $durationMax = 270 * 60;  // 4h30
            $movie->setDuration($fakerMovie->numberBetween($durationMin, $durationMax));

            $categoryName = $fakerMovie->movieGenre;
            if (!array_key_exists($categoryName, $categoriesArray)) {
                $category = new Category();
                $category->setName($categoryName);
                $manager->persist($category);
                $categoriesArray[$categoryName] = $category;
            } else {
                $category = $categoriesArray[$categoryName];
            }

            shuffle($actorsArray);
            foreach(array_slice($actorsArray, 0, rand(2,6)) as $ActorObject){
                $movie->addActor($ActorObject);
            }
            $movie->addCategory($category);
            $movie->setOnline($fakerMovie->boolean(70));

            // Add new fields
            $movie->setNbEntries($fakerMovie->optional(0.8)->numberBetween(100000, 50000000));
            $movie->setBudget($fakerMovie->optional(0.9)->randomFloat(2, 100000, 300000000));
            $movie->setUrl($fakerMovie->optional(0.7)->url());

            // Assign a random director (80% chance)
            if (!empty($directorsArray) && $fakerMovie->boolean(80)) {
                $randomDirector = $directorsArray[array_rand($directorsArray)];
                $movie->setDirector($randomDirector);
            }

            $manager->persist($movie);
        }

        $manager->flush();
    }
}

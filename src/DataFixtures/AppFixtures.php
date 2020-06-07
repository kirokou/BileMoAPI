<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Product;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        /** PRODUCT */
        foreach ($this->getData() as [$name, $reference, $description]) 
        {
            $product = new Product();
            $product->setName($name)
                    ->setReference($reference)
                    ->setDescription($description)
                    ->setPrice($faker->randomFloat($nbMaxDecimals = 2, $min = 500, $max =1500))  
                    ->setQuantity($faker->numberBetween($min = 5, $max = 1000));

            $manager->persist($product);
        }

        /** CLIENT */
        for($k=0; $k<=3; $k++)
        {
            $client = new Client();
            $password = $this->passwordEncoder->encodePassword($client,'openclassrooms-P7');

            $client->setName($faker->company)
                    ->setEmail($faker->email)
                    ->setPassword($password)
                    ->setRoles(['ROLE_USER']);

            $manager->persist($client);

            /** USER */
            for($l=0; $l<=4; $l++)
            {
                $user = (new User());
                $user->setFirstname($faker->firstName)
                     ->setLastname($faker->lastName)
                     ->setEmail($faker->email)
                     ->setClient($client);

                $manager->persist($user);
            }
        }
        $manager->flush();
    }


    private function getData(): array
    {
        return 
        [
            [
                'Samsung Galaxy S7 32Go Noir',
                'S0S7_32N',
                'Samsung Galaxy S7. La solution de sécurité dédiée de Samsung offre une protection permanente contre les pirates et les programmes malveillants par le biais de mises à jour régulières. De plus, la S7 fournit une sécurité supplémentaire en isolant et en cryptant vos données sensibles.',
            ],
            [
                'Samsung Galaxy S8 32Go Rouge',
                'S0S7_32GG',
                'Samsung Galaxy S8. La solution de sécurité dédiée de Samsung offre une protection permanente contre les pirates et les programmes malveillants par le biais de mises à jour régulières. De plus, la S7 fournit une sécurité supplémentaire en isolant et en cryptant vos données sensibles.',
            ],
            [
                'IPHONE X 64Go Blanc',
                'IP00X_64B',
                'La solution de sécurité dédiée de Iphone X offre une protection permanente contre les pirates et les programmes malveillants par le biais de mises à jour régulières. De plus, la iphone X fournit une sécurité supplémentaire en isolant et en cryptant vos données sensibles.',
            ],
            [
                'IPHONE 7 64Go Noir',
                'S00001_32N',
                'La solution de sécurité dédiée de iphone 7 offre une protection permanente contre les pirates et les programmes malveillants par le biais de mises à jour régulières. De plus, la S7 fournit une sécurité supplémentaire en isolant et en cryptant vos données sensibles.',
            ],
            [
                'IPHONE X 32Go Blanc',
                'IP0X_32BLA',
                'Acheter un Samsung Galaxy S7 revient à s’offrir l’un des smartphones les plus performants du moment. L’accent a ici été mis sur le design, avec une finition exemplaire et un écran incurvé de haute volée. Les performances n’ont pour autant pas été oubliées par le fabricant : une très bonne qualité de photo, une autonomie sans précédent et une étanchéité de l’appareil vous permettant de braver sans soucis les intempéries',
            ],
            [
                'XIAOMY B5 32Go Bleu',
                'XI0B5_32BLE',
                'Acheter un Samsung Galaxy S7 revient à s’offrir l’un des smartphones les plus performants du moment. L’accent a ici été mis sur le design, avec une finition exemplaire et un écran incurvé de haute volée. Les performances n’ont pour autant pas été oubliées par le fabricant : une très bonne qualité de photo, une autonomie sans précédent et une étanchéité de l’appareil vous permettant de braver sans soucis les intempéries',
            ],
            [
                'IPHONE 8 32Go Blanc',
                'IP08_32BLA',
                'Samsung Galaxy S7. La solution de sécurité dédiée de Samsung offre une protection permanente contre les pirates et les programmes malveillants par le biais de mises à jour régulières. De plus, la S7 fournit une sécurité supplémentaire en isolant et en cryptant vos données sensibles.',
            ],
            [
                'IPHONE 11 32Go Blanc',
                'IP011_32N',
                'Samsung Galaxy S7. La solution de sécurité dédiée de Samsung offre une protection permanente contre les pirates et les programmes malveillants par le biais de mises à jour régulières. De plus, la S7 fournit une sécurité supplémentaire en isolant et en cryptant vos données sensibles.',
            ],
            [
                'XIAOMY N75 32Go Bleu',
                'XI0N75_32N',
                'Samsung Galaxy S7. La solution de sécurité dédiée de Samsung offre une protection permanente contre les pirates et les programmes malveillants par le biais de mises à jour régulières. De plus, la S7 fournit une sécurité supplémentaire en isolant et en cryptant vos données sensibles.',
            ],
            [
                'XIAOMY L15 32Go Bleu',
                'XI0L15_32N',
                'XIAOMY L15. La solution de sécurité dédiée de Xiaomy offre une protection permanente contre les pirates et les programmes malveillants par le biais de mises à jour régulières. De plus, la L15 fournit une sécurité supplémentaire en isolant et en cryptant vos données sensibles.',
            ],
            
        ];
    }

}

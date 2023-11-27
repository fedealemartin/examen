<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Provincia;
use App\Entity\Ciudad;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use function Symfony\Component\String\u;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $slugger;

    public function __construct(UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger)
    {
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadProvincias($manager);
        $this->loadCiudades($manager);
    }

    private function loadUsers(ObjectManager $manager): void
    {
        foreach ($this->getUserData() as [$fullname, $username, $password, $email, $roles]) {
            $user = new User();
            $user->setFullName($fullname);
            $user->setUsername($username);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setEmail($email);
            $user->setRoles($roles);

            $manager->persist($user);
            $this->addReference($username, $user);
        }

        $manager->flush();
    }


    private function loadProvincias(ObjectManager $manager): void
    {
        foreach ($this->getProvinciaData() as [$descripcion]) {
            $prov = new Provincia();
            $prov->setDescripcion($descripcion);
            $manager->persist($prov);
            $this->addReference('prov-'.$descripcion, $prov);
        }

        $manager->flush();
    }

    private function loadCiudades(ObjectManager $manager): void
    {
        foreach ($this->getCiudadData() as [$descripcion, $provincia]) {
            $ciud = new Ciudad();
            $ciud->setDescripcion($descripcion);

            $existingProvincia = $manager->getRepository(Provincia::class)->findOneBy(['descripcion' => $provincia]);
            $ciud->setProvincia($existingProvincia);
            $manager->persist($ciud);
            $this->addReference('ciud-'.$descripcion, $ciud);
        }

        $manager->flush();
    }

    private function getCiudadData(): array
    {
        return [
            ['Puerto Santa Cruz','Santa Cruz'],
            ['Puerto Deseado','Santa Cruz'],
            ['Rio Gallegos','Santa Cruz'],
            ['Puerto San Julian','Santa Cruz'],
            ['Villa La Angostura','Neuquen'],
            ['San Martín de los Andes','Neuquen'],
            ['Villa Traful','Neuquen'],
            ['Guaymallén','Mendoza'],
            ['Maipú','Mendoza'],
            ['San Rafael','Mendoza'],
        ];
    }

    private function getProvinciaData(): array
    {
        return [
            ['Santa Cruz'],
            ['Neuquen'],
            ['Mendoza'],
            ['Corrientes'],
            ['Buenos Aires'],
        ];
    }

    private function getUserData(): array
    {
        return [
            // $userData = [$fullname, $username, $password, $email, $roles];
            ['Fede', 'fede_admin', 'kitten', 'fede_admin@symfony.com', ['ROLE_ADMIN']],
            ['Pedro', 'pedro_admin', 'kitten', 'pedro_admin@symfony.com', ['ROLE_ADMIN']],
            ['Juan', 'juan_admin', 'kitten', 'juan_admin@symfony.com', ['ROLE_ADMIN']],
            ['Nico', 'nico_user', 'kitten', 'nico_user@symfony.com', ['ROLE_USER']],
        ];
    }

    private function getRandomText(int $maxLength = 255): string
    {
        $phrases = $this->getPhrases();
        shuffle($phrases);

        do {
            $text = u('. ')->join($phrases)->append('.');
            array_pop($phrases);
        } while ($text->length() > $maxLength);

        return $text;
    }




}

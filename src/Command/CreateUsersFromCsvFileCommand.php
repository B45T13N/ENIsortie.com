<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\CampusRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CreateUsersFromCsvFileCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository         $userRepository,
        CampusRepository $campusRepository,
        UserPasswordEncoderInterface $userPasswordEncoderInterface
    )
    {
        parent::__construct();
        $this->userPasswordEncoderInterface = $userPasswordEncoderInterface;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->campusRepository = $campusRepository;
    }

    protected static $defaultName = 'app:create-user-from-file';

    protected function configure(): void
    {
        $this->setDescription('Importer des données en provenance d\'un fichier CSV');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->createUsers();

        return Command::SUCCESS;
    }

    private function getDataFromFile(string $fichierCsv, string $directory): array
    {
        $file = $directory . '/'. $fichierCsv;

        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        $normalizers = [new ObjectNormalizer()];

        $encoder = [
            new CsvEncoder(),
            new XmlEncoder(),
            new YamlEncoder()
        ];

        $serializer = new Serializer($normalizers, $encoder);
        /**
         * @var string $fileString
         */
        $fileString = file_get_contents($file);

        $data = $serializer->decode($fileString, $fileExtension);

        if (array_key_exists('results', $data)) {
            return $data['results'];
        }
        return $data;
    }

    public function createUsers(string $fichierCsv,string $directory): bool
    {
        $boolean = true;

        foreach ($this->getDataFromFile($fichierCsv, $directory) as $row) {
            foreach ($row as $value){

                    $table2 = explode(';', $value);

                    $user = $this->userRepository->findOneBy([
                        'username'=>$table2[0]
                    ]);
                    if (!$user){
                    $campus = $this->campusRepository->findOneBy(['nom' => $table2[6]]);
                    $user = new User();
                    $user->setUsername($table2[0])
                        ->setPassword($this->userPasswordEncoderInterface->encodePassword($user,'password'))
                        ->setNom($table2[1])
                        ->setPrenom($table2[2])
                        ->setEmail($table2[3])
                        ->setTelephone($table2[4])
                        ->setActif(true)
                        ->setCampus($campus);
                    if($table2[5] == 'administrateur' or $table2[5] == 'admin') {
                        $user->setRoles(['ROLE_ADMIN'])
                            ->setAdmin(true);
                    } else{
                        $user->setRoles(['ROLE_USER'])
                            ->setAdmin(false);
                    }



                    $this->entityManager->persist($user);

                    $boolean = true;
                    } else{
                        $boolean = false;
                    }
        }
            $this->entityManager->flush();
        }
        return $boolean;
    }
}




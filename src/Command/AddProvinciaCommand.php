<?php

namespace App\Command;

use App\Entity\Provincia;
use App\Repository\ProvinciaRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\String\u;

/**
 * A console command that creates users and stores them in the database.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:add-provincia
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:add-provincia -vv
 *
 * See https://symfony.com/doc/current/console.html
 *
 * We use the default services.yaml configuration, so command classes are registered as services.
 * See https://symfony.com/doc/current/console/commands_as_services.html
 *
 */
class AddProvinciaCommand extends Command
{

  protected static $defaultName = 'app:add-provincia';

  private $io;

  private $entityManager;
  private $validator;
  private $provincias;

  public function __construct(EntityManagerInterface $em, Validator $validator, ProvinciaRepository $provincias)
  {
      parent::__construct();

      $this->entityManager = $em;
      $this->validator = $validator;
      $this->provincias = $provincias;
  }

    protected function configure(): void
    {

        $this
            ->setDescription('Creates provincias and stores them in the database')
            ->setHelp($this->getCommandHelp())
            // commands can optionally define arguments and/or options (mandatory and optional)
            // see https://symfony.com/doc/current/components/console/console_arguments.html
            ->addArgument('descripcion', InputArgument::OPTIONAL, 'Nombre de la provincia')
        ;
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }


    /**
     * This method is executed after initialize() and before execute(). Its purpose
     * is to check if some of the options/arguments are missing and interactively
     * ask the user for those values.
     *
     * This method is completely optional. If you are developing an internal console
     * command, you probably should not implement this method because it requires
     * quite a lot of work. However, if the command is meant to be used by external
     * users, this method is a nice way to fall back and prevent errors.
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('descripcion') ) {
            return;
        }

        $this->io->title('Add Provincia Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:add-provincia nombre',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);



        // Ask for the provincia if it's not defined
        $descripcion = $input->getArgument('descripcion');
        if (null !== $descripcion) {
            $this->io->text(' > <info>Descripcion</info>: '.$descripcion);
        } else {
            $descripcion = $this->io->ask('Descripcion', null, [$this->validator, 'validateProvincia']);
            $input->setArgument('descripcion', $descripcion);
        }


    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('add-provincia-command');

        $descripcion = $input->getArgument('descripcion');


        // make sure to validate the user data is correct
        $this->validateProvinciaData($descripcion);

        // create the user and hash its password
        $prov = new Provincia();
        $prov->setDescripcion($descripcion);


        $this->entityManager->persist($prov);
        $this->entityManager->flush();

        $this->io->success(sprintf('was successfully created'));

        $event = $stopwatch->stop('add-provincia-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return Command::SUCCESS;
    }

    private function validateProvinciaData($descripcion): void
    {
        // first check if a user with the same username already exists.
        $existingUser = $this->provincias->findOneBy(['descripcion' => $descripcion]);

        if (null !== $existingUser) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" provincia.', $username));
        }


    }

    /**
     * The command help is usually included in the configure() method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
The <info>%command.name%</info> command creates new users and saves them in the database:

  <info>php %command.full_name%</info> <comment>username password email</comment>

By default the command creates regular users. To create administrator users,
add the <comment>--admin</comment> option:

  <info>php %command.full_name%</info> username password email <comment>--admin</comment>

If you omit any of the three required arguments, the command will ask you to
provide the missing values:

  # command will ask you for the email
  <info>php %command.full_name%</info> <comment>username password</comment>

  # command will ask you for the email and password
  <info>php %command.full_name%</info> <comment>username</comment>

  # command will ask you for all arguments
  <info>php %command.full_name%</info>

HELP;
    }

}

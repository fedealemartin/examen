<?php

namespace App\Command;

use App\Entity\Provincia;
use App\Entity\Ciudad;
use App\Repository\CiudadRepository;
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
 *     $ php bin/console app:add-ciudad
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:add-ciudad -vv
 *
 * See https://symfony.com/doc/current/console.html
 *
 * We use the default services.yaml configuration, so command classes are registered as services.
 * See https://symfony.com/doc/current/console/commands_as_services.html
 *
 */
class AddCiudadCommand extends Command
{

  protected static $defaultName = 'app:add-ciudad';

  private $io;

  private $entityManager;
  private $validator;
  private $ciudades;

  public function __construct(EntityManagerInterface $em, Validator $validator, CiudadRepository $ciudades)
  {
      parent::__construct();

      $this->entityManager = $em;
      $this->validator = $validator;
      $this->ciudades = $ciudades;
  }

  protected function configure(): void
  {

      $this
          ->setDescription('Creates ciudades and stores them in the database')
          ->setHelp($this->getCommandHelp())
          // commands can optionally define arguments and/or options (mandatory and optional)
          // see https://symfony.com/doc/current/components/console/console_arguments.html
          ->addArgument('descripcion', InputArgument::OPTIONAL, 'Nombre de la Ciudad')
          ->addArgument('provincia', InputArgument::OPTIONAL, 'Nombre de la Provincia')
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
      if (null !== $input->getArgument('descripcion')&& null !== $input->getArgument('provincia') ) {
          return;
      }

      $this->io->title('Add Ciudad Command Interactive Wizard');
      $this->io->text([
          'If you prefer to not use this interactive wizard, provide the',
          'arguments required by this command as follows:',
          '',
          ' $ php bin/console app:add-ciudad nombre provincia',
          '',
          'Now we\'ll ask you for the value of all the missing command arguments.',
      ]);



      // Ask for the provincia if it's not defined
      $descripcion = $input->getArgument('descripcion');
      if (null !== $descripcion) {
          $this->io->text(' > <info>Ciudad</info>: '.$descripcion);
      } else {
          $descripcion = $this->io->ask('Ciudad', null, [$this->validator, 'validateCiudad']);
          $input->setArgument('descripcion', $descripcion);
      }

      // Ask for the provincia if it's not defined
      $provincia = $input->getArgument('provincia');
      if (null !== $provincia) {
          $this->io->text(' > <info>Provincia</info>: '.$provincia);
      } else {
          $provincia = $this->io->ask('Provincia', null, [$this->validator, 'validateCiudadProvincia']);
          $input->setArgument('provincia', $provincia);
      }


  }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
      $stopwatch = new Stopwatch();
      $stopwatch->start('add-ciudad-command');

      $descripcion = $input->getArgument('descripcion');
      $provincia = $input->getArgument('provincia');


      // make sure to validate the user data is correct
      $this->validateCiudadData($descripcion, $provincia);

      // create the user and hash its password
      $ciud = new Ciudad();
      $ciud->setDescripcion($descripcion);

      $existingProvincia = $this->entityManager->getRepository(Provincia::class)->findOneBy(['descripcion' => $provincia]);
      $ciud->setProvincia($existingProvincia);



      $this->entityManager->persist($ciud);
      $this->entityManager->flush();

      $this->io->success(sprintf('was successfully created'));

      $event = $stopwatch->stop('add-ciudad-command');
      if ($output->isVerbose()) {
          $this->io->comment(sprintf('New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
      }

      return Command::SUCCESS;
    }

    private function validateCiudadData($descripcion, $provincia): void
    {
        // first check if a user with the same username already exists.
        $existingCiudad = $this->ciudades->findOneBy(['descripcion' => $descripcion]);

        if (null !== $existingCiudad) {
            throw new RuntimeException(sprintf('There is already a ciudad registered with the "%s" nombre.', $descripcion));
        }

        $existingProvincia = $this->entityManager->getRepository(Provincia::class)->findOneBy(['descripcion' => $provincia]);

        if (null == $existingProvincia) {
            throw new RuntimeException(sprintf('No existe una proviincia con el nombre "%s" ', $provincia));
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

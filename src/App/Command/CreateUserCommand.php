<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Utils\Validator;

/**
 * A console command that creates users and stores them in the database.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:create-user <username> <email> <password> --admin
 *
 * use the --admin option only if you want the new user to have administration privileges.
 *
 */
class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';
    private $entityManager;
    private $passwordEncoder;
    private $validator;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, Validator $validator)
    {
        $this->entityManager = $em;
        $this->passwordEncoder = $encoder;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:create-user')
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the new user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the new user.')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'If set, the user is created as an administrator.')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');
        $isAdmin = $input->getOption('admin');

        // make sure to validate the user data
        $this->validateUserData($username, $plainPassword, $email);

        // create the user and encode its password
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles([$isAdmin ? 'ROLE_ADMIN' : 'ROLE_USER']);
        // See https://symfony.com/doc/current/book/security.html#security-encoding-password
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('The user has been created !');
    }

    private function validateUserData($username, $plainPassword, $email)
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        // first check if a user with the same username already exists.
        $existingUser = $userRepository->findOneBy(['username' => $username]);

        if (null !== $existingUser) {
            throw new \RuntimeException(sprintf('There is already a user registered with the "%s" username.', $username));
        }

        // validate password and email if is not this input means interactive.
        $this->validator->validatePassword($plainPassword);
        $this->validator->validateEmail($email);

        // check if a user with the same email already exists.
        $existingEmail = $userRepository->findOneBy(['email' => $email]);

        if (null !== $existingEmail) {
            throw new \RuntimeException(sprintf('There is already a user registered with the "%s" email.', $email));
        }
    }
}

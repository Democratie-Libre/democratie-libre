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
 * A console command that change the password of a registered user.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:change-user-password <username> <new-password>
 *
 */
class ChangeUserPasswordCommand extends Command
{
    protected static $defaultName = 'app:change-user-password';
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
            ->setName('app:change-user-password')
            // the short description shown while running "php bin/console list"
            ->setDescription('Change the password of a registered user')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to change the password of a registered user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username of the registered user.')
            ->addArgument('new-password', InputArgument::REQUIRED, 'New password')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username         = $input->getArgument('username');
        $newPlainPassword = $input->getArgument('new-password');

        $userRepository = $this->entityManager->getRepository(User::class);
        $user           = $userRepository->findOneBy(['username' => $username]);

        if (null == $user) {
            throw new \RuntimeException(sprintf('There is no user registered with the "%s" username.', $username));
        }

        // validate password if is not this input means interactive.
        $this->validator->validatePassword($newPlainPassword);

        // encode the new password
        // See https://symfony.com/doc/current/book/security.html#security-encoding-password
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $newPlainPassword);
        $user->setPassword($encodedPassword);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('The password of the user has been changed !');
    }
}

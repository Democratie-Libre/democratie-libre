<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Theme;

/**
 * A console command that removes safely a theme from the database.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:remove-theme <id>
 *
 */
class RemoveThemeCommand extends Command
{
    protected static $defaultName = 'app:remove-theme';
    private $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:remove-theme')
            // the short description shown while running "php bin/console list"
            ->setDescription('Remove safely a theme from the database.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command should be executed as bin/console app:remove-theme <id>')
            ->addArgument('id', InputArgument::REQUIRED, 'The id of the theme to remove from the database.')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
       $id = $input->getArgument('id');

       $em = $this->entityManager;

       $themeRepository = $em->getRepository(Theme::class);

       $theme = $themeRepository->findOneById($id);

       if (null === $theme) {
            throw new \RuntimeException(sprintf('There is no theme with this id in the database !'));
       }

       if (false === $theme->isEmpty()) {
            throw new \RuntimeException(sprintf('This theme is not empty. Remove the associated proposals before !'));
       }

        // we suppress all the discussions associated before removing the theme
        // we do it manually because Doctrine cascading does not work has we expect here
        $discussions = $theme->getDiscussions();

        foreach ($discussions as $discussion) {
            $em->remove($discussion);
        }

        $em->flush();

        $themeRepository->removeFromTree($theme);
        $em->clear(); // clear cached nodes
        // it will remove this node from tree and reparent all children

        $output->writeln('The theme has been removed !');
    }
}

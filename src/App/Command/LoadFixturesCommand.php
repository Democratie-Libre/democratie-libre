<?php

namespace App\Command;

use App\Entity\Article;
use App\Entity\Post;
use App\Entity\Proposal;
use App\Entity\PublicDiscussion;
use App\Entity\Theme;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Loads demo data for local development (never run it in production).
 *
 *     $ php bin/console app:fixtures:load
 *
 * Creates two users (admin / user, password "password"), a theme tree,
 * proposals with articles, and discussions with posts.
 * It refuses to run if the database already contains users or themes.
 */
#[AsCommand(name: 'app:fixtures:load', description: 'Loads demo data for local development')]
class LoadFixturesCommand extends Command
{
    private const PASSWORD = 'password';

    private const LOREM = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.";

    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->em->getRepository(User::class)->count([]) > 0 || $this->em->getRepository(Theme::class)->count([]) > 0) {
            $output->writeln('<error>The database is not empty, aborting.</error>');

            return Command::FAILURE;
        }

        $admin = $this->createUser('admin', 'admin@example.com', 'ROLE_ADMIN');
        $user  = $this->createUser('jeanne', 'jeanne@example.com', 'ROLE_USER');
        $this->em->flush();

        // Themes
        $roots = [];
        foreach ([
            'Institutions' => "Comment organiser les pouvoirs et les contre-pouvoirs.",
            'Économie et travail' => "Production, emploi, fiscalité et partage des richesses.",
            'Écologie' => "Protéger le vivant et préparer la transition énergétique.",
            'Éducation, culture et recherche scientifique' => "Transmettre, créer et comprendre le monde.",
            'Santé' => "Accès aux soins et prévention.",
        ] as $title => $abstract) {
            $roots[$title] = $this->createTheme($title, $abstract);
        }
        $this->em->flush();

        $institutions = $roots['Institutions'];
        $children = [
            $this->createTheme('Pouvoir législatif', "Qui écrit la loi et comment.", $institutions),
            $this->createTheme('Pouvoir judiciaire', "Indépendance et organisation de la justice.", $institutions),
            $this->createTheme('Collectivités territoriales et décentralisation', "Répartition des compétences entre l'État et les territoires.", $institutions),
        ];
        $this->em->flush();
        $this->createTheme('Initiative citoyenne', "Référendums et propositions de loi d'origine populaire.", $children[0]);
        $this->em->flush();

        // Proposals
        $constitution = $this->createProposal(
            'Constitution de la dL',
            $institutions,
            $admin,
            "Un texte fondateur pour la démocratie libre.",
            "Cette proposition pose les **principes** de base :\n\n- la souveraineté appartient au peuple\n- tout citoyen peut proposer et amender\n- les décisions sont publiques\n\n" . self::LOREM,
            [
                ['Souveraineté', "Le peuple est souverain et l'exerce directement ou par ses délégués révocables."],
                ['Droit de proposition', "Tout citoyen inscrit peut déposer une proposition de loi.\n\n" . self::LOREM],
                ['Transparence', "Toutes les délibérations sont publiques et archivées."],
            ]
        );

        $proposal = $this->createProposal(
            'Tirage au sort des membres des commissions parlementaires et des comités consultatifs',
            $children[0],
            $user,
            "Remplacer la désignation partisane par un tirage au sort encadré.",
            self::LOREM . "\n\n" . self::LOREM,
            [
                ['Composition', self::LOREM],
                ['Durée du mandat', "Le mandat dure six mois et n'est pas renouvelable."],
            ]
        );

        $this->createProposal(
            'Revenu de base',
            $roots['Économie et travail'],
            $user,
            "Garantir à chacun un socle de dignité.",
            self::LOREM,
            [['Montant', "Le montant est fixé chaque année par référendum."]]
        );
        $this->em->flush();

        $proposal->addSupporter($admin);
        $constitution->addSupporter($user);
        $constitution->addOpponent($admin);

        // Discussions
        $global = PublicDiscussion::createGlobalDiscussion();
        $global->setTitle('Bienvenue sur la démocratie libre !');
        $global->addFollower($user);
        $this->em->persist($global);

        $onTheme = PublicDiscussion::createThemeDiscussion($institutions);
        $onTheme->setTitle('Faut-il supprimer le Sénat ?');
        $this->em->persist($onTheme);

        $onProposal = PublicDiscussion::createProposalDiscussion($proposal);
        $onProposal->setTitle('Comment éviter les tirages biaisés ?');
        $this->em->persist($onProposal);
        $this->em->flush();

        foreach ([$global, $onTheme, $onProposal] as $discussion) {
            foreach ([[$user, "Bonjour à tous ! " . self::LOREM], [$admin, "Merci pour votre message.\n\n- premier point\n- second point"], [$user, "Une réponse courte."]] as [$author, $content]) {
                $post = new Post();
                $post->setContent($content)->setAuthor($author)->setDiscussion($discussion);
                $this->em->persist($post);
            }
        }
        $this->em->flush();

        $output->writeln('<info>Fixtures loaded.</info>');
        $output->writeln(sprintf('Users: admin / %1$s and jeanne / %1$s', self::PASSWORD));

        return Command::SUCCESS;
    }

    private function createUser(string $username, string $email, string $role): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles([$role]);
        $user->setPassword($this->hasher->hashPassword($user, self::PASSWORD));
        $this->em->persist($user);

        return $user;
    }

    private function createTheme(string $title, string $abstract, ?Theme $parent = null): Theme
    {
        $theme = new Theme();
        $theme->setTitle($title);
        $theme->setAbstract($abstract);
        if (null === $parent) {
            $theme->setLvl(0);
        } else {
            $theme->setParent($parent);
        }
        $this->em->persist($theme);

        return $theme;
    }

    /**
     * @param array<int, array{0: string, 1: string}> $articles
     */
    private function createProposal(string $title, Theme $theme, User $author, string $abstract, string $motivation, array $articles): Proposal
    {
        $proposal = new Proposal();
        $proposal->setTitle($title);
        $proposal->setAbstract($abstract);
        $proposal->setMotivation($motivation);
        $proposal->setAuthor($author)->setTheme($theme)->snapshot();
        $this->em->persist($proposal);

        foreach ($articles as [$articleTitle, $content]) {
            $article = new Article();
            $article->setTitle($articleTitle);
            $article->setContent($content);
            $article->setMotivation("Pourquoi cet article ? " . self::LOREM);
            $article->setProposal($proposal);
            $this->em->persist($article);
        }

        return $proposal;
    }
}

<?php

namespace Tests;

use App\Entity\Article;
use App\Entity\Post;
use App\Entity\PrivateDiscussion;
use App\Entity\Proposal;
use App\Entity\PublicDiscussion;
use App\Entity\Theme;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base class for functional tests.
 *
 * Every test starts from an empty database filled with the same small dataset
 * (see self::$data), so tests can be run in any order.
 */
abstract class AppTestCase extends WebTestCase
{
    /** @var Client */
    protected $client;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $em;

    /**
     * Entities of the dataset, indexed by a short name: admin, user, other,
     * theme, proposal, article, discussion, private_discussion...
     *
     * @var array
     */
    protected $data = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();

        $this->resetDatabase();
        $this->loadDataset();
    }

    protected function tearDown(): void
    {
        $this->em->close();
        $this->em = null;
        $this->client = null;

        parent::tearDown();
    }

    private function resetDatabase()
    {
        $connection = $this->em->getConnection();
        $connection->exec('SET FOREIGN_KEY_CHECKS = 0');

        foreach ($connection->getSchemaManager()->listTableNames() as $table) {
            if ('migration_versions' === $table) {
                continue;
            }

            $connection->exec(sprintf('DELETE FROM `%s`', $table));
            $connection->exec(sprintf('ALTER TABLE `%s` AUTO_INCREMENT = 1', $table));
        }

        $connection->exec('SET FOREIGN_KEY_CHECKS = 1');
        $this->em->clear();
    }

    private function loadDataset()
    {
        $admin = $this->createUser('admin', ['ROLE_USER', 'ROLE_ADMIN']);
        $user  = $this->createUser('user');
        $other = $this->createUser('other');

        $theme = (new Theme())->setTitle('Theme racine')->setAbstract('Un theme racine.');
        $this->em->persist($theme);

        $child = (new Theme())->setTitle('Theme enfant')->setAbstract('Un theme enfant.')->setParent($theme);
        $this->em->persist($child);
        $this->em->flush();

        $proposal = (new Proposal())
            ->setTitle('Proposition de test')
            ->setAbstract('Un resume.')
            ->setMotivation('Une motivation.')
            ->setTheme($theme)
            ->setAuthor($user)
        ;
        $this->em->persist($proposal);
        $this->em->flush();

        $article = (new Article())
            ->setTitle('Article de test')
            ->setContent('Contenu de l\'article.')
            ->setMotivation('Motivation de l\'article.')
            ->setProposal($proposal)
        ;
        $this->em->persist($article);
        $this->em->flush();

        $discussion = PublicDiscussion::createThemeDiscussion($theme)->setTitle('Discussion publique');
        $this->em->persist($discussion);

        $globalDiscussion = PublicDiscussion::createGlobalDiscussion()->setTitle('Discussion globale');
        $this->em->persist($globalDiscussion);
        $this->em->flush();

        $article->snapshot();
        $proposal->snapshot();

        $post = (new Post())->setContent('Un message.')->setAuthor($user)->setDiscussion($discussion);
        $this->em->persist($post);

        $privateDiscussion = PrivateDiscussion::create()->setTitle('Discussion privee')->setAdmin($user);
        $privateDiscussion->addMember($other);
        $this->em->persist($privateDiscussion);
        $this->em->flush();

        $this->data = [
            'admin'      => $admin,
            'user'       => $user,
            'other'      => $other,
            'theme'      => $theme,
            'child'      => $child,
            'proposal'   => $proposal,
            'article'    => $article,
            'discussion' => $discussion,
            'global_discussion' => $globalDiscussion,
            'post'       => $post,
            'private_discussion' => $privateDiscussion,
            'proposal_version' => $proposal->getVersioning()->first(),
            'article_version'  => $article->getVersioning()->first(),
        ];
    }

    protected function createUser($username, array $roles = ['ROLE_USER'])
    {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($username.'@example.com')
            ->setRoles($roles)
        ;
        $user->setPlainPassword($username);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    /**
     * Logs in through the real login form (CSRF token included).
     * The password of every dataset user is its username.
     */
    protected function logIn($username)
    {
        $crawler = $this->client->request('GET', '/login');
        $form    = $crawler->selectButton('Connexion')->form([
            '_username' => $username,
            '_password' => $username,
        ]);
        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse()->isRedirect(),
            sprintf('Login as "%s" failed.', $username)
        );
    }

    /**
     * Opens $path, fills the form holding the "save" button with $values
     * (keyed by short field name, e.g. 'title') and submits it.
     *
     * @return \Symfony\Component\DomCrawler\Crawler The crawler of the page
     *                                                  that was displayed before submitting
     */
    protected function submitForm($path, array $values = [])
    {
        $crawler = $this->client->request('GET', $path);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode(), 'Cannot display the form at '.$path);

        $button = $crawler->filter('button[name$="[save]"]');
        $this->assertCount(1, $button, 'Expected exactly one save button at '.$path);

        $prefix = substr($button->attr('name'), 0, -strlen('[save]'));
        $fields = [];
        foreach ($values as $name => $value) {
            $fields[$prefix.'['.$name.']'] = $value;
        }

        $this->client->submit($button->form($fields));

        return $crawler;
    }

    protected function assertRedirectsTo($path)
    {
        $response = $this->client->getResponse();
        $this->assertTrue($response->isRedirect(), 'Expected a redirection, got '.$response->getStatusCode());
        $this->assertSame($path, parse_url($response->headers->get('Location'), PHP_URL_PATH));
    }

    protected function refresh($entity)
    {
        $id = $this->idOf($entity);
        $this->em->clear();

        return $this->em->find(get_class($entity), $id);
    }

    protected function idOf($entity)
    {
        return $this->em->getUnitOfWork()->getSingleIdentifierValue($entity);
    }
}

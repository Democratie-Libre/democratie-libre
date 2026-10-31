<?php

namespace Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AppTestCase;

/**
 * Characterization test: records the HTTP status of every GET route for every
 * kind of visitor, so that a framework upgrade cannot silently change the
 * behavior (including the access control).
 *
 * To regenerate the snapshot after an intended change:
 *     UPDATE_SNAPSHOT=1 bin/phpunit tests/Functional/RoutesTest.php
 */
class RoutesTest extends AppTestCase
{
    const SNAPSHOT = __DIR__.'/routes_snapshot.json';

    const VISITORS = ['anonymous', 'user', 'other', 'admin'];

    /**
     * Route name => [path template, dataset keys filling the placeholders].
     * The dataset is the one built by AppTestCase.
     */
    private static function routes()
    {
        return [
            'index'                               => ['/'],
            'global_room'                         => ['/global_room'],
            'roots'                               => ['/roots'],
            'project'                             => ['/project'],
            'contact'                             => ['/contact'],
            'login'                               => ['/login'],
            'register'                            => ['/register'],
            'profile_private_discussions'         => ['/profile/private_discussions'],
            'profile_locked_private_discussions'  => ['/profile/locked_private_discussions'],
            'profile_followed_discussions'        => ['/profile/followed_discussions'],
            'profile_proposals'                   => ['/profile/proposals'],
            'profile_administration'              => ['/profile/administration'],
            'email_edit'                          => ['/email/edit'],
            'password_edit'                       => ['/password/edit'],
            'avatar_edit'                         => ['/avatar/edit'],
            'theme_show_proposals'                => ['/theme/show_proposals/%s', 'theme'],
            'theme_show_locked_proposals'         => ['/theme/show_locked_proposals/%s', 'theme'],
            'theme_show_discussions'              => ['/theme/show_discussions/%s', 'theme'],
            'theme_show_locked_discussions'       => ['/theme/show_locked_discussions/%s', 'theme'],
            'theme_show_children'                 => ['/theme/show_children/%s', 'theme'],
            'theme_addroot'                       => ['/theme/addroot'],
            'theme_add'                           => ['/theme/add/%s', 'theme'],
            'theme_edit'                          => ['/theme/edit/%s', 'theme'],
            'theme_move'                          => ['/theme/move/%s', 'child'],
            'theme_delete'                        => ['/theme/delete/%s', 'child'],
            'proposal_show_motivation'            => ['/proposal/show_motivation/%s', 'proposal'],
            'proposal_show_articles'              => ['/proposal/show_articles/%s', 'proposal'],
            'proposal_show_removed_articles'      => ['/proposal/show_removed_articles/%s', 'proposal'],
            'proposal_show_discussions'           => ['/proposal/show_discussions/%s', 'proposal'],
            'proposal_show_locked_discussions'    => ['/proposal/show_locked_discussions/%s', 'proposal'],
            'proposal_show_versioning'            => ['/proposal/show_versioning/%s', 'proposal'],
            'proposal_show_administration'        => ['/proposal/show_administration/%s', 'proposal'],
            'proposal_add'                        => ['/proposal/add/%s', 'theme'],
            'proposal_edit'                       => ['/proposal/edit/%s', 'proposal'],
            'proposal_support'                    => ['/proposal/support/%s', 'proposal'],
            'proposal_removesupport'              => ['/proposal/removesupport/%s', 'proposal'],
            'proposal_oppose'                     => ['/proposal/oppose/%s', 'proposal'],
            'proposal_removeoppose'               => ['/proposal/removeoppose/%s', 'proposal'],
            'proposal_move'                       => ['/proposal/move/%s', 'proposal'],
            'proposal_delete'                     => ['/proposal/delete/%s', 'proposal'],
            'proposal_lock'                       => ['/proposal/lock/%s', 'proposal'],
            'proposal_version_show'               => ['/proposal_version/show/%s', 'proposal_version'],
            'proposal_version_next_version'       => ['/proposal_version/next_version/%s', 'proposal_version'],
            'proposal_version_previous_version'   => ['/proposal_version/previous_version/%s', 'proposal_version'],
            'public_discussion_show'              => ['/public_discussion/show/%s', 'discussion'],
            'public_discussion_add_to_global'     => ['/public_discussion/add_to_global_discussions'],
            'public_discussion_add_to_theme'      => ['/public_discussion/add_to_theme/%s', 'theme'],
            'public_discussion_add_to_proposal'   => ['/public_discussion/add_to_proposal/%s', 'proposal'],
            'public_discussion_add_to_article'    => ['/public_discussion/add_to_article/%s', 'article'],
            'public_discussion_edit'              => ['/public_discussion/edit/%s', 'discussion'],
            'public_discussion_delete'            => ['/public_discussion/delete/%s', 'discussion'],
            'public_discussion_move_to_global'    => ['/public_discussion/move_to_global/%s', 'discussion'],
            'public_discussion_move_to_theme'     => ['/public_discussion/move_to_theme/%s', 'discussion'],
            'public_discussion_move_to_proposal'  => ['/public_discussion/move_to_proposal/%s', 'discussion'],
            'public_discussion_move_to_article'   => ['/public_discussion/move_to_article/%s', 'discussion'],
            'public_discussion_lock'              => ['/public_discussion/lock/%s', 'discussion'],
            'public_discussion_unlock'            => ['/public_discussion/unlock/%s', 'discussion'],
            'public_discussion_follow'            => ['/public_discussion/follow/%s', 'discussion'],
            'public_discussion_stop_following'    => ['/public_discussion/stop_following/%s', 'discussion'],
            'private_discussion_show'             => ['/private_discussion/show/%s', 'private_discussion'],
            'private_discussion_edit'             => ['/private_discussion/edit/%s', 'private_discussion'],
            'private_discussion_lock'             => ['/private_discussion/lock/%s', 'private_discussion'],
            'post_open_private_discussion'        => ['/post/open_private_discussion/%s', 'post'],
            'post_move'                           => ['/post/move/%s', 'post'],
            'post_delete'                         => ['/post/delete/%s', 'post'],
            'article_show'                        => ['/article/show/%s', 'article'],
            'article_show_discussions'            => ['/article/show_discussions/%s', 'article'],
            'article_show_locked_discussions'     => ['/article/show_locked_discussions/%s', 'article'],
            'article_show_versioning'             => ['/article/show_versioning/%s', 'article'],
            'article_show_administration'         => ['/article/show_administration/%s', 'article'],
            'article_add'                         => ['/article/add/%s', 'proposal'],
            'article_edit'                        => ['/article/edit/%s', 'article'],
            'article_delete'                      => ['/article/delete/%s', 'article'],
            'article_remove'                      => ['/article/remove/%s', 'article'],
            'article_version_show'                => ['/article_version/show/%s', 'article_version'],
            'article_version_previous_version'    => ['/article_version/previous_version/%s', 'article_version'],
            'article_version_next_version'        => ['/article_version/next_version/%s', 'article_version'],
        ];
    }

    public static function routeProvider()
    {
        foreach (array_keys(self::routes()) as $name) {
            foreach (self::VISITORS as $visitor) {
                yield $name.' as '.$visitor => [$name, $visitor];
            }
        }
    }

    #[DataProvider('routeProvider')]
    public function testRouteStatus($name, $visitor)
    {
        $route = self::routes()[$name];
        $arg   = isset($route[1]) ? $this->identifierOf($this->data[$route[1]]) : null;
        $path  = null === $arg ? $route[0] : sprintf($route[0], $arg);

        if ('anonymous' !== $visitor) {
            $this->logIn($visitor);
        }

        try {
            $this->client->request('GET', $path);
            $response = $this->client->getResponse();

            $actual = $response->getStatusCode();
            if ($response->isRedirect()) {
                $actual .= ' -> '.$this->normalizeLocation($response->headers->get('Location'));
            }
        } catch (\Throwable $e) {
            // Symfony < 4.4 lets PHP errors (TypeError, null method call...) escape
            // the kernel, newer versions turn them into a 500 response.
            $actual = '500';
        }

        $key = $name.'|'.$visitor;

        if (getenv('UPDATE_SNAPSHOT')) {
            $this->recordSnapshot($key, (string) $actual);
            $this->addToAssertionCount(1);

            return;
        }

        $snapshot = json_decode(file_get_contents(self::SNAPSHOT), true);
        $this->assertArrayHasKey($key, $snapshot, 'Missing from the snapshot.');
        $this->assertSame($snapshot[$key], (string) $actual, $path);
    }

    private function identifierOf($entity)
    {
        return method_exists($entity, 'getSlug') ? $entity->getSlug() : $entity->getId();
    }

    private function normalizeLocation($location)
    {
        $path = parse_url($location, PHP_URL_PATH);

        // Slugs and ids are generated: replace them to keep the snapshot stable.
        return preg_replace('#/[a-z0-9-]*\d[a-z0-9-]*$|/(theme-|proposition-|article-|discussion-)[a-z0-9-]+$#', '/{id}', $path);
    }

    private function recordSnapshot($key, $value)
    {
        $snapshot = file_exists(self::SNAPSHOT) ? json_decode(file_get_contents(self::SNAPSHOT), true) : [];
        $snapshot[$key] = $value;
        ksort($snapshot);
        file_put_contents(self::SNAPSHOT, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }
}

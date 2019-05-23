<?php
namespace Deployer;

require 'recipe/symfony3.php';

inventory('hosts.yml');

// Project name
set('application', 'dL');
set('keep_releases', 5);
set('shared_dirs', [
    'var/logs',
    'var/cache'
]);
set('writable_dirs', [
    'var/cache',
    'var/logs'
]);
set('shared_files', [
    'app/config/parameters.yml',
]);
set('writable_use_sudo', false);
set('bin_dir', 'bin');
set('ssh_type', 'native');
set('ssh_multiplexing', true);

// Project repository
set('repository', 'git@github.com:Democratie-Libre/democratie-libre.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', [
    'web/uploads'
]);

// Writable dirs by web server 
add('writable_dirs', [
    'web/uploads'
]);

// Tasks
task('build', function () {
    run('cd {{release_path}} && build');
});

task('deploy:vendors', function () {
    if (!commandExist('unzip')) {
        writeln('<comment>To speed up composer installation setup "unzip" command with PHP zip extension https://goo.gl/sxzFcD</comment>');
    }
    $opts = '{{composer_action}} --verbose --prefer-dist --no-progress --no-dev --optimize-autoloader';
    run('cd {{release_path}} && {{bin/composer}} '.$opts.'', ['tty' => true]);
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
before('deploy:symlink', 'database:migrate');

task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:create_cache_dir',
    'deploy:clear_paths',
    'deploy:shared',
    'deploy:assets',
    'deploy:vendors',
    'deploy:assets:install',
    'deploy:cache:warmup',
    'deploy:writable',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');

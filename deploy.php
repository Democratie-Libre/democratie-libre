<?php
namespace Deployer;

require 'recipe/symfony3.php';

inventory('hosts.yml');

// Project name
set('application', 'DL');
set('keep_releases', 5);
set('http_user', 'deployment');
set('shared_dirs', ['var/logs', 'var/cache']);
set('writable_dirs', ['var/cache', 'var/logs']);
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
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);


// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
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

<?php

return array(
    'secret' => 'change-my-secret', // for basic auth
    'mailgun' => array(
        'api_url' => 'https://api.mailgun.net/v2/DOMAIN_NAME/messages',
        'api_key' => 'key-some_key',
        'from' => 'some@email.com',
        'to' => 'some@email.com',
    ),
    'reps' => array(
        'https://github.com/account/repo' => array(
            'url' => 'https://github.com/account/repo',
            'ssh_url' => 'git@github.com:account/repo.git',
            'branches' => array(
                'develop' => array( // branch name
                    'name' => 'develop', // branch name
                    'folders' => array(
                        array(
                            'path' => '/var/www/vhosts/00-default/public/test_deploy'
                        )
                    )
                )
            )
        ),
    )
);

<?php return array(
    'root' => array(
        'name' => 'proprietary/spamfilter',
        'pretty_version' => '1.0.0-dev',
        'version' => '1.0.0.0-dev',
        'reference' => null,
        'type' => 'phpbb-extension',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'grpc/grpc' => array(
            'pretty_version' => '1.30.0',
            'version' => '1.30.0.0',
            'reference' => '31952d18884d91c674b73f8b4da831f708706f20',
            'type' => 'library',
            'install_path' => __DIR__ . '/../grpc/grpc',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'proprietary/spamfilter' => array(
            'pretty_version' => '1.0.0-dev',
            'version' => '1.0.0.0-dev',
            'reference' => null,
            'type' => 'phpbb-extension',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'protobuf-php/protobuf' => array(
            'pretty_version' => 'v0.1.3',
            'version' => '0.1.3.0',
            'reference' => 'c0da95f75ea418b39b02ff4528ca9926cc246a8c',
            'type' => 'library',
            'install_path' => __DIR__ . '/../protobuf-php/protobuf',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);

<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'drupal';
$app['version'] = '1.0.0';
$app['release'] = '1';
$app['vendor'] = 'Xtreem Solution';
$app['packager'] = 'Xtreem Solution';
$app['license'] = 'GPL';
$app['license_core'] = 'GPL';
$app['description'] = lang('drupal_app_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('drupal_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('base_subcategory_web');


/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////


$app['core_requires'] = array(
    'mod_authnz_external',
    'mod_authz_unixgroup',
    'mod_ssl',
    'phpMyAdmin',
    'app-flexshare-core',
);

$app['requires'] = array(
    'app-web-server',
    'app-mariadb',
    'unzip',
    'zip',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/drupal' => array(
        'mode' => '0775',
        'owner' => 'webconfig',
        'group' => 'webconfig'
	),
    '/var/clearos/drupal/backup' => array(
        'mode' => '0775',
        'owner' => 'webconfig',
        'group' => 'webconfig'
	),
    '/var/clearos/drupal/versions' => array(
        'mode' => '0775',
        'owner' => 'webconfig',
        'group' => 'webconfig'
    ),
    '/var/clearos/drupal/sites' => array(
        'mode' => '0775',
        'owner' => 'webconfig',
        'group' => 'webconfig'
	)
);

$app['core_file_manifest'] = array(
    'app-drupal.conf'=> array('target' => '/etc/httpd/conf.d/app-drupal.conf'),
);

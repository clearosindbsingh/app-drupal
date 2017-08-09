<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'drupal';
$app['version'] = '1.0.0';
$app['release'] = '1';
$app['vendor'] = 'Vendor'; // e.g. Acme Co
$app['packager'] = 'Packager'; // e.g. Gordie Howe
$app['license'] = 'MyLicense'; // e.g. 'GPLv3';
$app['license_core'] = 'MyLicense'; // e.g. 'LGPLv3';
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
);

$app['requires'] = array(
    'app-web-server',
    'app-mariadb',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/drupal' => array(),
    '/var/clearos/drupal/backup' => array(),
    '/var/clearos/drupal/verions' => array(),
);

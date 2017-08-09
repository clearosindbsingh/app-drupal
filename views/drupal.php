<?php

/**
 * Drupal Add project View.
 *
 * @category   Apps
 * @package    Drupal
 * @subpackage Views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link    http://www.clearfoundation.com/docs/developer/apps/drupal/
 */

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('drupal');


///////////////////////////////////////////////////////////////////////////////
// Form
///////////////////////////////////////////////////////////////////////////////

$options['buttons']  = array(
    anchor_custom('/app/drupal/backup', "Backups", 'high', array('target' => '_self')),
    anchor_custom('/app/mariadb', "MariaDB Server", 'high', array('target' => '_blank')),
    anchor_custom('/app/web_server', "Web Server", 'high', array('target' => '_blank')),
);


///////////////////////////////////////////////////////////////////////////////
// Infobox
///////////////////////////////////////////////////////////////////////////////

echo infobox_highlight(
    lang('drupal_app_name'),
    lang('drupal_app_dependencies_description'),
    $options
);



///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////
$headers = array(
    lang('drupal_project_folder_name'),
);

///////////////////////////////////////////////////////////////////////////////
// Buttons
///////////////////////////////////////////////////////////////////////////////

$buttons  = array(anchor_custom('/app/drupal/addproject', lang('drupal_add_project'), 'high', array('target' => '_self')));


///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($projects as $value) {
    $item['title'] = $value['name'];
    $access_action = $base_path.$value['name'];
    $access_admin_action = $base_path.$value['name'].'/wp-admin';
    $delete_action = "javascript:";
    $item['anchors'] = button_set(
        array(
        	anchor_custom($access_action, lang('drupal_access_website'), 'high', array('target' => '_blank')),
        	anchor_delete($delete_action, 'low', array('class' => 'delete_project_anchor', 'data' => array('folder_name' => $value['name']))),
        )
    );
    $item['details'] = array(
      $value['name']
    );
    $items[] = $item;
}


///////////////////////////////////////////////////////////////////////////////
// List table: Table for drupal versions
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('drupal_my_projects'),
    $buttons,
    $headers,
    $items
);


///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

$headers = array(
    lang('drupal_drupal_versions'),
);


///////////////////////////////////////////////////////////////////////////////
// Buttons
///////////////////////////////////////////////////////////////////////////////

$buttons  = array();


///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////
$items = array();
foreach ($versions as $value) {
    if ($value['clearos_path']) {
    	$download_btn = anchor_custom('javascript:', lang('drupal_version_download_btn'), 'high', array('class' => 'disabled', 'disabled' => 'disabled'));
    	$delete_btn = anchor_custom('/app/drupal/version/delete/'.$value['file_name'], lang('drupal_version_delete_btn'), 'low', array('class' => 'delete_version_anchor', 'data' => array('file_name'=> $value['file_name'])));
    }
    else {
    	$download_btn = anchor_custom('/app/drupal/version/download/'.$value['file_name'], lang('drupal_version_download_btn'), 'high');
    	$delete_btn = anchor_custom('javascript:', lang('drupal_version_delete_btn'), 'low', array('class' => 'disabled', 'disabled' => 'disabled'));	
    }
    $item['anchors'] = button_set(
        array(
        	$download_btn,
        	$delete_btn
      )
    );
    $item['details'] = array(
        "drupal: ".$value['version'],
    );
    $items[] = $item;
}


///////////////////////////////////////////////////////////////////////////////
// List table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('drupal_drupal_versions'),
    $buttons,
    $headers,
    $items
);


///////////////////////////////////////////////////////////////////////////////
// Make project delete confirm popup
///////////////////////////////////////////////////////////////////////////////

$title = lang('drupal_confirm_delete_project');
$message = form_open('drupal/delete');
$message = $message. field_checkbox("delete_sure","1", lang('drupal_yes_delete_this_project'));
$message = $message. field_checkbox("delete_database","1", lang('drupal_yes_delete_assigned_database'));
$message = $message. field_input('root_username', 'root', lang('drupal_mysql_root_username'));
$message = $message. field_password('root_password', '', lang('drupal_mysql_root_password'));
$message = $message. field_input('folder_name', '', 'Folder Name', FALSE, array('id' => 'deleting_folder_name'));
$message = $message. form_close();
$confirm = '#';
$trigger = '';
$form_id = 'delete_form';
$modal_id = 'delete_modal';

echo modal_confirm($title, $message, 'javascript:', $trigger, $form_id, $modal_id);
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

echo form_open('drupal/addproject');
echo form_header(lang('drupal_add_project'));
echo field_input('folder_name', '', lang('drupal_folder_name'));
echo field_dropdown('use_exisiting_database', array('Yes' => lang('drupal_select_yes'), 'No' => lang('drupal_select_no')), 'No', lang('drupal_use_existing_database'));
echo field_input('database_name', '', lang('drupal_database_name'));
echo field_input('database_user_name', 'testuser', lang('drupal_database_username'));
echo field_password('database_user_password', '', lang('drupal_database_password'));
echo field_input('root_username', 'root', lang('drupal_mysql_root_username'));
echo field_password('root_password', '', lang('drupal_mysql_root_password'));
echo field_dropdown('drupal_version', $versions, $default_version, lang('drupal_drupal_version'));
echo field_button_set(
    array(
    	anchor_cancel('/app/drupal'),
    	form_submit_add('submit', 'high')
    )
);
echo form_footer();
echo form_close();

?>
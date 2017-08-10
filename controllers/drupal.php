<?php

/**
 * Version controller.
 *
 * @category   Apps
 * @package    Drupal
 * @subpackage Controller
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link    http://www.clearfoundation.com/docs/developer/apps/drupal/
 */


///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Drupal controller.
 *
 * @category   Apps
 * @package    Drupal
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link    http://www.clearfoundation.com/docs/developer/apps/drupal/
 */

class Drupal extends ClearOS_Controller
{
    /**
     * Drupal default controller.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('drupal');
        $this->load->library('drupal/Drupal');

        $projects = $this->drupal->get_project_list();
        $versions = $this->drupal->get_versions();
        $data['projects'] = $projects;
        $data['versions'] = $versions;
        $data['base_path'] = 'https://'.$_SERVER['SERVER_ADDR'].'/drupal/';

        // Load views
        //-----------
        $this->page->view_form('drupal', $data, lang('drupal_app_name'));
    }
    /**
     * Add a new Project
     * 
     * @return load view
     */ 
    function addproject()
    {
        // Load dependencies
        //------------------

        $this->lang->load('drupal');
        $this->load->library('drupal/Drupal');

        $version_all = $this->drupal->get_versions();
        $versions = array();
        foreach ($version_all as $key => $value) {
            if ($value['clearos_path'])
                $versions[$value['file_name']] = $value['version'];
        }
        if ($_POST) {

            // Handle Form 
            //------------------

            $use_exisiting_database = $this->input->post('use_exisiting_database');
            $this->form_validation->set_policy('folder_name', 'drupal/Drupal', 'validate_folder_name', TRUE);
            if ($use_exisiting_database == "Yes")
                $this->form_validation->set_policy('database_name', 'drupal/Drupal', 'validate_existing_database', TRUE);
            else
                $this->form_validation->set_policy('database_name', 'drupal/Drupal', 'validate_new_database', TRUE);
            $this->form_validation->set_policy('database_user_name', 'drupal/Drupal', 'validate_database_username', TRUE);
            $this->form_validation->set_policy('database_user_password', 'drupal/Drupal', 'validate_database_password', TRUE);
            $this->form_validation->set_policy('root_username', 'drupal/Drupal', 'validate_root_username', TRUE);
            $this->form_validation->set_policy('root_password', 'drupal/Drupal', 'validate_root_password', TRUE);
            $this->form_validation->set_policy('drupal_version', 'drupal/Drupal', 'validate_drupal_version', TRUE);
            $form_ok = $this->form_validation->run();
            if ($form_ok) {
                $folder_name = $this->input->post('folder_name');
                $database_name = $this->input->post('database_name');
                $database_username = $this->input->post('database_user_name');
                $database_user_password = $this->input->post('database_user_password');
                $root_username = $this->input->post('root_username');
                $root_password = $this->input->post('root_password');
                $drupal_version = $this->input->post('drupal_version');
                try {
                    $this->drupal->add_project($folder_name, $database_name, $database_username, $database_user_password, $root_username, $root_password, $use_exisiting_database, $drupal_version);
                    $this->page->set_message(lang('drupal_project_add_success'), 'info');
                    redirect('/drupal');
                } catch (Exception $e) {
                    $this->page->view_exception($e);
                }
            }
        }
        $data['versions'] = $versions;
        $data['default_version'] = 'latest.zip';
        $this->page->view_form('add_project', $data, lang('drupal_app_name'));
    }
    /**
     * Delete Project
     *
     * @param string $folder_name Folder Name 
     * @return redirect to index after delete
     */ 
    function delete($folder_name)
    {
        // Load dependencies
        //------------------

        $this->lang->load('drupal');
        $this->load->library('drupal/Drupal');

        if ($_POST) {
            $database_name = '';
            $folder_name = $this->input->post('folder_name');
            $delete_database = $this->input->post('delete_database');
            if ($folder_name)
                $database_name = $this->drupal->get_database_name($folder_name);
            $_POST['database_name'] = $database_name;
            $_POST['folder_name'] = $folder_name;
            $this->form_validation->set_policy('folder_name', 'drupal/Drupal', 'validate_folder_name_exists', TRUE);

            if ($delete_database && $database_name) {
                $this->form_validation->set_policy('database_name', 'drupal/Drupal', 'validate_existing_database', TRUE);
                $this->form_validation->set_policy('root_username', 'drupal/Drupal', 'validate_root_username', TRUE);
                $this->form_validation->set_policy('root_password', 'drupal/Drupal', 'validate_root_password', TRUE);
            }
            $form_ok = $this->form_validation->run();

            if ($form_ok) {
                $folder_name = $this->input->post('folder_name');
                $database_name = $this->input->post('database_name');
                $root_username = $this->input->post('root_username');
                $root_password = $this->input->post('root_password');

                try {	
                    $this->drupal->delete_folder($folder_name);

                    if ($delete_database && $database_name) {
                        //$this->drupal->backup_database($database_name, $root_username, $root_password); /// due to some temp error I commented it
                        $this->drupal->delete_database($database_name, $root_username, $root_password);
                    }
                    $this->page->set_message(lang('drupal_project_delete_success'), 'info');
                    redirect('/drupal');
                } catch (Exception $e) {
                    $this->page->view_exception($e);
                }
            }
        }
    }
}

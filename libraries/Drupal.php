<?php

/**
 * Drupal Libraray class.
 *
 * @category   apps
 * @package    Drupal
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2005-2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/drupal/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// N A M E S P A C E
///////////////////////////////////////////////////////////////////////////////

namespace clearos\apps\drupal;

///////////////////////////////////////////////////////////////////////////////
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////

$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';

///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////

clearos_load_language('drupal');

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

// Factories
//----------

use \clearos\apps\groups\Group_Manager_Factory as Group_Manager;

clearos_load_library('groups/Group_Manager_Factory');

// Classes
//--------

use \clearos\apps\base\Daemon as Daemon;
use \clearos\apps\base\File as File;
use \clearos\apps\base\Shell as Shell;
use \clearos\apps\base\File_Types as File_Types;
use \clearos\apps\base\Folder as Folder;
use \clearos\apps\base\Tuning as Tuning;
use \clearos\apps\network\Role as Role;

clearos_load_library('base/Daemon');
clearos_load_library('base/File');
clearos_load_library('base/Shell');
clearos_load_library('base/File_Types');
clearos_load_library('base/Folder');
clearos_load_library('base/Tuning');
clearos_load_library('network/Role');

// Exceptions
//-----------

use \Exception as Exception;
use \clearos\apps\base\Engine_Exception as Engine_Exception;
use \clearos\apps\base\File_No_Match_Exception as File_No_Match_Exception;
use \clearos\apps\base\File_Not_Found_Exception as File_Not_Found_Exception;
use \clearos\apps\base\Validation_Exception as Validation_Exception;

clearos_load_library('base/Engine_Exception');
clearos_load_library('base/File_No_Match_Exception');
clearos_load_library('base/File_Not_Found_Exception');
clearos_load_library('base/Validation_Exception');

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Drupal class.
 *
 * @category   apps
 * @package    drupal
 * @subpackage libraries
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2005-2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU Lesser General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/drupal/
 */

class Drupal extends Daemon
{
    ///////////////////////////////////////////////////////////////////////////
    // C O N S T A N T S
    ///////////////////////////////////////////////////////////////////////////

    const PATH_WEBROOT = '/var/www/html';
    const PATH_DRUPAL = '/var/www/html/drupal';
    const PATH_VERSIONS = '/var/clearos/drupal/versions/';
    const PATH_BACKUP = '/var/clearos/drupal/backup/';
    const COMMAND_MYSQLADMIN = '/usr/bin/mysqladmin';
    const COMMAND_MYSQL = '/usr/bin/mysql';
    const COMMAND_WGET = '/bin/wget';
    const COMMAND_ZIP = '/bin/zip';
    const COMMAND_UNZIP = '/bin/unzip';
    const COMMAND_MV = '/bin/mv';
    const CONFIG_SAMPLE_FILE_NAME = 'default.settings.php';
    const CONFIG_MAIN_FILE_NAME = 'settings.php';
    const CONFIG_MAIN_FILE_PATH = 'sites/default/settings.php';

    ///////////////////////////////////////////////////////////////////////////
    // V A R I A B L E S
    ///////////////////////////////////////////////////////////////////////////

    var $locales;

    ///////////////////////////////////////////////////////////////////////////
    // M E T H O D S
    ///////////////////////////////////////////////////////////////////////////

    /**
     * DansGuardian constructor.
     */

    public function __construct()
    {
        clearos_profile(__METHOD__, __LINE__);

        parent::__construct('drupal');

    }
    /**
     * Get Project path
     *
     * @param @string $folder_name Folder Name
     *
     * @return @string path of folder
     */
    function get_project_path($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        return self::PATH_DRUPAL.'/'.$folder_name.'/';
    }
    /**
     * Get Drupal version
     *
     * @return @array Array of available versions
     */
    function get_versions()
    {
        $versions = array(
                array(
                    'version' => '8.3.6',
                    'download_url' => 'https://ftp.drupal.org/files/projects/drupal-8.3.6.zip',
                    'deletable' => FALSE,
                    'size' => '',
                ),
                array(
                    'version' => '7.54',
                    'download_url' => 'https://ftp.drupal.org/files/projects/drupal-7.54.zip',
                    'deletable' => FALSE,
                    'size' => '',
                ),
            );
        foreach ($versions as $key => $value) {
            $versions[$key]['file_name'] = basename($versions[$key]['download_url']);
            $versions[$key]['clearos_path'] = $this->get_drupal_version_downloaded_path(basename($versions[$key]['download_url']));
        }
        return $versions;
    }
    /**
     * Get local system download Drupal version path
     * so system can copy from this path to new folder path 
     * 
     * @param @string $version_name zipped version name 
     *
     * @return @string $zip_folder if downloaded & available | FALSE if zip file is not available or not downloaded
     */
    function get_drupal_version_downloaded_path($version_name)
    {
        $zip_folder = self::PATH_VERSIONS.$version_name;
        $folder = new Folder($zip_folder, TRUE);
        if ($folder->exists())
            return $zip_folder;
        return FALSE;

    }

    /**
     * Add a new project.
     *
     * @param string $folder_name Folder Name            
     * @param string $database_name Database name 
     * @param string $database_username Database user 
     * @param string $database_user_password Database user password 
     * @param string $root_username Root username for root permissions 
     * @param string $root_password Root password 
     * @param string $use_exisiting_database Yes / No if you want to use existing database
     * @param string $drupal_version_file selected drupal version zip file name
     *
     * @return void
     */

    public function add_project(
        $folder_name, $database_name, $database_username, $database_user_password,
        $root_username, $root_password, $use_exisiting_database = "No", $drupal_version_file = 'latest.zip'
        ) 
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();

        if ($use_exisiting_database == "No")
            $command = "mysql -u $root_username -p$root_password -e \"create database $database_name; GRANT ALL PRIVILEGES ON $database_name.* TO $database_username@localhost IDENTIFIED BY '$database_user_password'\"";
        else
            $command = "mysql -u $root_username -p$root_password -e \"GRANT ALL PRIVILEGES ON $database_name.* TO $database_username@localhost IDENTIFIED BY '$database_user_password'\"";

        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Engine_Exception($e->get_message());
        }
        $output = $shell->get_output();
        $output_message = strtolower($output[0]);
        if (strpos($output_message, 'error') !== FALSE)
            throw new Exception($output_message);

        $this->create_project_folder($folder_name);
        $this->put_drupal($folder_name, $drupal_version_file);
        $this->copy_sample_config_file($folder_name);
        //$this->set_database_name($folder_name, $database_name);
        //$this->set_database_user($folder_name, $database_username);
        //$this->set_database_password($folder_name, $database_user_password);
        return $output;
    }
    /**
     * Copy Config File from sample file 
     *
     * @param string $folder_name Folder Name
     *
     * @return void
     */
    function copy_sample_config_file($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $folder_path = $this->get_project_path($folder_name);
        $sample_file = $folder_path.'sites/default/'.self::CONFIG_SAMPLE_FILE_NAME;
        $main_file = $folder_path.'sites/default/'.self::CONFIG_MAIN_FILE_NAME;

        $sample_file_obj    = new File($sample_file, TRUE);
        $main_file_obj      = new File($main_file, TRUE);

        if (!$main_file_obj->exists())
            $sample_file_obj->copy_to($main_file);

        // set file permission writtable
        $main_file_obj      = new File($main_file, TRUE);
        $main_file_obj->chmod(777);
    }
    /**
    * Validate Folder Name.
    *
    * @param string $folder_name Folder Name
    *
    * @return string error message if Folder name is invalid
    */
    public function validate_folder_name($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/^([a-z0-9_\-\.\$]+)$/', $folder_name))
            return lang('drupal_folder_name_invalid');
        else if($folder_name == 'drupal')
            return lang('drupal_folder_name_choose_other');
        else if($this->check_folder_exists($folder_name))
            return lang('drupal_folder_already_exists');
    }
    /**
     * Validate Folder name must be exists.
     *
     * @param string $folder_name Folder name
     *
     * @return string error message if Folder name is not exists
     */
    public function validate_folder_name_exists($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/^([a-z0-9_\-\.\$]+)$/', $folder_name))
            return lang('drupal_folder_name_invalid');
    }
    /**
     * Validate if database is new.
     *
     * @param string $database_name Database Name
     *
     * @return string error message if Database name is exists
     */
    public function validate_new_database($database_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $root_username = $_POST['root_username'];
        $root_password = $_POST['root_password'];
        $command = "mysql -u $root_username -p$root_password -e \"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database_name'\"";
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            return $e->get_message();
        }
        $output = $shell->get_output();
        $output_message = strtolower($output);
        if (strpos($output_message, 'error') !== FALSE)
            return lang('drupal_unable_connect_via_root_user');
        else if($output)
            return lang('drupal_database_already_exits');
    }
    /**
     * Validate if database is exisitng.
     *
     * @param string $database_name Database Name
     *
     * @return string error message if database name is not exists
     */
    public function validate_existing_database($database_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $root_username = $_POST['root_username'];
        $root_password = $_POST['root_password'];
        $command = "mysql -u $root_username -p$root_password -e \"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database_name'\"";
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            return $e->get_message();
        }
        $output = $shell->get_output();
        $output_message = strtolower($output);
        if (strpos($output_message, 'error') !== FALSE)
            return lang('drupal_unable_connect_via_root_user');
        else if(!$output)
            return lang('drupal_database_not_exits');
    }
    /**
     * Validate database username.
     *
     * @param string $username Username
     *
     * @return string error message if exists
     */
    public function validate_database_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/^([a-z0-9_\-\.\$]+)$/', $username))
            return lang('drupal_username_invalid');
    }
    /**
     * Validate database password.
     *
     * @param string $password Password
     *
     * @return string error message if exists
     */
    public function validate_database_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/.*\S.*/', $password))
            return lang('drupal_password_invalid');
    }
    /**
     * Validate root username.
     *
     * @param string $username Username
     *
     * @return string error message if exists
     */
    public function validate_root_username($username)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/^([a-z0-9_\-\.\$]+)$/', $username))
            return lang('drupal_username_invalid');
    }
    /**
     * Validate database root password.
     *
     * @param string $password Password
     *
     * @return string error message if exists
     */
    public function validate_root_password($password)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/.*\S.*/', $password))
            return lang('drupal_password_invalid');
    }
    /**
     * Validate drupal version.
     *
     * @param string $drupal_version version file name 
     *
     * @return string error message if exists
     */
    public function validate_drupal_version($drupal_version)
    {
        clearos_profile(__METHOD__, __LINE__);
        if (! preg_match('/.*\S.*/', $drupal_version))
            return lang('drupal_password_invalid');
    }
    /**
     * Check Folder Exists.
     *
     * @param string $folder_name Folder name
     *
     * @return TRUE if exists, FALSE if not exists 
     */
    function check_folder_exists($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $wpfolder = new Folder(self::PATH_DRUPAL, TRUE);
        $project_path = self::PATH_DRUPAL.'/'.$folder_name;
        if (!$wpfolder->exists()) {
            $wpfolder->create('root', 'root', 0775);
            return FALSE;
        }
        $project_folder = new Folder($project_path, TRUE);
        if ($project_folder->exists()) {
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Create Project Folder.
     *
     * @param string $folder_name Folder Name
     *
     * @return void
     */
    function create_project_folder($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        if ($this->check_folder_exists($folder_name)) {
            return FALSE;
        }
        $new_folder = new Folder(self::PATH_DRUPAL.'/'.$folder_name, TRUE);
        $new_folder->create('root', 'root', 0777);

    }
    /**
     * Download and setup drupal folder.
     *
     * @param string $folder_name Folder name
     * @param string $version_name Version name
     *
     * @return void
     */
    function put_drupal($folder_name, $version_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        $path_drupal = self::PATH_DRUPAL;

        //echo $this->get_drupal_version_downloaded_path($version_name); die;
        $file = new File($this->get_drupal_version_downloaded_path($version_name));
        if (!$file->exists())
            return FALSE;
        $file->copy_to($path_drupal);

        //// create a temp folder to copy
        $folder = new Folder($this->get_project_path('drupal'));
        if(!$folder->exists())
        	$folder->create('root', 'root', 0777);


        $shell = new Shell();
        $options['validate_exit_code'] = FALSE;

        $command = $path_drupal."/$version_name -d ".$this->get_project_path('drupal');

        try {
            $retval = $shell->execute(
                self::COMMAND_UNZIP, $command, TRUE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e);
        }
        $output = $shell->get_output();

        // get the name of inner / setup folder
        $folder = new Folder($this->get_project_path('drupal'));
        $list = $folder->get_listing(TRUE);

        $setup_folder = $list[0]['name'];

        // move setup folder to actual folder
        $setup_path = $this->get_project_path('drupal').$setup_folder.'/';
        $folder = new Folder($setup_path);
        $list = $folder->get_listing(TRUE, TRUE);
        foreach ($list as $key => $value) {
        	if ($value['is_dir']) {
        		$folder = new Folder($setup_path.$value['name']);
        		$folder->move_to($this->get_project_path($folder_name));
        	}
        	else {
        		$file = new File($setup_path.$value['name']);
        		$file->move_to($this->get_project_path($folder_name));
        	}
        }

        $folder = new Folder($this->get_project_path($folder_name).'sites');
        $folder->chmod(777);

        $folder = new Folder($this->get_project_path($folder_name).'sites/default');
        $folder->chmod(777); 

        // delete temp folder
        $folder = new Folder($this->get_project_path('drupal'));
        $folder->delete(TRUE);

        // delete temp zip file
        $file = new File($path_drupal.'/'.$version_name);
        if($file->exists() && (!$file->is_directory()))
            $file->delete();
        return $output;
    }
    /**
     * Download drupal version from official website.
     *
     * @param string $version_file_name Zip file name
     *
     * @return TRUE if download completed, FALSE if folder exists, ERROR if something goes wrong
    **/
    function download_version($version_file_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $options['validate_exit_code'] = FALSE;
        
        $path_versions = self::PATH_VERSIONS;
        $path_file = $path_versions.$version_file_name;

        $file = new File($path_file, TRUE);
        if($file->exists())
           return FALSE;
        
        $versions = $this->get_versions();
        $download_url = '';
        foreach ($versions as $key => $value) {
            if($value['file_name'] == $version_file_name) {
                $download_url = $value['download_url'];
                break;
            }
        }

        $shell = new Shell();
        $command = "$download_url -P $path_versions";
        try {
            $retval = $shell->execute(
                self::COMMAND_WGET, $command, TRUE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e);
        }
        $output = $shell->get_output();
        return TRUE;
    }
    /**
     * Delete downloaded drupal version.
     *
     * @param string $version_file_name Zip file name
     *
     * @return TRUE if delete completed, FALSE if file not exists, ERROR if something goes wrong 
     */
    function delete_version($version_file_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        
        $path_versions = self::PATH_VERSIONS;
        $path_file = $path_versions.$version_file_name;

        $file = new File($path_file, TRUE);
        if (!$file->exists())
           return FALSE;
        $file->delete();
            return TRUE;
    }
    /**
     * List of project.
     *
     * @return array $list of all projects under drupal
     */
    function get_project_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();
        $folder = new Folder(self::PATH_DRUPAL);
        if ($folder->exists()) {
            $list = $folder->get_listing(TRUE, FALSE);
        }
        return $list;
    }
    /**
     * Delete project folder.
     *
     * @param string $folder_name Folder Name
     *
     * @return void
     */
    function delete_folder($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        $this->get_database_name($folder_name);
        $this->do_backup_folder($folder_name);
        $folder = new Folder($this->get_project_path($folder_name));
        $folder->delete(TRUE);
    }
    /**
     * Create backup of given project folder.
     *
     * @param string $folder_name Folder Name
     *
     * @return void
     */
    function do_backup_folder($folder_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        $folder_path = $this->get_project_path($folder_name);

        $zip_path = self::PATH_DRUPAL.'/'.$folder_name.'__'.date('Y-m-d-H-i-s').'.zip';
        $command = "-r $zip_path $folder_path";
        
        $options['validate_exit_code'] = FALSE;
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_ZIP, $command, TRUE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e);
        }
        $output = $shell->get_output();
        $file = new File($zip_path);

        $backup_folder = new Folder(self::PATH_BACKUP);
        if(!$backup_folder->exists())
        	$backup_folder->create('root','root',755);

        if ($file->exists() && !$file->is_directory()) {
            $file->move_to(self::PATH_BACKUP);
        }
    }

    /**
     * Get database name from config file.
     *
     * @param string $folder_name Project folder name
     *
     * @return string $database_name Database Name
     */
    function get_database_name($folder_name)
    {
        return $this->find_value_from_config($folder_name, 'database');
    }

    /**
     * Get variable value from config file.
     *
     * @param string $folder_name Project folder name
     * @param string $key Key
     *
     * @return string $value Value of given key
     */
    function find_value_from_config($folder_name, $key)
    {
    	$folder_path = $this->get_project_path($folder_name);
        $main_file = $folder_path.self::CONFIG_MAIN_FILE_PATH;
        
        $file = new File($main_file, TRUE);
        $lines = $file->get_contents_as_array();
        $key_number = '';
        $setuped = false;
        foreach ($lines as $key => $value) {
        	if(trim($value) == "'default' =>") {
        		$setuped = true;
        	}
        	if (strpos($value, "'$key' =>") !== false) {
        		if ($setuped) {
			    	$key_number = $key;
			    	break;
        		}
			}
        }
        if (!$key_number)
        	return false;
        $string = explode(' => ', $lines[$key_number]);
        preg_match('/".*?"|\'.*?\'/', $string[1], $matches);
        $value = trim($matches[0], "'");
        return $value;
    }
    /**
     * Delete MYSQL database.
     *
     * @param string $database_name Database Name
     * @param string $root_username Root Username
     * @param string $root_password Root Password
     *
     * @return Exception is somethings goes wrong with MYSQL 
    */
    function delete_database($database_name, $root_username, $root_password)
    {
        $command = "mysql -u $root_username -p$root_password -e \"DROP DATABASE $database_name\"";
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e->get_message());
        }
        $output = $shell->get_output();
        $output_message = strtolower($output);

        if (strpos($output_message, 'error') !== FALSE)
            throw new Exception(lang('drupal_unable_connect_via_root_user'));
    }
    /**
     * Backup MYSQL database.
     *
     * @param string $database_name Database Name
     * @param string $root_username Root Username
     * @param string $root_password Root Password
     *
     * @return Exception is somethings goes wrong with MYSQL 
    */
    function backup_database($database_name, $root_username, $root_password)
    {
        $sql_file_path = self::PATH_BACKUP.$database_name.'__sql__'.date('Y-m-d-H-i-s').'.sql';
        $command = "mysql -u $root_username -p$root_password -e \"mysqldump $database_name > $sql_file_path\"";
        //echo $command; die;
        $shell = new Shell();
        try {
            $retval = $shell->execute(
                self::COMMAND_MYSQL, $command, FALSE, $options
            );
        } catch (Engine_Exception $e) {
            throw new Exception($e->get_message());
        }
        $output = $shell->get_output();
        $output_message = strtolower($output);
        if (strpos($output_message, 'error') !== FALSE)
            throw new Exception(lang('drupal_unable_connect_via_root_user'));
        
    }
    /**
     * List of avalable Project & SQL backups.
     *
     * @return list of all backups under drupal including database
    */
    function get_backup_list()
    {
        clearos_profile(__METHOD__, __LINE__);

        $list = array();
        $folder = new Folder(self::PATH_BACKUP);
        if ($folder->exists()) {
            $list = $folder->get_listing(TRUE, TRUE);
        }
        return $list;
    }
    /**
     * Start force download of backup
     *
     * @param string $file_name Backup file name
     * @return void
    */
    function download_backup($file_name)
    {
        clearos_profile(__METHOD__, __LINE__);
        // Make file full path
        $file_path = self::PATH_BACKUP.$file_name;

        // Check file exists
        if (file_exists($file_path)) {
            // Getting file extension.
            $extension = explode('.', $file_name);
            $extension = $extension[count($extension)-1]; 
            // For Gecko browsers
            header('Content-Transfer-Encoding: binary');  
            // Supports for download resume
            header('Accept-Ranges: bytes');  
            // Calculate File size
            header('Content-Length: ' . filesize($file_path));  
            header('Content-Encoding: none');
            // Change the mime type if the file is not PDF
            header('Content-Type: application/'.$extension);  
            // Make the browser display the Save As dialog
            header('Content-Disposition: attachment; filename=' . $file_name);  
            readfile($file_path); 
            exit;
        }
        else
            throw new File_Not_Found_Exception(lang('drupal_file_not_found'));
    }
    /**
     * Delete backup from system
     *
     * @param string $file_name Backup file name
     * @return TRUE if deletion successful, Exception if something wrong in deletion
    **/
    function delete_backup($file_name)
    {
        clearos_profile(__METHOD__, __LINE__);

        $file_path = self::PATH_BACKUP.$file_name;
        $file = new File($file_path);

        if (!$file->is_directory())
            $file->delete(TRUE);
        else
            throw new File_Not_Found_Exception(lang('drupal_file_not_found'));
        return TRUE;
    }
}
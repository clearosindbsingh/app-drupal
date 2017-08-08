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

class Version extends ClearOS_Controller
{
	/**
     * Download Version file on local system
     *
     * @param string $file_name File Name 
     * @return redirect to index after download 
     */ 
    function download($file_name)
	{
		// Load dependencies
        //------------------

		$this->lang->load('drupal');
		$this->load->library('drupal/Drupal');

		$this->drupal->download_version($file_name);
		$this->page->set_message(lang('drupal_version_download_success'), 'info');
		redirect('/drupal');
	}
	/**
     * Delete Version file on local system
     *
     * @param string $file_name File Name 
     * @return redirect to index after delete 
     */ 
	function delete($file_name)
	{
		// Load dependencies
        //------------------

		$this->lang->load('drupal');
		$this->load->library('drupal/Drupal');

		$this->drupal->delete_version($file_name);
		$this->page->set_message(lang('drupal_version_delete_success'), 'info');
		redirect('/drupal');
	}
}

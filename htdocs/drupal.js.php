<?php
/**
 * Drupal Javascript Helper.
 *
 * @category   apps
 * @package    Drupal
 * @subpackage javascript
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2017 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/drupal/
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
// B O O T S T R A P
///////////////////////////////////////////////////////////////////////////////
$bootstrap = getenv('CLEAROS_BOOTSTRAP') ? getenv('CLEAROS_BOOTSTRAP') : '/usr/clearos/framework/shared';
require_once $bootstrap . '/bootstrap.php';
///////////////////////////////////////////////////////////////////////////////
// T R A N S L A T I O N S
///////////////////////////////////////////////////////////////////////////////
clearos_load_language('base');
clearos_load_language('drupal');
///////////////////////////////////////////////////////////////////////////////
// J A V A S C R I P T
///////////////////////////////////////////////////////////////////////////////
header('Content-Type: application/x-javascript');
?>

///////////////////////////////////////////////////////////////////////////////
// For main page
///////////////////////////////////////////////////////////////////////////////
$(function(){
	$(".delete_project_anchor").click(function(e){
		e.preventDefault();
		openProjectDeleteDialog(this);
		return false;
	});
	$(".delete_version_anchor").click(function(e){
		e.preventDefault();
		deleteDrupalVersion(this);
		return false;
	});
	$(".hide_btn").click(function(){
		$("#delete_modal").modal('hide');
	})
	$("#delete_database").change(function(){
		if($(this).is(":checked"))
		{
			$("#root_username_field").show();
			$("#root_password_field").show();
		}
		else
		{
			$("#root_username_field").hide();
			$("#root_password_field").hide();
		}
	});
	$("#delete_sure").change(function(){
		if($(this).is(":checked"))
		{
			$("#modal-confirm-delete_modal").removeClass('disabled').removeAttr('disabled');
		}
		else
		{
			$("#modal-confirm-delete_modal").addClass('disabled').attr('disabled','disabled');
		}
	});
	$("#modal-confirm-delete_modal").click(function(e) {
		e.preventDefault();
		if($("#delete_sure").is(":checked")) {
			$("#delete_modal form").submit();
		}
	});
});
function openProjectDeleteDialog($this)
{
	$("#deleting_folder_name").val($($this).attr('data-folder_name'));
	$("#delete_sure").attr('checked',false);
	$("#delete_database").attr('checked',false);
	$("#deleting_folder_name_field").hide();
	$("#root_username_field").hide();
	$("#root_password_field").hide();
	$("#modal-confirm-delete_modal").addClass('disabled').attr('disabled','disabled');
	$("#delete_modal").modal('show');
}
function deleteProject($this)
{
	var folder_name = $($this).attr('data-folder_name');
	var con = confirm("Are you sure want to delete this project ?");
	if(con)
		window.location.href = '/app/drupal/delete/'+folder_name;
}
function deleteDrupalVersion($this)
{
	var file_name = $($this).attr('data-file_name');
	var con = confirm("Are you sure want to delete this version ?");
	if(con)
		window.location.href = '/app/drupal/version/delete/'+file_name;
}



///////////////////////////////////////////////////////////////////////////////
// For add project page
///////////////////////////////////////////////////////////////////////////////

$(function(e){
	selectDatabase();
	$("#use_exisiting_database").change(function(){
		selectDatabase();
	})
})
function selectDatabase()
{
	var option = $("#use_exisiting_database").val();
	if(option == "Yes")
	{
		$("#database_name_label").text("Existing DB Name");
	}
	else
	{
		$("#database_name_label").text("New DB Name");
	}
}
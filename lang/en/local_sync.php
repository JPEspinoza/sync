<?php
/**
 * Strings for component "sync", language "en"
 *
 * @package	sync
 */

defined("MOODLE_INTERNAL") || die();

$string["pluginname"] = "Omega Synchronizations";
$string["sync_title"] = "Synchronization";
$string["sync_subtitle"] = "Create Synchronization";
$string["sync_page"] = "Synchronization";
$string["sync_heading"] ="Synchronization";
$string["sync_sub_heading"] ="Create Synchronization";
$string["omega"] = "Academic Period";
$string["webc"] = "Categories";
$string["in_charge"] = "Responsible";
$string["in_charge_help"] = "The responsible will be notified of when the synchronization has completed. Note that this field is optional, but it is recommended
		since it's an easy way to track a synchronization's success. Ex: user@uai.cl";
$string["in_charge_default"] ="select a responsible";
$string["buttons"] = "Create";
$string["optional"] = "If you are looking for another period please ";
$string["create"] = "Create Syncronization";
$string["record"] = "Records";
$string["error_period"] = "Select an academic period to be synchronized";
$string["error_period_active"] = "The selected academic period is already saved in an active synchronization";
$string["error_period_inactive"] = "The selected academic period is already saved in an inactive synchronization";
$string["error_omega"] = "Select a category";
$string["error_omega_active"] = "The selected category is already saved in an active synchronization";
$string["error_omega_inactive"] = "The selected category is already saved in an inactive synchronization";
$string["error_responsible_invalid"] = "Invalid email (must be @uai.cl)";
$string["error_responsible_nonexistent"] = "Email does not exist in the database";
$string["error_communication"] = "Failed to retrieve the academic period list from Omega. Try again later.";
$string["sync_success"] = "Synchronization saved successfully";
$string["status"] = "Status";
$string["active"] = "Active";
$string["inactive"] = "Inactive";
$string["task_courses"] = "Omega courses synchronization";
$string["h_title"] = "Omega Sync";
$string["h_id"] = "ID";
$string["h_catid"] = "Category ID";
$string["h_catname"] = "Category name";
$string["h_academicperiodid"] = "Academic period ID";
$string["h_academicperiodname"] = "Academic period name";
$string["h_executiontime"] = "Execution time";
$string["h_synccourses"] = "Synchronized courses";
$string["h_syncenrols"] = "Synchronized enrols";
$string["h_emptytable"] = "The table is empty";
$string["h_tabletitle"] = "Synchronizations History";
$string["history"] = "History";
$string["omega_default"] = "Select a period...";
$string["webc_default"] = "Select a category...";
$string["timecreated"]="Time Created";
$string["academicperiod"] = "Academic Period";
$string["periodid"] = "Period ID";
$string["category"]	= "Category";
$string["categoryid"] = "Category ID";
$string["sede"] = "Campus";
$string["activation"] = "Activate/Desactivate";
$string["manualunsub"] = "Eliminate manual enrolments";
$string["selfunsub"]="Eliminate self- enrolments";
$string["edit"] = "Edit";
$string["deletesync"] = "This synchronization will be erased permanently Are you sure to continue?";
$string["syncrecordtitle"] = "Syncronization Records";
$string["synctable"] = "Records";
$string["errorperiod"] = "Error";
$string["editform"] = "¿Are you sure you want to edit this sync?";
$string["buttonedit"] = "Save Changes";
$string["syncdoesnotexist"] = "please select at least a sync";
$string["unenrol_success"] = "Users succesfully unenroled";
$string["unenrol_fail"] = "Failed to unenrol users. Try again later.";
$string["activate"]="Activate";
$string["deactivate"]="Deactivate";
$string["unenrol"]="Eliminate enrol";

//Settings
$string["token"] = "Token Omega";
$string["tokendesc"] = "Authorization Token for Webapi Omega.";
$string["urlgetalumnos"] = "Url GetAlumnos Service";
$string["urlgetalumnosdesc"] = "Url Omega Webapi to get students and teachers to sync.";
$string["urlgetcursos"] = "Url GetCursos Service";
$string["urlgetcursosdesc"] = "Url Omega Webapi to get courses to sync.";
$string["urlgetacademicperiods"] = "Url GetPeriodosAcademicos service";
$string["urlgetacademicperiodsdesc"] = "Url Omega Webapi to get academic periods to sync.";
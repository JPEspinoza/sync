<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 * @package    local
 * @subpackage sync
 * @copyright  2016 Hans Jeria (hansjeria@gmail.com)
 * @copyright  2017 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @copyright  2017 Mihail Pozarski (mpozarski944@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
// Define whether the sync has been actived or not inactived
define('SYNC_STATUS_INACTIVE', 0);
define('SYNC_STATUS_ACTIVE', 1);
define('MODULE_FORUM', 'forum');

function sync_validateomega_services($options = null){
    global $DB, $CFG;

    $registros = 0;
    $url = $CFG->sync_urlvalidateserviceomega;
    $token = $CFG->sync_token;
    /*$fields = array(
        "token" => $token
    );*/

    mtrace("\n\n## Validando servicios de Omega {$url} ##\n");
    for ($i = 1; $i<=3; $i++){

        mtrace("Intento de comunicacion {$i}");
        $registros = 0;

        try {

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_POST, FALSE);
            //curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

            mtrace("## conexion a servicios Omega Iniciada ##");
            $result = json_decode(curl_exec($curl));
            mtrace("## conexion a servicios Omega Completada ##");

            if(curl_errno($curl)){
                mtrace("## Error CURL ##");
                throw new Exception(curl_error($curl));
            }

            curl_close($curl);

            $registros = count($result);

        } catch (Exception $e) {
            mtrace('Excepción capturada: ',  $e->getMessage());
        }

        if ($registros > 0) break;
    }

    return $result;
}

function sync_getusers_fromomega($academicids, $syncinfo, $options = null){
	global $DB, $CFG;

    $registros = 0;

	$url = $CFG->sync_urlgetalumnos;
    $token = $CFG->sync_token;
    $fields = array(
			"token" => $token,
			"PeriodosAcademicos" => array($academicids)
	);

    mtrace("\n\n## Obteniendo listado de usuarios desde Omega {$url} ##\n");
    for ($i = 1; $i<=3; $i++) {

        mtrace("Intento de comunicacion {$i}");
        $registros = 0;

        try {

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($curl, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            curl_setopt($curl, CURLOPT_TIMEOUT, 200);

            mtrace("## conexion a servicios Omega Iniciada ##");
            $result = json_decode(curl_exec($curl));
            mtrace("## conexion a servicios Omega Completada ##");

            if(curl_errno($curl)){
                mtrace("## Error CURL ##");
                throw new Exception(curl_error($curl));
            }

            curl_close($curl);

            $registros = count($result);

        } catch (Exception $e) {
            mtrace('Excepción capturada: ',  $e->getMessage());
        }

        if ($registros > 0) break;

    }

    $users = array();
    $metausers = array();

    if ($registros > 0) {
        // Check the version to use the corrects functions
        if(PHP_MAJOR_VERSION < 7){
            $coursesids = array();
            foreach ($result as $course){
                $coursesids[] = $course->SeccionId;
            }
        }else{
            // Needs the academic period to record the history of sync
            $coursesids = array_column($result, 'SeccionId');
        }

        $academicdbycourseid = sync_getacademicbycourseids($coursesids);
        if ($options['debug']) {
            mtrace("#### Adding Enrollments ####");
        }

        foreach($result as $user) {
            if($user->Email !== "" && $user->Email !== NULL){
                $insertdata = new stdClass();
                $academicid = $user->PeriodoAcademicoId;
                if(!isset($academicdbycourseid[$user->SeccionId]) || empty($academicdbycourseid[$user->SeccionId])){
                    $insertdata->course = NULL;
                }else{
                    $insertdata->course = $academicdbycourseid[$user->SeccionId];
                }
                $insertdata->user = ($CFG->sync_emailexplode) ? explode("@", $user->Email)[0] : $user->Email;

                switch ($user->Tipo) {
                    case 'EditingTeacher':
                        $insertdata->role = $CFG->sync_teachername;
                        break;
                    case 'NonEditingTeacher':
                        $insertdata->role = $CFG->sync_noneditingteachername;
                        break;
                    case 'Student':
                        $insertdata->role = $CFG->sync_studentname;
                        break;
                    default:
                        $insertdata->role = $CFG->sync_studentname;
                        break;
                };

                if($insertdata->course != NULL){
                    $users[] = $insertdata;
                    $syncinfo[$academicid]["enrol"] += 1;
                    if ($options['debug']) {
                        mtrace("USER: ".$insertdata->user." TYPE: ".$insertdata->role." COURSE: ".$insertdata->course);
                    }
                }

                $generalcoursedata = new stdClass();
                $generalcoursedata->course = ($insertdata->role == $CFG->sync_teachername) ? $academicid."-PROFESORES" : $academicid."-ALUMNOS";
                $generalcoursedata->user = $insertdata->user;
                $generalcoursedata->role = $CFG->sync_studentname;

                if($insertdata->role != $CFG->sync_noneditingteachername){
                    if(!in_array($generalcoursedata, $metausers)) {
                        $metausers[] = $generalcoursedata;
                        $syncinfo[$academicid]["enrol"] += 1;
                        if ($options['debug']) {
                            mtrace("USER: ".$insertdata->user." TYPE: ".$generalcoursedata->role." COURSE: ".$generalcoursedata->course);
                        }
                    }
                }
            }elseif ($options['debug']){
                mtrace("Skipping empty..");
            }
        }
    } else {
        mtrace ("No users obtained");
    }

	return array($users,$metausers, $syncinfo);
}

function sync_getcourses_fromomega($academicids, $syncinfo, $options = null){
	global $CFG;

	$registros = 0;
	$url = $CFG->sync_urlgetcursos;
	$token = $CFG->sync_token;
    $fields = array(
			"token" => $token,
			"PeriodosAcademicos" => array($academicids)
	);
    $result = array();

    mtrace("\n\n## Obteniendo listado de cursos desde Omega {$url} ##\n");
	for ($i = 1; $i<=3; $i++){

        mtrace("Intento de comunicacion {$i}");
        $registros = 0;

        try {

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($fields));
            curl_setopt($curl, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            curl_setopt($curl, CURLOPT_TIMEOUT, 200);

            mtrace("## conexion a servicios Omega Iniciada ##");
            $result = json_decode(curl_exec($curl));
            mtrace("## conexion a servicios Omega Completada ##");

            if(curl_errno($curl)){
                mtrace("## Error CURL ##");
                throw new Exception(curl_error($curl));
            }

            curl_close($curl);
            $registros = count($result);

        } catch (Exception $e) {
            mtrace('Excepción capturada: ',  $e->getMessage());
        }

        if ($registros > 0) break;
    }

	if ($options) {
		mtrace("#### Adding Courses ####");
	}

    $courses = array();
	if ($registros > 0) {

        foreach($result as $course) {
            $insertdata = new stdClass();
            $insertdata->dataid = $syncinfo[$course->PeriodoAcademicoId]["dataid"];
            // Format ISO-8859-1 Fullname
            $insertdata->fullname = $course->FullName;
            // Validate encode Fullname
            //mtrace(mb_detect_encoding($course->FullName,"ISO-8859-1, GBK, UTF-8"));
            $insertdata->shortname = $course->ShortName;
            $insertdata->idnumber = $course->SeccionId;
            $insertdata->categoryid = $syncinfo[$course->PeriodoAcademicoId]["categoryid"];
            if($insertdata->fullname != NULL && $insertdata->shortname != NULL && $insertdata->idnumber != NULL){
                $courses[] = $insertdata;
                $syncinfo[$course->PeriodoAcademicoId]["course"] += 1;
                if ($options['debug']) {
                    mtrace("COURSE: ".$insertdata->shortname." IDNUMBER: ".$insertdata->idnumber." CATEGORY: ".$insertdata->categoryid);
                }
            }
        }

        // Build the academic period's general students course
        $studentscourse = new StdClass();
        $studentscourse->dataid = $syncinfo[$academicids]["dataid"];
        $studentscourse->fullname = "Alumnos ".$syncinfo[$academicids]["periodname"];
        $studentscourse->shortname = $academicids."-ALUMNOS";
        $studentscourse->idnumber = NULL;
        $studentscourse->categoryid = $syncinfo[$academicids]["categoryid"];

        // Build the academic period's general teachers course
        $teacherscourse = new StdClass();
        $teacherscourse->dataid = $syncinfo[$academicids]["dataid"];
        $teacherscourse->fullname = "Profesores ".$syncinfo[$academicids]["periodname"];
        $teacherscourse->shortname = $academicids."-PROFESORES";
        $teacherscourse->idnumber = NULL;
        $teacherscourse->categoryid = $syncinfo[$academicids]["categoryid"];
        if ($options['debug']) {
            mtrace("COURSE: ".$studentscourse->shortname." CATEGORY: ".$studentscourse->categoryid);
            mtrace("COURSE: ".$teacherscourse->shortname." CATEGORY: ".$teacherscourse->categoryid);
        }
        $courses[] = $studentscourse;
        $syncinfo[$course->PeriodoAcademicoId]["course"] += 1;
        $courses[] = $teacherscourse;
        $syncinfo[$course->PeriodoAcademicoId]["course"] += 1;

    } else {
	    mtrace("No courses obtained");
    }

	return array($courses, $syncinfo);
}

function getacademicperiods ($options = null, $status = 1) {
    global $DB;

    $academicperiodid = $options['academicperiodid'];
    $currentstatus = SYNC_STATUS_ACTIVE;
    if ($status == 0) $currentstatus = SYNC_STATUS_INACTIVE;

    // Get all ID from each academic period
    mtrace ("Get all academic period id with status {$status}");
    if ($academicperiodid > 0) $periods = $DB->get_records("sync_data", array("status" => $currentstatus, "academicperiodid" => $academicperiodid));
    else $periods = $DB->get_records("sync_data", array("status" => $currentstatus));

    return $periods;

}

function sync_getacademicperiod($options = null){
	global $DB;

	$periods = getacademicperiods($options, 1);

	mtrace("Academic Period to synchronize \n");
	$academicids = array();
	$syncinfo = array();
	if(count($periods) > 0){
		foreach($periods as $period) {
			$academicids[] = $period->academicperiodid;
			$syncinfo[$period->academicperiodid] = array(
					"dataid" => $period->id,
					"course" => 0,
					"enrol" => 0,
					"categoryid" => $period->categoryid,
					"periodname" => $period->academicperiodname
			);
			mtrace("ID: ".$period->academicperiodid." NAME: ".$period->academicperiodname." CATEGORY: ".$period->categoryid." \n");
		}
		return array($academicids, $syncinfo);
	}else{
		return array(FALSE, FALSE);
	}
}

function sync_getacademicbycourseids($coursesids){
	global $DB;
	$shortnamebycourseid = array();
		if(!empty($coursesids)){
		// get_in_or_equal used after in the IN ('') clause of multiple querys
		list($sqlin, $param) = $DB->get_in_or_equal($coursesids);	
		$sqlgetacademic = "SELECT c.id, 
				c.shortname, 
				c.idnumber, 
				s.academicperiodid
				FROM {sync_course} AS c INNER JOIN {sync_data} AS s ON (c.dataid = s.id)
				WHERE c. idnumber $sqlin";
		$academicinfo = $DB->get_records_sql($sqlgetacademic, $param);
		// Check the version to use the corrects functions
		if(PHP_MAJOR_VERSION < 7){
			
			foreach ($academicinfo as $academic){
				$shortnamebycourseid[$academic->idnumber] = $academic->shortname;
			}
		}else{
			$shortnamebycourseid = array_column($academicinfo, 'shortname', 'idnumber');
		}
	}
	return $shortnamebycourseid;
}

function sync_getacademicperiodids_fromomega() {
    global $CFG;

    $curl = curl_init();
    $url = $CFG->sync_urlgetacademicperiods;
    $token = $CFG->sync_token;
    $fields = array(
        "token" => $token
    );
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_POST, TRUE);
    curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($fields));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    $result = json_decode(curl_exec($curl));
    curl_close($curl);

    return $result;
}

function sync_getacademicperiodids_fromomega_toarray ()
{
    global $CFG;

    $periods = sync_getacademicperiodids_fromomega();
    $academicids = array();
    if (count($periods) > 0) {
        foreach ($periods as $period) {
            $periodrow = array();
            array_push($periodrow, $period->periodoAcademicoId);
            array_push($periodrow, $period->unidadAcademicaId);
            array_push($periodrow, $period->periodoAcademico);
            array_push($periodrow, $period->sede);
            array_push($periodrow, $period->estado);
            array_push($periodrow, $period->AnoPeriodo);
            array_push($periodrow, $period->NumeroPeriodo);
            array_push($periodrow, $period->tipo);

            array_push($academicids, $periodrow);
        }

    }
    return $academicids;
}

function sync_tabs() {
	$tabs = array();
	// Create sync
	$tabs[] = new tabobject(
			"create",
			new moodle_url("/local/sync/create.php"),
			get_string("create", "local_sync")
	);
	// Records.
	$tabs[] = new tabobject(
			"record",
			new moodle_url("/local/sync/record.php"),
			get_string("record", "local_sync")
	);
	// History
	$tabs[] = new tabobject(
			"history",
			new moodle_url("/local/sync/history.php"),
			get_string("history", "local_sync")
	);
	return $tabs;
}

function sync_delete_enrolments($enrol, $categoryid){
	global $DB, $OUTPUT;
	
	$success = false;
	$message = "";
	
	if($enrol == "manual" || $enrol == "self") {
		$sql = "SELECT ue.id
				FROM {user_enrolments} AS ue
				INNER JOIN {enrol} AS e ON (e.id = ue.enrolid AND e.enrol = ?)
				INNER JOIN {course} AS c ON (c.id = e.courseid)
				INNER JOIN {course_categories} AS cc ON (cc.id = c.category AND cc.id = ?)";
		$todelete = $DB->get_records_sql($sql, array($enrol, $categoryid));
		
		$userenrolmentsid = array();
		foreach($todelete as $idtodelete){
			$userenrolmentsid[] = $idtodelete->id;
		}
		
		if (!empty($userenrolmentsid)){
			list($sqlin, $param) = $DB->get_in_or_equal($userenrolmentsid);
			$query = "DELETE
					FROM {user_enrolments}
					WHERE id $sqlin";
			
			if($DB->execute($query, $param)) {
				$success = true;
				$message .= $OUTPUT->notification(get_string("unenrol_success", "local_sync"), "notifysuccess");
			} else {
				$message .= $OUTPUT->notification(get_string("unenrol_fail", "local_sync"));
			}
		} else {
			$message .= $OUTPUT->notification(get_string("unenrol_empty", "local_sync"));
		}
	} else {
		$message .= $OUTPUT->notification(get_string("unenrol_fail", "local_sync"));
	}
	
	return array($success, $message);
}

function sync_deletecourses($syncid) {
	global $DB;
	
	$data = $DB->get_record("sync_data", array(
			"id" => $syncid
	));
	$categoryid = $data->categoryid;
	
	if($categoryid != 0) {
		return $DB->delete_records("course", array(
				"category" => $categoryid
		));
	} else {
		return false;
	}
}

function sync_validate_deletion($syncid) {
	global $OUTPUT, $DB;
	
	$capable = true;
	$message = "";
	
	if($syncdata = $DB->get_record("sync_data", array(
			"id" => $syncid
		))) {
		$categoryid = $syncdata->categoryid;	
		// Category without children
		if($DB->record_exists("course_categories", array(
				"parent" => $categoryid
		))) {
			$capable = false;
			$message .= $OUTPUT->notification(get_string("category_haschildren", "local_sync"));
		} else {
			// Course without users
			$enrolmentssql = "SELECT ue.id,
					COUNT(ue.id) AS instances,
					sd.academicperiodid AS periodid,
					sd.academicperiodname AS periodname,
					c.fullname AS coursefullname,
					c.shortname AS courseshortname
					FROM {sync_data} AS sd
					INNER JOIN {course} AS c ON (sd.categoryid = c.category AND c.category = ?)
					INNER JOIN {enrol} AS e ON (c.id = e.courseid)
					INNER JOIN {user_enrolments} AS ue ON (e.id = ue.enrolid)
					GROUP BY c.id";
			
			$enrolmentsparams = array($categoryid);
			// Course without modules
			$modulessql = "SELECT cm.id,
					COUNT(cm.id) AS instances,
					sd.academicperiodid AS periodid,
					sd.academicperiodname AS periodname,
					c.fullname AS coursefullname,
					c.shortname AS courseshortname
					FROM {sync_data} AS sd
					INNER JOIN {course} AS c ON (sd.categoryid = c.category AND c.category = ?)
					INNER JOIN {course_modules} AS cm ON (c.id = cm.course)
					INNER JOIN {modules} AS m ON (m.id = cm.module AND m.name <> ?)
					GROUP BY c.id";
			$modulesparams = array($categoryid, MODULE_FORUM);
			
			$enrolments = $DB->get_records_sql($enrolmentssql, $enrolmentsparams);
			$modules = $DB->get_records_sql($modulessql, $modulesparams);
			
			if(!empty($enrolments)) {
				$capable = false;
				foreach($enrolments as $enrolment) {
					$message .= $OUTPUT->notification(
							get_string("courses_delete_description", "local_sync").
							$enrolment->periodname.
							"' (ID: ".
							$enrolment->periodid.
							get_string("courses_delete_cause", "local_sync").
							$enrolment->coursefullname.
							get_string("courses_delete_shortname", "local_sync").
							$enrolment->courseshortname.
							get_string("courses_delete_has", "local_sync").
							$enrolment->instances.
							get_string("courses_delete_enroled", "local_sync")
					);
				}
			} else {
				$message .= $OUTPUT->notification(get_string("courses_enroled_success", "local_sync"), "notifysuccess");
			}
			
			if(!empty($modules)) {
				$capable = false;
				foreach($modules as $module) {
					$message .= $OUTPUT->notification(
							get_string("courses_delete_description", "local_sync").
							$module->periodname.
							"' (ID: ".
							$module->periodid.
							get_string("courses_delete_cause", "local_sync").
							$module->coursefullname.
							get_string("courses_delete_shortname", "local_sync").
							$module->courseshortname.
							get_string("courses_delete_has", "local_sync").
							$module->instances.
							get_string("courses_delete_modules", "local_sync")
					);
				}
			} else {
				$message .= $OUTPUT->notification(get_string("courses_modules_success", "local_sync"), "notifysuccess");
			}
		}
	} else {
		$capable = false;
		$message .= $OUTPUT->notification(get_string("courses_missingid", "local_sync"));
	}		
	return array($capable, $message);
}

function sync_records_tabs() {
	$tabs = array();
	// Active
	$tabs[] = new tabobject(
			"active",
			new moodle_url("/local/sync/record.php", array(
					"view" => "active"
			)),
			get_string("active", "local_sync")
	);	
	// Inactive
	$tabs[] = new tabobject(
			"inactive",
			new moodle_url("/local/sync/record.php", array(
					"view" => "inactive"
			)),
			get_string("inactive", "local_sync")
	);	
	return $tabs;
}

function sync_sendmail($userlist, $syncfail, $fixedcourses, $error, $type = 0) {
    GLOBAL $CFG, $USER, $DB;
    $userfrom = core_user::get_noreply_user();
    $userfrom->maildisplay = true;
	
	foreach ($userlist as $user){
        $eventdata = new \core\message\message();

        //subject
        $eventdata->subject = "Get academic period sync error";
        if ($type == 0) {

            $messagehtml = "<html>".
                "<p>Estimado: usuario,</p>".
                "<p>Se ha completado la tarea de sincronización: " . date('d/m/Y h:i:s a', time()). "</p>".
                "<p><b>Errores de Sincronización:</b></p>".
                "<p>#DATAHERE#</p>".
                "<p><b>Corrección de Cursos:</b></p>".
                "<p>#DATACOURSES#</p>".
                "<p>Atentamente,</p>".
                "<p>Equipo de WebCursos</p>".
                "</html>";

            $messagetext = "<p>Estimado: usuario,</p>".
                "<p>Se ha completado la tarea de sincronización: " . date('d/m/Y h:i:s a', time()). "</p>".
                "<p><b>Errores de Sincronización:</b></p>".
                "<p>#DATAHERE#</p>".
                "<p><b>Corrección de Cursos:</b></p>".
                "<p>#DATACOURSES#</p>".
                "<p>Atentamente,</p>".
                "<p>Equipo de WebCursos</p>";

            if ($error == 1) {
                $messagehtml = str_replace("#DATAHERE#", sync_htmldata($syncfail), $messagehtml);
                $messagetext = str_replace("#DATAHERE#", sync_htmldata($syncfail), $messagetext);
            }
            else {
                $messagehtml = str_replace("#DATAHERE#", "", $messagehtml);
                $messagetext = str_replace("#DATAHERE#", "", $messagetext);
            }

            if (count($fixedcourses[0]) > 0) {
                $messagehtml = str_replace("#DATACOURSES#", sync_htmldatacourses($fixedcourses), $messagehtml);
                $messagetext = str_replace("#DATACOURSES#", sync_htmldatacourses($fixedcourses), $messagetext);
            }
            else {
                $messagehtml = str_replace("#DATACOURSES#", "", $messagehtml);
                $messagetext = str_replace("#DATACOURSES#", "", $messagetext);
            }
        } else {

            $messagehtml = "<html>".
                "<p>Estimado: usuario,</p>".
                "<p>Se ha cancelado la tarea de sincronización: " . date('d/m/Y h:i:s a', time()). "</p>".
                "<p><b>No se detectaron los servicios de Omega activos.</b></p>".
                "<p>Atentamente,</p>".
                "<p>Equipo de WebCursos</p>".
                "</html>";

            $messagetext = "<p>Estimado: usuario,</p>".
                "<p>Se ha completado la tarea de sincronización: " . date('d/m/Y h:i:s a', time()). "</p>".
                "<p><b>No se detectaron los servicios de Omega activos.</b></p>".
                "<p>Atentamente,</p>".
                "<p>Equipo de WebCursos</p>";

        }

        $eventdata->component = "local_sync"; // your component name
        $eventdata->name = "sync_notification"; // this is the message name from messages.php
        $eventdata->userfrom = $userfrom;
        $eventdata->userto = $user;
        $eventdata->subject = "Sync Notification";
        $eventdata->fullmessage = $messagetext;
        $eventdata->fullmessageformat = FORMAT_HTML;
        $eventdata->fullmessagehtml = $messagehtml;
        $eventdata->smallmessage = "Sync Notification";
        $eventdata->notification = 1; // this is only set to 0 for personal messages between users

        $eventdata->contexturl = 'http://www.webcursos.uai.cl';
        $eventdata->contexturlname = 'Context name';
        //$eventdata->replyto = $mailto;
        $content = array('*' => array('header' => ' ', 'footer' => ' This is an automated message. Do not reply. ')); // Extra content for specific processor
        $eventdata->set_additional_content('email', $content);

        $eventdata->courseid = 1;//the course id was needed according to the documentation.
        //print_r($eventdata);
        $messageid = message_send($eventdata);
    }
}

function sync_htmldata ($syncFail) {
    $table = "";

    if (count($syncFail) > 0) {
        foreach ($syncFail as $fails) {
            $table .= "<p><b>Periodo Académico:</b> {$fails[0]} - <b>Cursos Sincronizados:</b> {$fails[1]} - <b>Enrols Totales:</b> {$fails[2]}</p>";
        }
    }

    return $table;
}

function sync_htmldatacourses ($fixedcourses) {
    $table = "";
    if (count($fixedcourses[0]) > 0) {
        foreach ($fixedcourses[0] as $course) {
            $fix = "No";
            if ($course->fixed > 0) $fix = "Sí";
            $table .= "<p><b>Id:</b> {$course->id} - <b>Shortname:</b> {$course->syncshortname} - <b>Fullname:</b> {$course->syncfullname} - <b>Result:</b> {$fix}</p>";
        }
    }

    return $table;
}

/* Deprecated */
function validateEmarkingError () {

    global $OUTPUT, $DB;

    mtrace("\n\n## Validando errores de Emarking ##\n");
    $moduleid = 0;

    $sqlmodule = "select id from {modules} where name = ?";
    $modules = $DB->get_records_sql($sqlmodule, array('emarking'));
    foreach($modules as $module){
        $moduleid = $module->id;
    }
    //mtrace ("moduleid = " . $moduleid);

    $sql = "SELECT c.id, c.shortname, ema.name, from_unixtime(ema.timecreated) AS fechacreacion, ct.id AS contextid, ct.instanceid, ema.type
    FROM {context} AS ct
    INNER JOIN {course_modules} AS cm ON (cm.id = ct.instanceid AND module=?)
    INNER JOIN {modules} AS m ON (m.id=cm.module)
    INNER JOIN {course} AS c ON (c.id = cm.course)
    INNER JOIN {emarking} AS ema ON (ema.id = cm.instance)
    WHERE
        ct.contextlevel=?
        AND NOT EXISTS (
            SELECT 1 FROM {grading_areas} x
            WHERE
                x.contextid = ct.id
                AND x.component = 'mod_emarking'
                AND x.areaname = 'attempt'
        )
        AND EXISTS (
            SELECT 1 FROM {sync_enrol}
            WHERE course = c.shortname
        )
        AND ema.type != ?
    ORDER BY c.shortname";

    //mtrace ($sql);
    $result = $DB->get_records_sql($sql, array($moduleid, 70, 0));

    $courseproblems = array();
    if(count($result) > 0){
        foreach($result as $res) {
            array_push($courseproblems,array($res->id, $res->shortname, $res->name, $res->fechacreacion, $res->contextid, $res->instanceid, $res->type));
        }
    }

    return $courseproblems;

}

function sync_omega ($options = null) {

    $result = true;
    $error = 0;

    sync_clear_academic_periods ($options); // clear tables from desactivated academic periods
    if (count(sync_getacademicperiodids_fromomega()) == 0) {
        sync_generate_mail($options, null, null, 1, 1);
        return 1; // Omega Services not working - abort
    }
    $syncinfo = sync_sincronize_current_periods ($options);
    $syncfail = sync_get_failed_periods ($syncinfo, $options);
    if (count($syncfail) > 0) $error = 1;

    // Validate emarking grading methods error (Deprecated - Fixed on emarking plugin)

    // Fix courses fullname and shortname
    $fixedcourses = sync_fix_created_courses($options);

    sync_generate_mail($options, $syncfail, $fixedcourses, $error);
    return $error;

}

function sync_clear_academic_periods ($options) {
    // Get all ID from each academic period with status is inactive
    $periods = getacademicperiods ($options, 0);
    $academicids = getacademicperiodsid($periods);

    // clear sync tables from inactive academicids
    sync_clear_academicid_tables ($academicids, $options);
}

function getacademicperiodsid ($periods) {
    $academicids = array();
    if(count($periods) > 0){
        foreach($periods as $period) {
            $academicids[] = $period->academicperiodid;
        }
        return $academicids;
    }else{
        return false;
    }
}

function sync_clear_academicid_tables ($academicids, $options) {
    if ($academicids) {
        foreach ($academicids as $academicid) {
            sync_delete_course_table ($academicid, $options);
            sync_delete_users_table ($academicid, $options);
        }
    }
}

function sync_delete_course_table ($academicid, $options) {
    global $DB;

    // Clean table courses from the current academic period
    if ($options['debug'] == true) mtrace("Borrando cursos de la bdd externa");
    if (!$DB->execute("DELETE FROM {sync_course} where shortname like '" . $academicid . "-%'")) mtrace("DELETE Table sync_course academicperiodid = " . $academicid . ": Failed");
    else mtrace("DELETE Table sync_course academicperiodid = " . $academicid . ": Success");
}

function sync_delete_users_table ($academicid, $options) {
    global $DB;

    // Clean table Users from the current academic period
    if ($options['debug'] == true) mtrace("Borrando usuarios de la bdd externa");
    if (!$DB->execute("DELETE FROM {sync_enrol} where course like '" . $academicid . "-%'")) mtrace("DELETE Table sync_enrol academicperiodid = " . $academicid . ": Failed");
    else mtrace("DELETE Table sync_enrol academicperiodid = " . $academicid . ": Success");
}

function sync_truncate_course_table ($options) {
    global $DB;

    // Clean table courses from the current academic period
    if ($options['debug'] == true) mtrace("Borrando cursos de la bdd externa");
    if (!$DB->execute("TRUNCATE TABLE {sync_course}")) mtrace("Truncate Table sync_course Failed");
    else mtrace("Truncate Table sync_course Success");
}

function sync_truncate_users_table ($options) {
    global $DB;

    // Clean table Users from the current academic period
    if ($options['debug'] == true) mtrace("Borrando usuarios de la bdd externa");
    if (!$DB->execute("TRUNCATE TABLE {sync_enrol}")) mtrace("Truncate Table sync_enrol Failed");
    else mtrace("Truncate Table sync_enrol Success");
}

function get_period_fromomega_key ($academicid, $periodosfromomega) {

    $keyid = 0;
    // echo $academicid;
    // print_r($periodosfromomega);
    foreach ($periodosfromomega as $key => $period) {
        if ($period[0] == $academicid) $keyid = $key;
    }
    return $keyid;
}

function sync_sincronize_current_periods ($options) {
    global $DB, $CFG;

    $periodosfromomega = sync_getacademicperiodids_fromomega_toarray();
    list($academicids, $syncinfo) = sync_getacademicperiod($options['academicperiodid']);

    // Check we have
    if ($academicids) {

        foreach ($academicids as $academicid) {

            if ($options['debug'] == true) mtrace("\n\nSincronizando periodo academico: {$academicid} - " . date("F j, Y, G:i:s"));
            $key = get_period_fromomega_key ($academicid, $periodosfromomega);
            mtrace ("The key is {$key}");

            if ($key > 0) $syncinfo[$academicid]["activeperiod"] = 1;
            else $syncinfo[$academicid]["activeperiod"] = 0;
            $syncinfo[$academicid]["error"] = 0;

            // ******************* get courses from omega ************************
            list($courses, $syncinfo) = sync_getcourses_fromomega($academicid, $syncinfo, $options["debug"]);
            if (count($courses) > 0) {
                sync_delete_course_table ($academicid, $options);
                mtrace ("Inserting courses into courses table");
                $DB->insert_records("sync_course", $courses);
            } else {
                // **************** add validation *******************
                if ($syncinfo[$academicid]["activeperiod"] == 1) $syncinfo[$academicid]["error"] = 1;
            }

            // ********************* Get users from omega **********************
            list($users, $metausers, $syncinfo) = sync_getusers_fromomega($academicid, $syncinfo, $options["debug"]);
            if ($users > 0) {
                sync_delete_users_table ($academicid, $options);
                mtrace ("Inserting users into enrol table");
                $DB->insert_records("sync_enrol", $users); // Insert users into enrol table
                mtrace ("Users inserted");

                mtrace ("Inserting metausers into courses table");
                $DB->insert_records("sync_enrol", $metausers); // Insert meta-users into enrol table
                mtrace ("users inserted");
            } else {
                // **************** add validation *******************
                if ($syncinfo[$academicid]["activeperiod"] == 1) $syncinfo[$academicid]["error"] = 1;
            }
        }

        // print_r($syncinfo);
        // *************************** Sync History table *************************
        sync_add_to_history ($syncinfo, $options);


    }
    else {
        mtrace("No se encontraron Periodos académicos activos para sincronizar.");

        if ($options['academicperiodid'] > 0) {
            sync_delete_course_table ($options['academicperiodid'], $options); // Delete Only the param academic period
            sync_delete_users_table ($options['academicperiodid'], $options); // Delete previous enrol
        }
        else {
            sync_truncate_course_table($options);
            sync_truncate_users_table ($options);
        }
    }

    return ($syncinfo);

}

function sync_add_to_history ($syncinfo, $options) {
    global $DB;

    // insert records in sync_history
    $historyrecords = array();  // history records array
    $time = time();
    foreach ($syncinfo as $academic => $rowinfo) {

        // record current row
        $insert = new stdClass();
        $insert->dataid = $rowinfo["dataid"];
        $insert->executiondate = $time;
        $insert->countcourses = $rowinfo["course"];
        $insert->countenrols = $rowinfo["enrol"];

        if ($academic > 0) $historyrecords[] = $insert; // add current record to history record array

        if ($options["debug"]) mtrace("Academic Period ".$academic.", Total courses ".$rowinfo["course"].", Total enrol ".$rowinfo["enrol"]."\n");
    }

    // save history records array into sync history table
    $DB->insert_records("sync_history", $historyrecords);
}

function sync_get_failed_periods ($syncinfo, $options) {
    global $DB;

    $syncfail = array(); // sync fail array
    foreach ($syncinfo as $academic => $rowinfo) {
        //if (($academic > 0) && ($rowinfo["course"] == 0 || $rowinfo["enrol"] == 0)) {
        if ($academic > 0 && $rowinfo["error"] == 1) {
            array_push($syncfail,array($academic, $rowinfo["course"], $rowinfo["enrol"]));
        }
    }
    return $syncfail;
}

function sync_get_users_email_list () {
    global $DB, $CFG;

    $userlist = array();
    $mails = explode("," ,$CFG->sync_mailalert);
    foreach ($mails as $mail) {
        $sqlmail = "Select id From {user} where username = ?";
        $usercfg = $DB->get_records_sql($sqlmail,array($mail));
        foreach ($usercfg as $user) {
            array_push($userlist, $user->id);
        }
    }

    return $userlist;
}

function sync_fix_created_courses($options) {
    $fixedcourses = sync_fix_courses($options);
    return array($fixedcourses);
}

function sync_fix_courses($options) {
    global $DB, $CFG;

    $errorlist = sync_get_courses_to_fix($options);
    if (count($errorlist) > 0) {
        $errorlist = sync_fix_courses_update($errorlist, $options);
    }
    else {
        mtrace ("No course problems detected");
    }
    return $errorlist;
}

function sync_get_courses_to_fix($options) {
    global $DB;

    $sql = "select s.shortname as syncshortname, s.fullname as syncfullname, c.id, c.shortname as courseshortname, c.fullname as coursefullname
    from mdl_sync_course s
    join mdl_course c on c.idnumber = s.idnumber AND (c.shortname != s.shortname OR s.fullname != c.fullname)";
    $regs = $DB->get_records_sql($sql);

    return $regs;
}

function sync_fix_courses_update($errorlist, $options) {
    global $DB;

    mtrace ("Fixing courses names and shortnames");
    foreach ($errorlist as $coursetofix) {
        // Get course info to fix
        $course = $DB->get_records("course", array("id" => $coursetofix->id));
        $course = $course[$coursetofix->id];

        // Validating and changing shortname and fullname if necesary
        if ($course->shortname != $coursetofix->syncshortname) $course->shortname = $coursetofix->syncshortname;
        if ($course->fullname != $coursetofix->syncfullname) $course->fullname = $coursetofix->syncfullname;
        try {
            mtrace ("Fixing course {$coursetofix->id} with shortname: {$coursetofix->syncshortname} and fullname: {$coursetofix->syncfullname}");
            update_course($course); // course/lib.php // Execute update
            mtrace ("Fix completed");
        } catch (Exception $e) {
                mtrace("Excepción capturada: {$e->getMessage()}");
        }


        // Validate change
        $course = $DB->get_records("course", array("id" => $coursetofix->id));
        $course = $course[$coursetofix->id];

        if ($course->shortname == $coursetofix->syncshortname && $course->fullname == $coursetofix->syncfullname) $coursetofix->fixed = 1;
        else $coursetofix->fixed = 0;
    }
    mtrace ("End Fixing courses names and shortnames");

    return $errorlist;

}

function sync_generate_mail($options, $syncfail, $fixedcourses, $error, $type = 0) {
    mtrace("Enviando correos a usuarios");
    // Add Script to get list o users who will receive the mail
    $userlist = sync_get_users_email_list();
    sync_sendmail($userlist, $syncfail, $fixedcourses, $error, $type);
}
<?php
/**
 * Strings for component "sync", language "es"
 *
 * @package	sync
 */

defined("MOODLE_INTERNAL") || die();

$string["pluginname"] = "Sincronizaciones Omega";
$string["sync_title"] = "Sincronización";
$string["sync_subtitle"] = "Creación de Sincronización";
$string["sync_page"] = "Sincronización";
$string["sync_heading"] ="Sincronización";
$string["sync_sub_heading"] ="Crear Sincronización";
$string["omega"] = "Período Académico";
$string["webc"] = "Categorías";
$string["in_charge"] = "Responsable";
$string["in_charge_help"] = "El responsable será notificado cuando la sincronización se complete. Nótese que este campo es opcional, pero se recomienda llenarlo
		al ser una manera fácil para rastear su éxito. Ej: usuario@uai.cl";
$string["in_charge_default"] ="Agregue un responsable";
$string["buttons"] = "Crear";
$string["optional"]= "Si busca un período distinto por favor haga";
$string["create"] = "Crear Sincronización";
$string["record"] = "Registros";
$string["error_period"] = "Seleccione un período académico a sincronizar";
$string["error_period_active"] = "El período académico seleccionado ya está guardado en una sincronización activa";
$string["error_period_inactive"] = "El período académico seleccionado ya está guardado en una sincronización inactiva";
$string["error_omega"] = "Seleccione una categoría";
$string["error_omega_active"] = "La categoría seleccionada ya está guardada en una sincronización activa";
$string["error_omega_inactive"] = "La categoría seleccionada ya está guardada en una sincronización inactiva";
$string["error_responsible_invalid"] = "Email inválido (debe ser @uai.cl)";
$string["error_responsible_nonexistent"] = "Email no existe en la base de datos";
$string["error_communication"] = "Falló la obtención de la lista de períodos académicos de Omega. Inténtelo más tarde.";
$string["sync_success"] = "Sincronización guardada satisfactoriamente";
$string["status"] = "Estado";
$string["active"] = "Activo";
$string["inactive"] = "Inactivo";
$string["task_courses"] = "Sincronización de cursos de Omega";
$string["h_title"] = "Sincronizaciones Omega";
$string["h_id"] = "ID";
$string["h_catid"] = "ID de la categoría";
$string["h_catname"] = "Nombre de la categoría";
$string["h_academicperiodid"] = "ID del periódo académico";
$string["h_academicperiodname"] = "Nombre del periódo académico";
$string["h_executiontime"] = "Hora de ejecución";
$string["h_synccourses"] = "Cursos sincronizados";
$string["h_syncenrols"] = "Matriculados sincronizados";
$string["h_emptytable"] = "La tabla está vacía";
$string["h_tabletitle"] = "Historial de Sincronizaciones";
$string["history"] = "Historial";
$string["omega_default"] = "Seleccione un período...";
$string["webc_default"] = "Seleccione una categoría...";
$string["timecreated"]="Fecha de Creación";
$string["academicperiod"] = "Período Académico";
$string["periodid"] = "ID Período";
$string["category"]	= "Categoría";
$string["categoryid"] = "ID Categoría";
$string["sede"] = "Sede";
$string["activation"] = "Activar/Desactivar";
$string["manualunsub"] = "Eliminar matriculas manuales";
$string["selfunsub"]="Eliminar auto-matriculados";
$string["edit"] = "Editar";
$string["deletesync"] = "Esta sincronización será borrada permanentemente ¿Desea continuar?";
$string["syncrecordtitle"] = "Historial De Sincronizaciones";
$string["synctable"] = "Historial";
$string["errorperiod"] = "Error";
$string["editform"] = "Esta sincronización será editada ¿Desea continuar?";
$string["buttonedit"] = "Guardar Cambios";
$string["syncdoesnotexist"] = "Por favor seleccione una sincronización";
$string["unenrol_success"] = "Usuarios desmatriculados exitosamente";
$string["unenrol_fail"] = "No se pudo desmatricular usuarios. Vuelva a intentarlo mas tarde.";
$string["unenrol_status"]="No se pudo desmatricular usuarios, la sincronización aún está activa.";
$string["activate"]="Activar";
$string["deactivate"]="Desactivar";
$string["unenrol"]="Desmatricular";


//Settings
$string["token"] = "Token Omega";
$string["tokendesc"] = "Token de autorización para Webapi Omega.";
$string["urlgetalumnos"] = "Url Servicio GetAlumnos";
$string["urlgetalumnosdesc"] = "Url de Webapi Omega para obtener los estudiantes y profesores a sincronizar.";
$string["urlgetcursos"] = "Url Servicio GetCursos";
$string["urlgetcursosdesc"] = "Url de Webapi Omega para obtener los cursos a sincronizar.";
$string["urlgetacademicperiods"] = "Url servicio GetPeriodosAcademicos";
$string["urlgetacademicperiodsdesc"] = "Url de Webapi Omega para obtener los ids de períodos académicos a sincronizar.";
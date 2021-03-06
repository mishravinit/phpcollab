<?php
/*
** Application name: phpCollab
** Last Edit page: 05/11/2004
** Path by root:  ../tasks/edittask.php
** Authors: Ceam / Fullo
**
** =============================================================================
**
**               phpCollab - Project Managment
**
** -----------------------------------------------------------------------------
** Please refer to license, copyright, and credits in README.TXT
**
** -----------------------------------------------------------------------------
** FILE: edittask.php
**
** DESC: Screen: edit task page
**
** HISTORY:
**  05/11/2004  -   fixed 1059973
**  12/01/2005  -   cleaned code
**  12/03/2005  -   fixed mssql bug for worked hours
**  19/05/2005  -   fixed and &amp; in link
**  22/05/2005  -   added subtask copy
**  25/04/2006  -   replaced JavaScript Calendar functions
** -----------------------------------------------------------------------------
** TO-DO:
** clean code
** =============================================================================
*/


$checkSession = "true";
include_once '../includes/library.php';

$tasks = new \phpCollab\Tasks\Tasks();

$id = $_GET["id"];
$action = $_GET["action"];

//case multiple edit tasks
$multi = strstr($_GET['id'], "**");

if ($multi != "") {
    phpCollab\Util::headerFunction("../tasks/updatetasks.php?report=" . $_GET['report'] . "&project=" . $_GET['project'] . "&id=" . $_GET['id'] . "");
}

if ($id != "" && $action != "update" && $action != "add") {
    $taskDetail = $tasks->getTaskById(filter_var($_GET['id'], FILTER_VALIDATE_INT));
    $project = $taskDetail['tas_project'];
} else {
    $project = $_GET['project'];
}

$projects = new \phpCollab\Projects\Projects();

$projectDetail = $projects->getProjectById($project);

$teamMember = "false";

$teams = new \phpCollab\Teams\Teams();
$phases = new \phpCollab\Phases\Phases();

$teamMembers = $teams->getTeamByProjectIdAndTeamMember($project, $idSession);

$comptMemberTest = count($teamMembers);

if ($comptMemberTest == "0") {
    $teamMember = "false";
} else {
    $teamMember = "true";
}

if ($teamMember == "false" && $profilSession != "5") {
    phpCollab\Util::headerFunction("../tasks/listtasks.php?project={$project}&msg=taskOwner");
}

//case update or copy task
if (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && $id != ""
    && !empty($_POST["task_name"])
) {
    $assignments = new \phpCollab\Assignments\Assignments();
    $files = new \phpCollab\Files\Files();

    //case update or copy task
    if ($action == "update") {
        //concat values from date selector and replace quotes by html code in name
        $task_name = phpCollab\Util::convertData($_POST["task_name"]);
        $d = phpCollab\Util::convertData($_POST["d"]);
        $comments = phpCollab\Util::convertData($_POST["comments"]);
        $taskStatus = $_POST['taskStatus'];
        $complete_date = $_POST["complete_date"];
        $old_st = $_POST["old_st"];
        $completion = $_POST["completion"] || 0;
        $invoicing = $_POST["invoicing"];
        $worked_hours = $_POST["worked_hours"];
        $at = $_POST["at"];
        $pr = $_POST["pr"];
        $start_date = $_POST["start_date"];
        $due_date = $_POST["due_date"];
        $etm = $_POST["etm"];
        $atm = $_POST["atm"];
        $old_project = $_POST["old_project"];

        if ($enableInvoicing == "true") {
            $invoices = new \phpCollab\Invoices\Invoices();
        }

            //case copy task
        if ($docopy == "true") {

            //Change task status if parent phase is suspended, complete or not open.
            if ($projectDetail['pro_phase_set'] != "0") {
                $currentPhase = $phases->getPhasesByProjectIdAndPhaseOrderNum($project, $phase);
                if ($taskStatus == 3 && $currentPhase['pha_status'] != 1) {
                    $taskStatus = 4;
                }
            }

            if ($completion == "10") {
                $taskStatus = "1";
            }

            if ($pub == "") {
                $pub = "1";
            }

            if ($invoicing == "") {
                $invoicing = "0";
            }

            if ($worked_hours == "") {
                $worked_hours = "0.00";
            }
            //Insert Task details with or without parent phase
            $dbParams = [];
            $dbParams['project_id'] = $project;
            $dbParams['task_name'] = $task_name;
            $dbParams['description'] = $d;
            $dbParams['owner'] = $idSession;
            $dbParams['assigned_to'] = $at;
            $dbParams['status'] = $taskStatus;
            $dbParams['priority'] = $pr;
            $dbParams['start_date'] = $start_date;
            $dbParams['due_date'] = $due_date;
            $dbParams['estimated_time'] = $etm;
            $dbParams['actual_time'] = $atm;
            $dbParams['comments'] = $comments;
            $dbParams['created'] = $dateheure;
            $dbParams['published'] = $pub;
            $dbParams['completion'] = $completion;
            $dbParams['parent_phase'] = ($phase != 0) ? $phase: 0;
            $dbParams['invoicing'] = $invoicing;
            $dbParams['worked_hours'] = $worked_hours;
            $dbParams['assigned'] = null;

            $num = $tasks->addTask($dbParams);

            unset($dbParams);


            $listSubTasks = $tasks->getSubtasksByParentTaskId($id);
            foreach ($listSubTasks as $subTask) {
                $dbParams = [];
                $dbParams['task'] = $num;
                $dbParams['name'] = $subTask["subtas_name"];
                $dbParams['description'] = $subTask["subtas_description"];
                $dbParams['owner'] = $subTask["subtas_owner"];
                $dbParams['assigned_to'] = $subTask["subtas_assigned_to"];
                $dbParams['status'] = $subTask["subtas_status"];
                $dbParams['priority'] = $subTask["subtas_priority"];
                $dbParams['start_date'] = $subTask["subtas_start_date"];
                $dbParams['due_date'] = $subTask["subtas_due_date"];
                $dbParams['completed_date'] = $subTask["subtas_complete_date"];
                $dbParams['estimated_time'] = $subTask["subtas_estimated_time"];
                $dbParams['actual_time'] = $subTask["subtas_actual_time"];
                $dbParams['comments'] = $subTask["comments"];
                $dbParams['created'] = $dateheure;
                $dbParams['assigned'] = $dateheure;
                $dbParams['published'] = $subTask["subtas_published"];
                $dbParams['completion'] = $subTask["subtas_completion"];

                $tasks->addSubTask($dbParams);
                unset($dbParams);
            }

            // invoice
            if ($enableInvoicing == "true") {
                if ($taskStatus == "1") {
                    $completeItem = "1";
                } else {
                    $completeItem = "0";
                }

                $detailInvoice = $invoices->getInvoicesByProjectId($project);
                if ($detailInvoice["inv_status"] == "0") {
                    $dbParams = [];
                    $dbParams['title'] = $task_name;
                    $dbParams['description'] = $d;
                    $dbParams['invoice_id'] = $detailInvoice['inv_id'];
                    $dbParams['created'] = $dateheure;
                    $dbParams['active'] = $invoicing;
                    $dbParams['completed'] = $completeItem;
                    $dbParams['mod_type'] = 1;
                    $dbParams['mod_value'] = $num;
                    $dbParams['worked_hours'] = $worked_hours;
                    $invoice_num = $invoices->addInvoiceItem($dbParams);

                    unset($dbParams);
                }
            }

            if ($taskStatus == "1" && $complete_date != "--") {
                $tasks->setCompletionDateForTaskById($num, $dateheure);
            }

            //if assigned_to not blank, set assigned date
            if ($at != "0") {
                $tasks->updateAssignedDate($num, $dateheure);
            }

            $assignmentId = $assignments->addAssignment($num, $idSession, $at, $dateheure);

            //if assigned_to not blank, add to team members (only if doesn't already exist)
            if ($at != "0") {
                $teamMember = $teams->isTeamMember($project, $at);

                if (!$teamMember) {
                    $teamMemberId = $teams->addTeam($project, $at, 1, 0);
                }

                //send task assignment mail if notifications = true
                if ($notifications == "true") {
                    include '../tasks/noti_taskassignment.php';
                }
            }

            //create task sub-folder if filemanagement = true
            if ($fileManagement == "true") {
                phpCollab\Util::createDirectory("files/$project/$num");
            }

            phpCollab\Util::headerFunction("../tasks/viewtask.php?id=$num&msg=addAssignment");

        } else {
        //case update task

            //Change task status if parent phase is suspended, complete or not open.
            if ($projectDetail['pro_phase_set'] != "0") {
                $currentPhase = $phases->getPhasesByProjectIdAndPhaseOrderNum($project, $phase);

                if ($taskStatus == 3 && $currentPhase['pha_status'] != 1) {
                    $taskStatus = 4;
                }
            }

            if ($pub == "") {
                $pub = "1";
            }
            if ($completion == "10") {
                $taskStatus = "1";
            }

            //recompute number of completed tasks of the project
            $projectDetail['pro_name'] = phpCollab\Util::projectComputeCompletion(
                $projectDetail,
                $tableCollab["projects"]
            );

            if ($invoicing == "") {
                $invoicing = "0";
            }

            //Update task with our without parent phase
            $tmpquery5 = <<<SQL
UPDATE {$tableCollab["tasks"]} SET 
name = :task_name, 
description = :description, 
assigned_to = :assigned_to, 
status = :status, 
priority = :priority, 
start_date = :start_date, 
due_date = :due_date, 
estimated_time = :estimated_time, 
actual_time = :actual_time, 
comments = :comments, 
modified = :modified, 
completion = :completion, 
parent_phase = :parent_phase, 
published = :published, 
invoicing = :invoicing,
worked_hours = :worked_hours 
WHERE id = :task_id
SQL;

//            $tmpquery5Params = [];
//            $tmpquery5Params['task_name'] = $task_name;
//            $tmpquery5Params['description'] = $d;
//            $tmpquery5Params['assigned_to'] = $at;
//            $tmpquery5Params['status'] = $taskStatus;
//            $tmpquery5Params['priority'] = $pr;
//            $tmpquery5Params['start_date'] = $start_date;
//            $tmpquery5Params['due_date'] = $due_date;
//            $tmpquery5Params['estimated_time'] = $etm;
//            $tmpquery5Params['actual_time'] = $atm;
//            $tmpquery5Params['comments'] = $comments;
//            $tmpquery5Params['modified'] = $dateheure;
//            $tmpquery5Params['completion'] = $completion;
//            $tmpquery5Params['parent_phase'] = ($phase != 0) ? $phase: 0;
//            $tmpquery5Params['published'] = $pub;
//            $tmpquery5Params['invoicing'] = $invoicing;
//            $tmpquery5Params['worked_hours'] = $worked_hours;
//            $tmpquery5Params['task_id'] = $id;



//            $tasks->setTaskName();
//            $tasks->setDescription();
//            $tasks->setAssignedTo();
//            $tasks->setStatus();
//            $tasks->setPriority();
//            $tasks->setStartDate();
//            $tasks->setDueDate();
//            $tasks->setEstimatedTime();
//            $tasks->setActualTime();
//            $tasks->setComments();
//            $tasks->setModifiedDate();
//            $tasks->setCompletion();
//            $tasks->setParentPhase();
//            $tasks->setPublished();
//            $tasks->setInvoicing();
//            $tasks->setWorkedHours();
//            $tasks->setTaskId();


            if ($taskStatus == "1" && $complete_date == "--") {
                $tasks->setCompletionDateForTaskById($id, $date);
            } else {
                $tasks->setCompletionDateForTaskById($id, $complete_date);
            }

            if ($old_st == "1" && $taskStatus != $old_st) {
                $tasks->setCompletionDateForTaskById($id, '');
            }

            //if project different from past value, set project number in tasks table
            if ($project != $old_project) {
                $tasks->setProjectByTaskId($project, $id);

                $files->setProjectByTaskId($project, $id);
                phpCollab\Util::createDirectory("files/$project/$id");

                $dir = opendir("../files/$old_project/$id");

                if (is_resource($dir)) {
                    while ($v = readdir($dir)) {
                        if ($v != '.' && $v != '..') {
                            copy("../files/$old_project/$id/" . $v, "../files/$project/$id/" . $v);
                            @unlink("../files/$old_project/$id/" . $v);
                        }
                    }
                }

                //recompute number of completed tasks of the old project
                $oldproject = $projects->getProjectById($old_project);

                phpCollab\Util::projectComputeCompletion(
                    $oldproject,
                    $tableCollab["projects"]
                );
            }

            if ($enableInvoicing == "true") {
                if ($taskStatus == "1") {
                    $completeItem = "1";
                } else {
                    $completeItem = "0";
                }

                $detailInvoice = $invoices->getInvoicesByProjectId($project);

                if ($detailInvoice["inv_status"] == "0") {
                    $invoiceItemsId = $invoices->updateInvoiceItems($invoicing, $completeItem, $worked_hours, $id);
                }
            }

            //if assigned_to not blank and past assigned value blank, set assigned date
            if ($at != "0" && $_POST["old_assigned"] == "") {
                $tasks->updateAssignedDate($id, $dateheure);
            }

            //if assigned_to different from past value, insert into assignment
            //add new assigned_to in team members (only if doesn't already exist)
            if ($at != $_POST["old_at"]) {
                $tmpquery2 = "INSERT INTO {$tableCollab["assignments"]} (task,owner,assigned_to,assigned) VALUES (:task_id,:owner_id,:assigned_to,:assigned)";
                $dbParams = [];
                $dbParams['task_id'] = $id;
                $dbParams['owner_id'] = $idSession;
                $dbParams['assigned_to'] = $at;
                $dbParams['assigned'] = $dateheure;
                phpCollab\Util::newConnectSql($tmpquery2, $dbParams);
                unset($dbParams);
                
                $tmpquery = "WHERE tea.project = '$project' AND tea.member = '$at'";
                $testinTeam = new phpCollab\Request();
                $testinTeam->openTeams($tmpquery);
                $comptTestinTeam = count($testinTeam->tea_id);

                if ($comptTestinTeam == "0") {
                    $tmpquery3 = "INSERT INTO {$tableCollab["teams"]} (project,member,published,authorized) VALUES (:project_id,:member_id,:published,:authorized)";
                    $dbParams = [];
                    $dbParams['project_id'] = $project;
                    $dbParams['member_id'] = $at;
                    $dbParams['published'] = 1;
                    $dbParams['authorized'] = 0;
                    phpCollab\Util::newConnectSql($tmpquery3, $dbParams);
                    unset($dbParams);
                }

                $msg = "updateAssignment";
//                phpCollab\Util::newConnectSql($tmpquery5, $tmpquery5Params);
//                $tmpquery5Params = [];
//                $tmpquery5Params['task_name'] = $task_name;
//                $tmpquery5Params['description'] = $d;
//                $tmpquery5Params['assigned_to'] = $at;
//                $tmpquery5Params['status'] = $taskStatus;
//                $tmpquery5Params['priority'] = $pr;
//                $tmpquery5Params['start_date'] = $start_date;
//                $tmpquery5Params['due_date'] = $due_date;
//                $tmpquery5Params['estimated_time'] = $etm;
//                $tmpquery5Params['actual_time'] = $atm;
//                $tmpquery5Params['comments'] = $comments;
//                $tmpquery5Params['modified'] = $dateheure;
//                $tmpquery5Params['completion'] = $completion;
//                $tmpquery5Params['parent_phase'] = ($phase != 0) ? $phase: 0;
//                $tmpquery5Params['published'] = $pub;
//                $tmpquery5Params['invoicing'] = $invoicing;
//                $tmpquery5Params['worked_hours'] = $worked_hours;
//                $tmpquery5Params['task_id'] = $id;

                // Todo: Finish and test this:
                // Refactoring the above code to call method
                $parentPhase = ($phase != 0) ? $phase: 0;
                $tasks->updateTask($task_name, $d, $at, $taskStatus, $priority, $start_date, $due_date, $etm, $atm, $comments, $dateheure, $completion, $parentPhase, $pub, $invoicing, $worked_hours, $id);

                // Todo: refactor PDO
                $tmpquery = "WHERE tas.id = '$id'";
                $taskDetail = new phpCollab\Request();
                $taskDetail->openTasks($tmpquery);

                //send task assignment mail if notifications = true
                if ($notifications == "true") {
                    include '../tasks/noti_taskassignment.php';
                }
            } else {
                $msg = "update";
                // Todo: refactor PDO
                $dbParams = [];
                $dbParams[''] = '';
                phpCollab\Util::newConnectSql($tmpquery5, $tmpquery5Params);
                $tmpquery = "WHERE tas.id = '$id'";
                $taskDetail = new phpCollab\Request();
                $taskDetail->openTasks($tmpquery);

                //send status task change mail if notifications = true
                if ($at != "0" && $taskStatus != $old_st) {
                    if ($notifications == "true") {
                        include '../tasks/noti_statustaskchange.php';
                    }
                }

                //send priority task change mail if notifications = true
                if ($at != "0" && $pr != $old_pr) {
                    if ($notifications == "true") {
                        include '../tasks/noti_prioritytaskchange.php';
                    }
                }

                //send due date task change mail if notifications = true
                if ($at != "0" && $due_date != $old_dd) {
                    if ($notifications == "true") {
                        include '../tasks/noti_duedatetaskchange.php';
                    }
                }
            }

            if ($taskStatus != $old_st) {
                $cUp .= "\n[status:$st]";
            }

            if ($pr != $old_pr) {
                $cUp .= "\n[priority:$pr]";
            }

            if ($due_date != $old_dd) {
                $cUp .= "\n[datedue:$due_date]";
            }

            if ($cUp != "" || $taskStatus != $old_st || $pr != $old_pr || $due_date != $old_dd) {
                $cUp = phpCollab\Util::convertData($cUp);
                $tmpquery6 = "INSERT INTO {$tableCollab["updates"]} (type,item,member,comments,created) VALUES (:type,:item,:member,:comments,:created)";
                $dbParams = [];
                $dbParams['type'] = 1;
                $dbParams['item'] = $id;
                $dbParams['member'] = $idSession;
                $dbParams['comments'] = $cUp;
                $dbParams['created'] = $dateheure;
                phpCollab\Util::newConnectSql($tmpquery6, $dbParams);
                unset($dbParams);
            }

            phpCollab\Util::headerFunction("../tasks/viewtask.php?id=$id&msg=$msg");
        }
    }

}
//case add task
if (
    $_SERVER['REQUEST_METHOD'] == 'POST'
    && $id == ""
    && !empty($_POST["task_name"])
    && !empty($_GET["action"])
) {
/**
 * ToDo: Isn't this logic basically repeated above for update/copy?  If so, refactor so it is only done once, then
 * use class methods based on the action flag
 **/

    //case add task
    if ($action == "add") {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['task_name'] != "") {
                $_POST['task_name'] = filter_var($_POST['task_name'], FILTER_SANITIZE_STRING);
                if ($_POST['task_name'] == "") {
                    $errors .= 'Please enter a valid task name.<br/><br/>';
                }
            } else {
                $errors .= 'Please enter a task name.<br/>';
            }

            $project_id = $_POST["project"];
            $task_name = $_POST["task_name"];
            $description = $_POST["d"];
            $assigned_to = $_POST["at"];
            $phase = $_POST["phase"];
            $task_status = $_POST["taskStatus"];
            $completion = $_POST["completion"] || 0;
            $priority = $_POST["pr"];
            $start_date = $_POST["start_date"];
            $due_date = $_POST["due_date"];
            $estimated_time = $_POST["etm"];
            $actual_time = $_POST["atm"];
            $comments = $_POST["comments"];
            $worked_hours = $_POST["worked_hours"];
            $published = $_POST["published"];
            $invoicing = $_POST["invoicing"];

            $_POST['name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
        }

        //Change task status if parent phase is suspended, complete or not open.
        if ($projectDetail['pro_phase_set'] == 1) {
            $currentPhase = $phases->getPhasesByProjectIdAndPhaseOrderNum($project, $phase);
            if ($taskStatus == 3 && $currentPhase['pha_status'] != 1) {
                $taskStatus = 4;
            }
        }

        if ($completion == "10") {
            $taskStatus = 1;
        }

        if ($published == "") {
            $published = 1;
        }

        if ($invoicing == "") {
            $invoicing = 0;
        }

        if ($worked_hours == "") {
            $worked_hours = "0.00";
        }

        $dbParams = [];
        $dbParams['project_id'] = $project;
        $dbParams['task_name'] = $task_name;
        $dbParams['description'] = $description;
        $dbParams['owner'] = $idSession;
        $dbParams['assigned_to'] = ($assigned_to != 0) ? $assigned_to : 0;
        $dbParams['status'] = $taskStatus;
        $dbParams['priority'] = $priority;
        $dbParams['start_date'] = $start_date;
        $dbParams['due_date'] = $due_date;
        $dbParams['estimated_time'] = $estimated_time;
        $dbParams['actual_time'] = $actual_time;
        $dbParams['comments'] = $comments;
        $dbParams['created'] = $dateheure;
        $dbParams['published'] = $published;
        $dbParams['completion'] = $completion;
        $dbParams['parent_phase'] = ($phase != 0) ? $phase: 0;
        $dbParams['invoicing'] = $invoicing;
        $dbParams['worked_hours'] = $worked_hours;

        //if assigned_to not blank, set assigned date
        if ($assigned_to != "0") {
            $dbParams['assigned'] = $dateheure;
        } else {
            $dbParams['assigned'] = null;
        }

        $newTaskId = $tasks->addTask($dbParams);

        if ($enableInvoicing == "true") {
            $invoices = new \phpCollab\Invoices\Invoices();

            if ($taskStatus == "1") {
                $completeItem = "1";
            } else {
                $completeItem = "0";
            }

            $detailInvoice = $invoices->getInvoicesByProjectId($project);

            if ($detailInvoice["inv_status"] == "0") {
                $dbParams = [];
                $dbParams['task_name'] = $task_name;
                $dbParams['description'] = $description;
                $dbParams['invoice_id'] = $detailInvoice['inv_id'];
                $dbParams['created'] = $dateheure;
                $dbParams['active'] = $invoicing;
                $dbParams['completed'] = $completeItem;
                $dbParams['mod_type'] = 1;
                $dbParams['mod_value'] = $newTaskId;
                $dbParams['worked_hours'] = $worked_hours;
                $invNum = $invoices->addInvoiceItem($dbParams);
                unset($dbParams);
            }
        }

        if ($taskStatus == "1") {
            $tasks->setCompletionDateForTaskById($newTaskId, $dateheure);
        }

        //recompute number of completed tasks of the project
        $projectDetail['pro_name'] = phpCollab\Util::projectComputeCompletion(
            $projectDetail,
            $tableCollab["projects"]
        );

        //if assigned_to not blank, set assigned date
        if ($at != "0") {
            $tasks->updateAssignedDate($newTaskId, $dateheure);
        }
        $tmpquery2 = "INSERT INTO {$tableCollab["assignments"]} (task,owner,assigned_to,assigned) VALUES(:task_id, :owner_id, :assigned_to, :assigned)";
        $dbParams = [];
        $dbParams['task_id'] = $newTaskId;
        $dbParams['owner_id'] = $idSession;
        $dbParams['assigned_to'] = $at;
        $dbParams['assigned'] = $dateheure;

        phpCollab\Util::newConnectSql($tmpquery2, $dbParams);
        unset($dbParams);

        //if assigned_to not blank, add to team members (only if doesn't already exist)
        //add assigned_to in team members (only if doesn't already exist)
        if ($at != "0") {
            $testinTeam = $teams->getTeamByProjectIdAndTeamMember($project, $at);
            $comptTestinTeam = count($testinTeam);

            if ($comptTestinTeam == "0") {
                $dbParams = [];
                $dbParams['project'] = $project;
                $dbParams['member'] = $at;
                $dbParams['published'] = 1;
                $dbParams['authorized'] = 0;
                $teams->addTeam($dbParams);
                unset($dbParams);
            }

            //send task assignment mail if notifications = true
            if ($notifications == "true") {
                include '../tasks/noti_taskassignment.php';
            }
        }

        //create task sub-folder if filemanagement = true
        if ($fileManagement == "true") {
            phpCollab\Util::createDirectory("files/$project/$newTaskId");
        }

        phpCollab\Util::headerFunction("../tasks/viewtask.php?id=$newTaskId&msg=addAssignment");
    }

    //set default values
    $taskDetail['tas_assigned_to'] = "0";
    $taskDetail['tas_priority'] = $projectDetail['pro_priority'];
    $taskDetail['tas_status'] = "2";
}

//set value in form
$task_name = $taskDetail['tas_name'];
$d = $taskDetail['tas_description'];
$start_date = $taskDetail['tas_start_date'];
$due_date = $taskDetail['tas_due_date'];
$complete_date = $taskDetail['tas_complete_date'];
$etm = $taskDetail['tas_estimated_time'];
$atm = $taskDetail['tas_actual_time'];
$comments = $taskDetail['tas_comments'];
$pub = $taskDetail['tas_published'];
$worked_hours = $taskDetail['tas_worked_hours'];

if ($pub == "0") {
    $checkedPub = "checked";
}


if ($projectDetail['pro_org_id'] == "1") {
    $projectDetail['pro_org_name'] = $strings["none"];
}


if ($projectDetail['pro_phase_set'] != "0") {
    if ($id != "") {
        $tPhase = $taskDetail['tas_parent_phase'];
        if (!$tPhase) {
            $tPhase = '0';
        }
        $project = $subtaskDetail['tas_project'];
    }

    if ($id == "") {
        $tPhase = $phase;
        if (!$tPhase) {
            $tPhase = '0';
        }
    }

    $targetPhase = $phases->getPhasesByProjectIdAndPhaseOrderNum($project, $tPhase);
}

$bodyCommand = "onload=\"document.etDForm.task_name.focus();\"";

$headBonus = "";
$includeCalendar = true; //Include Javascript files for the pop-up calendar
include APP_ROOT . '/themes/' . THEME . '/header.php';

$blockPage = new phpCollab\Block();
$blockPage->openBreadcrumbs();
$blockPage->itemBreadcrumbs($blockPage->buildLink("../projects/listprojects.php?", $strings["projects"], "in"));
$blockPage->itemBreadcrumbs($blockPage->buildLink("../projects/viewproject.php?id=" . $projectDetail['pro_id'], $projectDetail['pro_name'], "in"));
if ($projectDetail['pro_phase_set'] != "0") {
    $blockPage->itemBreadcrumbs($blockPage->buildLink("../phases/listphases.php?id=" . $projectDetail['pro_id'], $strings["phases"], "in"));
    $blockPage->itemBreadcrumbs($blockPage->buildLink("../phases/viewphase.php?id=" . $targetPhase["pha_id"], $targetPhase["pha_name"], "in"));
}

$blockPage->itemBreadcrumbs($blockPage->buildLink("../tasks/listtasks.php?project=" . $projectDetail['pro_id'], $strings["tasks"], "in"));

if ($id == "") {
    $blockPage->itemBreadcrumbs($strings["add_task"]);
}

if ($id != "") {
    $blockPage->itemBreadcrumbs($blockPage->buildLink("../tasks/viewtask.php?id=" . $taskDetail['tas_id'], $taskDetail['tas_name'], "in"));
    $blockPage->itemBreadcrumbs($strings["edit_task"]);
}

$blockPage->closeBreadcrumbs();

if ($msg != "") {
    include '../includes/messages.php';
    $blockPage->messageBox($msgLabel);
}

$block1 = new phpCollab\Block();


if ($id == "") {
    $block1->form = "etD";
    $block1->openForm("../tasks/edittask.php?project=$project&action=add&#" . $block1->form . "Anchor");
}

if ($id != "") {
    $block1->form = "etD";
    $block1->openForm("../tasks/edittask.php?project=$project&id=$id&action=update&docopy=$docopy&#" . $block1->form . "Anchor");
    echo <<<HIDDENFIELDS
<input type="hidden" name="old_at" value="{$taskDetail['tas_assigned_to']}">
<input type="hidden" name="old_assigned" value="{$taskDetail['tas_assigned']}">
<input type="hidden" name="old_pr" value="{$taskDetail['tas_priority']}">
<input type="hidden" name="old_st" value="{$taskDetail['tas_status']}">
<input type="hidden" name="old_dd" value="{$taskDetail['tas_due_date']}">
<input type="hidden" name="old_project" value="{$taskDetail['tas_project']}">
HIDDENFIELDS;
}

if (isset($error) && $error != "") {
    $block1->headingError($strings["errors"]);
    $block1->contentError($error);
}

if ($id == "") {
    $block1->heading($strings["add_task"]);
}

if ($id != "") {
    if ($docopy == "true") {
        $block1->heading($strings["copy_task"] . " : " . $taskDetail['tas_name']);
    } else {
        $block1->heading($strings["edit_task"] . " : " . $taskDetail['tas_name']);
    }
}

$block1->openContent();
$block1->contentTitle($strings["info"]);

echo <<< Project
    <tr class="odd">
    <td valign="top" class="leftvalue">{$strings["project"]} :</td>
    <td>
    <select name="project">
Project;

if ($projectsFilter == "true") {
    $listProjects = $projects->getFilteredProjectsByTeamMember($idSession);
} else {
    $listProjects = $projects->getAllProjects();
}

foreach ($listProjects as $proj) {
    if ($proj["pro_id"] == $projectDetail['pro_id']) {
        echo '<option value="' . $proj["pro_id"] . '" selected>' . $proj["pro_name"] . '</option>';
    } else {
        echo '<option value="' . $proj["pro_id"] . '">' . $proj["pro_name"] . '</option>';
    }
}
echo "</select></td></tr>";


//Display task's phase
if ($projectDetail['pro_phase_set'] != "0") {
    echo "<tr class='odd'><td valign='top' class='leftvalue'>" . $strings["phase"] . " :</td><td>" . $blockPage->buildLink("../phases/viewphase.php?id=" . $targetPhase["pha_id"], $targetPhase["pha_name"], "in") . "</td></tr>";
}
echo "<tr class='odd'><td valign='top' class='leftvalue'>" . $strings["organization"] . " :</td><td>" . $projectDetail['pro_org_name'] . "</td></tr>";

$block1->contentTitle($strings["details"]);

echo "<tr class='odd'><td valign='top' class='leftvalue'>" . $strings["name"] . " :</td><td><input size='44' value='";

if ($docopy == "true") {
    echo $strings["copy_of"];
}

echo "$task_name' style='width: 400px' name='task_name' maxlength='100' type='TEXT'></td>
        </tr>";
echo <<<Description
    <tr class="odd">
        <td valign="top" class="leftvalue">{$strings["description"]} :</td>
            <td><textarea rows="10" style="width: 400px; height: 160px;" name="d" cols="47">{$d}</textarea></td>
        </tr>
Description;

echo <<<AssignedTo
        <tr class="odd">
            <td valign="top" class="leftvalue">{$strings["assigned_to"]} :</td>
            <td><select name="at">
AssignedTo;


if ($taskDetail['tas_assigned_to'] == "0") {
    echo '<option value="0" selected>' . $strings["unassigned"] . '</option>';
} else {
    echo '<option value="0">' . $strings["unassigned"] . '</option>';
}

$teamList = $teams->getTeamByProjectIdAndOrderedBy($project, 'mem.name');

foreach ($teamList as $team_member) {
    $clientUser = "";

    if ($team_member["tea_mem_profil"] == "3") {
        $clientUser = " (" . $strings["client_user"] . ")";
    }


    if (!empty($taskDetail['tas_assigned_to']) && $taskDetail['tas_assigned_to'] === $team_member["tea_mem_id"]) {
        echo <<<Option
<option value="{$team_member["tea_mem_id"]}" selected>{$team_member["tea_mem_login"]} / {$team_member["tea_mem_name"]}{$clientUser} </option>
Option;
    } else {
        echo <<<Option
<option value="{$team_member["tea_mem_id"]}">{$team_member["tea_mem_login"]} / {$team_member["tea_mem_name"]}{$clientUser}</option>
Option;

    }
}
echo "      </select></td>
        </tr>";

//Select phase
if ($projectDetail['pro_phase_set'] != "0") {
    $projectTarget = $projectDetail['pro_id'];
    $phaseList = $phases->getPhasesByProjectId($projectTarget, 'order_num');

    echo '<tr class="odd"><td valign="top" class="leftvalue">' . $strings["phase"] . ' :</td><td>';
    echo '<select name="phase">';

    $phaseCounter = 0;
    foreach ($phaseList as $item) {
        $phaseNum = $item['pha_order_num'];
        if ($taskDetail['tas_parent_phase'] == $phaseNum || $phase == $phaseNum) {
            echo '<option value="'.$phaseNum.'" selected>' . $item["pha_name"] . '</option>';
        } else {
            echo '<option value="' . $phaseNum . '">' . $item["pha_name"] . '</option>';
        }
    }
    echo "</select></td></tr>";
}

echo "<tr class='odd'><td valign='top' class='leftvalue'>" . $strings["status"] . " :</td><td><select name='taskStatus' onchange=\"changeSt(this)\">";

$comptSta = count($status);

foreach ($status as $key => $item) {
    if (!empty($taskDetail['tas_status']) && $taskDetail['tas_status'] == $key) {
        echo '<option value="'.$key.'" selected>'.$item.'</option>';
    } else {
        if (empty($taskDetail['tas_status']) && $key === 2) {
            echo '<option value="'.$key.'" selected>'.$item.'</option>';
        } else {
            echo '<option value="'.$key.'">'.$item.'</option>';
        }
    }
}

echo <<<HTML
    </select>
            </td>
        </tr>
        <tr class="odd">
            <td valign="top" class="leftvalue">{$strings["completion"]} :</td>
            <td>
                <select name="completion">
HTML;


for ($i = 0; $i < 11; $i++) {
    $complValue = ($i > 0) ? $i . "0 %" : $i . " %";

    if ($taskDetail['tas_completion'] == $i) {
        echo '<option value="' . $i . '" selected>' . $complValue . '</option>';
    } else {
        echo '<option value="' . $i . '">' . $complValue . '</option>';
    }
}

echo "          </select>
            </td>
        </tr>
        <tr class='odd'>
            <td valign='top' class='leftvalue'>" . $strings["priority"] . " :</td>
            <td><select name='pr'>";

$comptPri = count($priority);

for ($i = 0; $i < $comptPri; $i++) {
    if ($taskDetail['tas_priority'] == $i) {
        echo "<option value='$i' selected>$priority[$i]</option>";
    } else {
        echo "<option value='$i'>$priority[$i]</option>";
    }
}

echo "</select></td></tr>";

if ($start_date == "") {
    $start_date = $date;
}
if ($due_date == "") {
    $due_date = "--";
}
if ($complete_date == "") {
    $complete_date = "--";
}

$block1->contentRow($strings["start_date"], "<input type='text' name='start_date' id='start_date' size='20' value='$start_date'><input type='button' value=' ... ' id=\"trigStartDate\">");
echo "
<script type='text/javascript'>
    Calendar.setup({
        inputField     :    'start_date',
        button         :    'trigStartDate',
        $calendar_common_settings
    });
</script>
";
$block1->contentRow($strings["due_date"], "<input type='text' name='due_date' id='due_date' size='20' value='$due_date'><input type='button' value=' ... ' id=\"trigDueDate\">");
echo <<<JAVASCRIPT
<script type='text/javascript'>
    Calendar.setup({
        inputField     :    'due_date',
        button         :    'trigDueDate',
        {$calendar_common_settings}
    });
</script>
JAVASCRIPT;

if ($id != "") {
    $block1->contentRow($strings["complete_date"], "<input type='text' name='complete_date' id='complete_date' size='20' value='$complete_date'><input type='button' value=' ... ' id=\"trigCompleteDate\">");
    echo <<<JAVASCRIPT
	<script type='text/javascript'>
	    Calendar.setup({
	        inputField     :    'complete_date',
	        button         :    'trigCompleteDate',
        {$calendar_common_settings}
	    });
	</script>
JAVASCRIPT;
}

echo <<<TR
    <tr class="odd">
            <td valign="top" class="leftvalue">{$strings["estimated_time"]} :</td>
            <td><input size="32" value="$etm" style="width: 250px" name="etm" maxlength="32" type="TEXT">{$strings["hours"]}</td>
        </tr>
TR;
echo <<<TR
        <tr class="odd">
            <td valign="top" class="leftvalue">{$strings["actual_time"]} :</td>
            <td><input size="32" value="$atm" style="width: 250px" name="atm" maxlength="32" type="TEXT">{$strings["hours"]}</td>
        </tr>
TR;
echo <<<TR
        <tr class="odd">
            <td valign="top" class="leftvalue">{$strings["comments"]} :</td>
            <td><textarea rows="10" style="width: 400px; height: 160px;" name="comments" cols="47">{$_POST["comments"]}</textarea></td>
        </tr>
TR;
echo <<<TR
        <tr class="odd">
            <td valign="top" class="leftvalue">{$strings["published"]} :</td>
            <td><input size="32" value="0" name="pub" type="checkbox" {$checkedPub}></td>
        </tr>
TR;

if ($enableInvoicing == "true") {
    if ($taskDetail["tas_invoicing"] == "1") {
        $ckeckedInvoicing = "checked";
    }
    $block1->contentRow($strings["invoicing"], "<input size=\"32\" value=\"1\" name=\"invoicing\" type=\"checkbox\" $ckeckedInvoicing>");
    $block1->contentRow($strings["worked_hours"], "<input size=\"32\" value=\"$worked_hours\" style=\"width: 250px\" name=\"worked_hours\" type=\"TEXT\">");
}

if ($id != "") {
    $block1->contentTitle($strings["updates_task"]);
    echo "  <tr class='odd'>
                <td valign='top' class='leftvalue'>" . $strings["comments"] . " :</td>
                <td><textarea rows='10' style='width: 400px; height: 160px;' name='cUp' cols='47'></textarea></td>
            </tr>";
}

echo <<<HTML
      <tr class="odd">
                <td valign="top" class="leftvalue">&nbsp;</td>
                <td><input type="SUBMIT" value="{$strings["save"]}"></td>
            </tr>
HTML;

$block1->closeContent();
$block1->closeForm();

include APP_ROOT . '/themes/' . THEME . '/footer.php';
?>

<script>
    function changeSt(theObj, firstRun) {
        if (theObj.selectedIndex === 3) {

            if (firstRun !== true) document.etDForm.completion.selectedIndex = 0;
            document.etDForm.completion.disabled = false;
        } else {
            if (theObj.selectedIndex === 0 || theObj.selectedIndex === 1) {
                document.etDForm.completion.selectedIndex = 10;
            } else {
                document.etDForm.completion.selectedIndex = 0;
            }
            document.etDForm.completion.disabled = true;

        }
    }

    changeSt(document.etDForm.taskStatus, true);
</script>

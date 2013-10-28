<?php

require_once('../../config.php');
//require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->dirroot."/course/lib.php");
require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/gradelib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir.'/conditionlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');

$update   = optional_param('update', 0, PARAM_INT);
$courseid = optional_param('id', 0, PARAM_INT);


$url = new moodle_url('/blocks/fn_assignment_reset/assignment_reset.php');
$url->param('id', $courseid);

$PAGE->set_url($url);


if ($courseid) { // editing course
    if ($courseid == SITEID){
        // don't allow editing of  'site course' using this from
        print_error('cannoteditsiteform');
    }

    $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
    require_login($course);

    $coursecontext = context_course::instance($course->id);
    require_capability('moodle/course:update', $coursecontext);


    //$fullmodulename = get_string('modulename', $module->name);
    $fullmodulename = 'Assignments';

    if ($course->format != 'site') {
        $pageheading = 'Assignment Settings Reset Tool';
    } else {
        $pageheading = get_string('updatinga', 'moodle', $fullmodulename);
    }

} else {
    require_login();
    print_error('invalidaction');
}


$pagepath = 'mod-assign-mod';


$PAGE->set_pagetype($pagepath);
$PAGE->set_pagelayout('admin');


$streditinga = 'Assignment Settings Reset Tool';
$strmodulenameplural = get_string('modulenameplural', 'assign');


$PAGE->navbar->ignore_active();
$PAGE->navbar->add($course->shortname, new moodle_url("$CFG->wwwroot/course/view.php?id=$courseid"));
$PAGE->navbar->add('Assignment Settings Reset Tool');

$PAGE->set_heading($course->fullname);
$PAGE->set_title($streditinga);
$PAGE->set_cacheable(false);

$PAGE->requires->css('/blocks/fn_assignment_reset/style.css');

echo $OUTPUT->header();
echo $OUTPUT->heading_with_help($pageheading, '', 'assign', 'icon');

$modmoodleform = "$CFG->dirroot/blocks/fn_assignment_reset/assignment_reset_form.php";

if (file_exists($modmoodleform)) {
    require_once($modmoodleform);
} else {
    print_error('noformdesc');
}

$modlib = "$CFG->dirroot/mod/assign/lib.php";

if (file_exists($modlib)) {
    include_once($modlib);
} else {
    print_error('modulemissingcode', '', '', $modlib);
}

$mformclassname = 'fn_assignment_reset_form';

$globalConfig = $DB->get_records('config_plugins',array('plugin'=> 'block_fn_assignment_reset'));


$data = new object();

foreach ($globalConfig as $gconfig) {
    $data->{$gconfig->name} = $gconfig->value;
}
$data->id = $courseid;


$mform = new $mformclassname();

$mform->set_data($data);

if ($mform->is_cancelled()) {

    redirect("$CFG->wwwroot/blocks/fn_assignment_reset/assignment_reset.php");

} else if ($fromform = $mform->get_data()) {


    $assigns = $DB->get_records('assign', array('course'=>$courseid));

    $count = 0;

    echo $OUTPUT->container_start('box generalbox', 'notice');
    //echo $OUTPUT->box_start();

    foreach ($assigns as $assign) {
        ++$count;
        $cm = get_coursemodule_from_instance('assign', $assign->id, 0, false, MUST_EXIST);
        //require_login($course, false, $cm); // needed to setup proper $COURS
        $context = context_module::instance($cm->id);
        require_capability('moodle/course:manageactivities', $context);
        $module = $DB->get_record('modules', array('id'=>$cm->module), '*', MUST_EXIST);

        //GRADE ITEM
        $fromform->gradetopass;
        $fromform->roundtype;
        /*
        1 => 'Round up',
        2 => 'Round down',
        3 => 'Do not round (use half mark)'
        */
        $grade_item = $DB->get_record('grade_items', (array('itemtype'=>'mod', 'itemmodule'=>'assign', 'iteminstance'=>$cm->instance, 'courseid'=>$cm->course)));

        switch ($fromform->roundtype) {
            case 1://Round up
                $gradepass = ceil(($fromform->gradetopass * $grade_item->grademax) / 100);
                break;
            case 2://Round down
                $gradepass = floor(($fromform->gradetopass * $grade_item->grademax) / 100);
                break;
            case 3://Do not round (use half mark)
                $gradepass = round((($fromform->gradetopass * $grade_item->grademax) / 100), (isset($CFG->grade_decimalpoints) ? $CFG->grade_decimalpoints : 5));
                break;
        }


        $grade_item->gradepass = $gradepass;
        $DB->update_record('grade_items', $grade_item);


        //ASSIGN
        if (!isset($fromform->allowsubmissionsfromdate)) {
            $assign->allowsubmissionsfromdate = 0;
        }

        if (!isset($fromform->duedate)) {
            $assign->duedate = 0;
        }

        if (!isset($fromform->cutoffdate)) {
            $assign->cutoffdate = 0;
        }

        $assign->alwaysshowdescription = $fromform->alwaysshowdescription;
        $assign->submissiondrafts = $fromform->submissiondrafts;
        $assign->requiresubmissionstatement = $fromform->requiresubmissionstatement;
        $assign->attemptreopenmethod = $fromform->attemptreopenmethod;
        $assign->maxattempts = $fromform->maxattempts;

        $assign->sendnotifications = $fromform->sendnotifications;

        if (!isset($fromform->sendlatenotifications)) {
            $assign->sendlatenotifications = 0;
        }else{
            $assign->sendlatenotifications = $fromform->sendlatenotifications;
        }

        $assign->teamsubmission = $fromform->teamsubmission;


        if (!isset($fromform->requireallteammemberssubmit)) {
            $assign->requireallteammemberssubmit = 0;
        }else{
            $assign->requireallteammemberssubmit = $fromform->requireallteammemberssubmit;
        }


        if (!isset($fromform->teamsubmissiongroupingid)) {
            $assign->teamsubmissiongroupingid = 0;
        }else{
            $assign->teamsubmissiongroupingid = $fromform->teamsubmissiongroupingid;
        }

        $assign->completionsubmit = !empty($formdata->completionsubmit);
        $assign->blindmarking = $fromform->blindmarking;

        //print_r($assign);

        $DB->update_record('assign', $assign);


        //ASSIGN PLUGIN CONFIG
        if (isset($fromform->assignsubmission_file_maxsizebytes)) {
            if($plugin = $DB->get_record('assign_plugin_config', array('assignment'=>$assign->id, 'subtype'=>'assignsubmission', 'plugin'=>'file', 'name'=>'maxsubmissionsizebytes'))){
                $plugin->value = $fromform->assignsubmission_file_maxsizebytes;
                $DB->update_record('assign_plugin_config', $plugin);
            }else{
                $plugin = new object();
                $plugin->assignment = $assign->id;
                $plugin->subtype = 'assignsubmission';
                $plugin->plugin = 'file';
                $plugin->name = 'maxsubmissionsizebytes';
                $plugin->value = $fromform->assignsubmission_file_maxsizebytes;
                $DB->insert_record('assign_plugin_config', $plugin);
            }
        }

        if (isset($fromform->assignsubmission_comments_enabled)) {
            if($plugin = $DB->get_record('assign_plugin_config', array('assignment'=>$assign->id, 'subtype'=>'assignsubmission', 'plugin'=>'comments', 'name'=>'enabled'))){
                $plugin->value = $fromform->assignsubmission_comments_enabled;
                $DB->update_record('assign_plugin_config', $plugin);
            }else{
                $plugin = new object();
                $plugin->assignment = $assign->id;
                $plugin->subtype = 'assignsubmission';
                $plugin->plugin = 'comments';
                $plugin->name = 'enabled';
                $plugin->value = $fromform->assignsubmission_comments_enabled;
                $DB->insert_record('assign_plugin_config', $plugin);
            }
        }

        //ASSIGN PLUGIN CONFIG
        if (isset($fromform->assignfeedback_comments_enabled)) {
            if($plugin = $DB->get_record('assign_plugin_config', array('assignment'=>$assign->id, 'subtype'=>'assignfeedback', 'plugin'=>'comments', 'name'=>'enabled'))){
                $plugin->value = $fromform->assignfeedback_comments_enabled;
                $DB->update_record('assign_plugin_config', $plugin);
            }else{
                $plugin = new object();
                $plugin->assignment = $assign->id;
                $plugin->subtype = 'assignfeedback';
                $plugin->plugin = 'comments';
                $plugin->name = 'enabled';
                $plugin->value = $fromform->assignfeedback_comments_enabled;
                $DB->insert_record('assign_plugin_config', $plugin);
            }
        }

        if (isset($fromform->assignfeedback_offline_enable)) {
            if($plugin = $DB->get_record('assign_plugin_config', array('assignment'=>$assign->id, 'subtype'=>'assignfeedback', 'plugin'=>'offline', 'name'=>'enabled'))){
                $plugin->value = $fromform->assignfeedback_offline_enable;
                $DB->update_record('assign_plugin_config', $plugin);
            }else{
                $plugin = new object();
                $plugin->assignment = $assign->id;
                $plugin->subtype = 'assignfeedback';
                $plugin->plugin = 'offline';
                $plugin->name = 'enabled';
                $plugin->value = $fromform->assignfeedback_offline_enable;
                $DB->insert_record('assign_plugin_config', $plugin);
            }
        }

        if (isset($fromform->assignfeedback_file_enabled)) {
            if($plugin = $DB->get_record('assign_plugin_config', array('assignment'=>$assign->id, 'subtype'=>'assignfeedback', 'plugin'=>'file', 'name'=>'enabled'))){
                $plugin->value = $fromform->assignfeedback_file_enabled;
                $DB->update_record('assign_plugin_config', $plugin);
            }else{
                $plugin = new object();
                $plugin->assignment = $assign->id;
                $plugin->subtype = 'assignfeedback';
                $plugin->plugin = 'file';
                $plugin->name = 'enabled';
                $plugin->value = $fromform->assignfeedback_file_enabled;
                $DB->insert_record('assign_plugin_config', $plugin);
            }
        }

        echo "$count.  [ Updated ] {$assign->name}<br />\n";



        if (!isset($fromform->completion)) {
            $fromform->completion = COMPLETION_DISABLED;
        }
        if (!isset($fromform->completionview)) {
            $fromform->completionview = COMPLETION_VIEW_NOT_REQUIRED;
        }

        // Convert the 'use grade' checkbox into a grade-item number: 0 if
        // checked, null if not
        if (isset($fromform->completionusegrade) && $fromform->completionusegrade) {
            $fromform->completiongradeitemnumber = 0;
        } else {
            $fromform->completiongradeitemnumber = null;
        }

        $completion = new completion_info($course);

        if ($completion->is_enabled()) {
            // Update completion settings
            $cm->completion                = $fromform->completion;
            $cm->completiongradeitemnumber = $fromform->completiongradeitemnumber;
            $cm->completionview            = $fromform->completionview;
            $cm->completionexpected        = $fromform->completionexpected;
        }

        $DB->update_record('course_modules', $cm);
        //UNLOCK COMPLETION
        $fromform->completionunlocked = 1;
        // Now that module is fully updated, also update completion data if
        // required (this will wipe all user completion data and recalculate it)
        if ($completion->is_enabled() && !empty($fromform->completionunlocked)) {
            //$completion->reset_all_state($cm);
        }



    }//FOR EVERY ASSIGNS


    grade_regrade_final_grades($courseid);

    //echo $OUTPUT->box_end();
    echo $OUTPUT->continue_button(new moodle_url("$CFG->wwwroot/course/view.php", array('id' => $courseid)));
    echo $OUTPUT->container_end();





    // the type of event to trigger (mod_created/mod_updated)
    $eventname = '';
    /*
    if (!empty($fromform->update)) {

        // Now that module is fully updated, also update completion data if
        // required (this will wipe all user completion data and recalculate it)
        if ($completion->is_enabled() && !empty($fromform->completionunlocked)) {
            $completion->reset_all_state($cm);
        }

        $eventname = 'mod_updated';

        add_to_log($course->id, "course", "update mod",
                   "../mod/$fromform->modulename/view.php?id=$fromform->coursemodule",
                   "$fromform->modulename $fromform->instance");
        add_to_log($course->id, $fromform->modulename, "update",
                   "view.php?id=$fromform->coursemodule",
                   "$fromform->instance", $fromform->coursemodule);

    } else {
        print_error('invaliddata');
    }
    */



    //plagiarism_save_form_elements($fromform); //save plagiarism settings






} else {
    echo '<div style="text-align:center; color:red;" >Note: Any changes that you make here will be applied to ALL assignments within this course.<br /> Please use with caution.</div>';
    $mform->display();
}

echo $OUTPUT->footer();
//////////////////////////////////////////////////////////////////
<?php

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

class fn_assignment_reset_form extends moodleform {
    function definition() {
        global $DB, $COURSE, $CFG;

        $mform    = $this->_form;

        $mform->addElement('html', '<div class="assignment_reset">');
        $mform->addElement('header', 'configheader', get_string('gradetopass_header','block_fn_assignment_reset'));

        $gradetopass_options = array();
        $gradetopass_options = array(10 => '10%',
                                     20 => '20%',
                                     30 => '30%',
                                     40 => '40%',
                                     50 => '50%',
                                     60 => '60%',
                                     70 => '70%',
                                     80 => '80%',
                                     90 => '90%');

        $mform->addElement('select', 'gradetopass', get_string('gradetopass','block_fn_assignment_reset'), $gradetopass_options);

        $round_options = array();
        $round_options = array(1 => 'Round up',
                               2 => 'Round down',
                               3 => 'Do not round (use half mark)');

        $mform->addElement('select', 'roundtype', get_string('roundtype','block_fn_assignment_reset'), $round_options);




        $config = get_config('assign');

        $mform->addElement('header', 'general', get_string('settings', 'assign'));

        $mform->addElement('checkbox', 'allowsubmissionsfromdate', get_string('allowsubmissionsfromdate', 'assign'));
        $mform->addHelpButton('allowsubmissionsfromdate', 'allowsubmissionsfromdate', 'assign');
        $mform->setDefault('allowsubmissionsfromdate', time());

        $mform->addElement('checkbox', 'duedate', get_string('duedate', 'assign'));
        $mform->addHelpButton('duedate', 'duedate', 'assign');
        $mform->setDefault('duedate', time()+7*24*3600);

        $mform->addElement('checkbox', 'cutoffdate', get_string('cutoffdate', 'assign'));
        $mform->addHelpButton('cutoffdate', 'cutoffdate', 'assign');
        $mform->setDefault('cutoffdate', time()+7*24*3600);

        $mform->addElement('selectyesno', 'alwaysshowdescription', get_string('alwaysshowdescription', 'assign'));
        $mform->addHelpButton('alwaysshowdescription', 'alwaysshowdescription', 'assign');
        $mform->setDefault('alwaysshowdescription', 1);

        $mform->addElement('selectyesno', 'sendnotifications', get_string('sendnotifications', 'assign'));
        $mform->addHelpButton('sendnotifications', 'sendnotifications', 'assign');
        $mform->setDefault('sendnotifications', 1);

        $mform->addElement('selectyesno', 'sendlatenotifications', get_string('sendlatenotifications', 'assign'));
        $mform->addHelpButton('sendlatenotifications', 'sendlatenotifications', 'assign');
        $mform->setDefault('sendlatenotifications', 1);
        $mform->disabledIf('sendlatenotifications', 'sendnotifications', 'eq', 1);

        $mform->addElement('selectyesno', 'teamsubmission', get_string('teamsubmission', 'assign'));
        $mform->addHelpButton('teamsubmission', 'teamsubmission', 'assign');
        $mform->setDefault('teamsubmission', 0);

        $mform->addElement('selectyesno', 'requireallteammemberssubmit', get_string('requireallteammemberssubmit', 'assign'));
        $mform->addHelpButton('requireallteammemberssubmit', 'requireallteammemberssubmit', 'assign');
        $mform->setDefault('requireallteammemberssubmit', 0);
        $mform->disabledIf('requireallteammemberssubmit', 'teamsubmission', 'eq', 0);
        $mform->disabledIf('requireallteammemberssubmit', 'submissiondrafts', 'eq', 0);


        $groupings = groups_get_all_groupings($COURSE->id);
        $options = array();
        $options[0] = get_string('none');
        foreach ($groupings as $grouping) {
            $options[$grouping->id] = $grouping->name;
        }
        $mform->addElement('select', 'teamsubmissiongroupingid', get_string('teamsubmissiongroupingid', 'assign'), $options);

        $mform->addHelpButton('teamsubmissiongroupingid', 'teamsubmissiongroupingid', 'assign');
        $mform->setDefault('teamsubmissiongroupingid', 0);
        $mform->disabledIf('teamsubmissiongroupingid', 'teamsubmission', 'eq', 0);

        $mform->addElement('selectyesno', 'blindmarking', get_string('blindmarking', 'assign'));
        $mform->addHelpButton('blindmarking', 'blindmarking', 'assign');
        $mform->setDefault('blindmarking', 0);

        //SUBMISSION
        $mform->addElement('header', 'general', 'Submission types');

        //Maximum submission size
        $defaultmaxsubmissionsizebytes = $COURSE->maxbytes;
        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes, get_config('assignsubmission_file', 'maxbytes'));

        if ($COURSE->maxbytes == 0) {
            $choices[0] = get_string('siteuploadlimit', 'assignsubmission_file');
        } else {
            $choices[0] = 'Course upload limit' . ' (' . display_size($COURSE->maxbytes) . ')';
        }
        $settings[] = array('type' => 'select',
                            'name' => 'maxsubmissionsizebytes',
                            'description' => get_string('maximumsubmissionsize', 'assignsubmission_file'),
                            'options'=> $choices,
                            'default'=> $defaultmaxsubmissionsizebytes);

        $mform->addElement('select', 'assignsubmission_file_maxsizebytes', get_string('maximumsubmissionsize', 'assignsubmission_file'), $choices);
        $mform->addHelpButton('assignsubmission_file_maxsizebytes', 'maximumsubmissionsize', 'assignsubmission_file');
        $mform->setDefault('assignsubmission_file_maxsizebytes', $defaultmaxsubmissionsizebytes);
        $mform->disabledIf('assignsubmission_file_maxsizebytes', 'assignsubmission_file_enabled', 'eq', 0);

        //Submission comments
        $mform->addElement('selectyesno', 'assignsubmission_comments_enabled', get_string('assignsubmission_comments_enabled', 'block_fn_assignment_reset'));
        $mform->addHelpButton('assignsubmission_comments_enabled', 'enabled', 'assignsubmission_comments');


        //FEEDBACK
        $mform->addElement('header', 'general', get_string('feedbacktypes', 'assign'));

        //Feedback comments
        $mform->addElement('selectyesno', 'assignfeedback_comments_enabled', get_string('assignfeedback_comments_enabled', 'block_fn_assignment_reset'));
        $mform->addHelpButton('assignfeedback_comments_enabled', 'enabled', 'assignfeedback_comments');

        //Offline grading worksheet
        $mform->addElement('selectyesno', 'assignfeedback_offline_enable', get_string('assignfeedback_offline_enable', 'block_fn_assignment_reset'));
        $mform->addHelpButton('assignfeedback_offline_enable', 'enabled', 'assignfeedback_offline');

        //Feedback files
        $mform->addElement('selectyesno', 'assignfeedback_file_enabled', get_string('assignfeedback_file_enabled', 'block_fn_assignment_reset'));
        $mform->addHelpButton('assignfeedback_file_enabled', 'enabled', 'assignfeedback_file');

        $courseid = optional_param('id', 0, PARAM_INT);
        $mform->addElement('hidden', 'id', $courseid);
        $mform->setType('id', PARAM_INT);

        //SUBMISSION SETTINGS
        $mform->addElement('header', 'submissionsettings', get_string('submissionsettings', 'assign'));

        $name = get_string('submissiondrafts', 'assign');
        $mform->addElement('selectyesno', 'submissiondrafts', $name);
        $mform->addHelpButton('submissiondrafts', 'submissiondrafts', 'assign');
        $mform->setDefault('submissiondrafts', 0);

        if (empty($config->submissionstatement)) {
            $mform->addElement('hidden', 'requiresubmissionstatement', 0);
        } else if (empty($config->requiresubmissionstatement)) {
            $name = get_string('requiresubmissionstatement', 'assign');
            $mform->addElement('selectyesno', 'requiresubmissionstatement', $name);
            $mform->setDefault('requiresubmissionstatement', 0);
            $mform->addHelpButton('requiresubmissionstatement',
                                  'requiresubmissionstatementassignment',
                                  'assign');
        } else {
            $mform->addElement('hidden', 'requiresubmissionstatement', 1);
        }
        $mform->setType('requiresubmissionstatement', PARAM_BOOL);

        $options = array(
            ASSIGN_ATTEMPT_REOPEN_METHOD_NONE => get_string('attemptreopenmethod_none', 'mod_assign'),
            ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL => get_string('attemptreopenmethod_manual', 'mod_assign'),
            ASSIGN_ATTEMPT_REOPEN_METHOD_UNTILPASS => get_string('attemptreopenmethod_untilpass', 'mod_assign')
        );
        $mform->addElement('select', 'attemptreopenmethod', get_string('attemptreopenmethod', 'mod_assign'), $options);
        $mform->setDefault('attemptreopenmethod', ASSIGN_ATTEMPT_REOPEN_METHOD_NONE);
        $mform->addHelpButton('attemptreopenmethod', 'attemptreopenmethod', 'mod_assign');

        $options = array(ASSIGN_UNLIMITED_ATTEMPTS => get_string('unlimitedattempts', 'mod_assign'));
        $options += array_combine(range(1, 30), range(1, 30));
        $mform->addElement('select', 'maxattempts', get_string('maxattempts', 'mod_assign'), $options);
        $mform->addHelpButton('maxattempts', 'maxattempts', 'assign');
        $mform->setDefault('maxattempts', -1);
        $mform->disabledIf('maxattempts', 'attemptreopenmethod', 'eq', ASSIGN_ATTEMPT_REOPEN_METHOD_NONE);







        // Conditional activities: completion tracking section
        if(!isset($completion)) {
            $completion = new completion_info($COURSE);
        }
        if ($completion->is_enabled()) {
            $mform->addElement('header', 'activitycompletionheader', get_string('activitycompletion', 'completion'));

            // Unlock button for if people have completed it (will
            // be removed in definition_after_data if they haven't)
            //$mform->addElement('submit', 'unlockcompletion', get_string('unlockcompletion', 'completion'));
            //$mform->registerNoSubmitButton('unlockcompletion');
            //$mform->addElement('hidden', 'completionunlocked', 0);
            //$mform->setType('completionunlocked', PARAM_INT);

            $mform->addElement('select', 'completion', get_string('completion', 'completion'),
                array(COMPLETION_TRACKING_NONE=>get_string('completion_none', 'completion'),
                COMPLETION_TRACKING_MANUAL=>get_string('completion_manual', 'completion')));
            //$mform->setDefault('completion', true ? COMPLETION_TRACKING_MANUAL : COMPLETION_TRACKING_NONE);
            $mform->addHelpButton('completion', 'completion', 'completion');

            // Automatic completion once you view it
            $gotcompletionoptions = false;
            if (plugin_supports('mod', 'assign', FEATURE_COMPLETION_TRACKS_VIEWS, false)) {
                $mform->addElement('checkbox', 'completionview', get_string('completionview', 'completion'),
                    get_string('completionview_desc', 'completion'));
                $mform->disabledIf('completionview', 'completion', 'ne', COMPLETION_TRACKING_AUTOMATIC);
                $gotcompletionoptions = true;
            }

            // Automatic completion once it's graded
            if (plugin_supports('mod', 'assign', FEATURE_GRADE_HAS_GRADE, false)) {
                $mform->addElement('checkbox', 'completionusegrade', get_string('completionusegrade', 'completion'),
                    get_string('completionusegrade_desc', 'completion'));
                $mform->disabledIf('completionusegrade', 'completion', 'ne', COMPLETION_TRACKING_AUTOMATIC);
                $mform->addHelpButton('completionusegrade', 'completionusegrade', 'completion');
                $gotcompletionoptions = true;
            }

            // Automatic completion according to module-specific rules
            $this->_customcompletionelements = $this->add_completion_rules();
            foreach ($this->_customcompletionelements as $element) {
                $mform->disabledIf($element, 'completion', 'ne', COMPLETION_TRACKING_AUTOMATIC);
            }

            $gotcompletionoptions = $gotcompletionoptions ||
                count($this->_customcompletionelements)>0;

            // Automatic option only appears if possible
            if ($gotcompletionoptions) {
                $mform->getElement('completion')->addOption(
                    get_string('completion_automatic', 'completion'),
                    COMPLETION_TRACKING_AUTOMATIC);
            }

            // Completion expected at particular date? (For progress tracking)
            $mform->addElement('date_selector', 'completionexpected', get_string('completionexpected', 'completion'), array('optional'=>true));
            $mform->addHelpButton('completionexpected', 'completionexpected', 'completion');
            $mform->disabledIf('completionexpected', 'completion', 'eq', COMPLETION_TRACKING_NONE);
        }

        //BUTTON
        $this->add_action_buttons();

        // Add warning popup/noscript tag, if grades are changed by user.
        if ($mform->elementExists('grade') && !empty($this->_instance) && $DB->record_exists_select('assign_grades', 'assignment = ? AND grade <> -1', array($this->_instance))) {
            $module = array(
                'name' => 'mod_assign',
                'fullpath' => '/mod/assign/module.js',
                'requires' => array('node', 'event'),
                'strings' => array(array('changegradewarning', 'mod_assign'))
                );
            $PAGE->requires->js_init_call('M.mod_assign.init_grade_change', null, false, $module);

            // Add noscript tag in case
            $noscriptwarning = $mform->createElement('static', 'warning', null,  html_writer::tag('noscript', get_string('changegradewarning', 'mod_assign')));
            $mform->insertElementBefore($noscriptwarning, 'grade');
        }
        $mform->addElement('html', '</div>');
    }


    /**
     * Any data processing needed before the form is displayed
     * (needed to set up draft areas for editor and filemanager elements)
     * @param array $defaultvalues
     */
    function data_preprocessing(&$defaultvalues) {
        global $DB;

        $ctx = null;
        if ($this->current && $this->current->coursemodule) {
            $cm = get_coursemodule_from_instance('assign', $this->current->id, 0, false, MUST_EXIST);
            $ctx = context_module::instance($cm->id);
        }
        $assignment = new assign($ctx, null, null);
        if ($this->current && $this->current->course) {
            if (!$ctx) {
                $ctx = context_course::instance($this->current->course);
            }
            $assignment->set_course($DB->get_record('course', array('id'=>$this->current->course), '*', MUST_EXIST));
        }
        $assignment->plugin_data_preprocessing($defaultvalues);
    }

    function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionsubmit', '', get_string('completionsubmit', 'assign'));
        return array('completionsubmit');
    }

    function completion_rule_enabled($data) {
        return !empty($data['completionsubmit']);
    }


}
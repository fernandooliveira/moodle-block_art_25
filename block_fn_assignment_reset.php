<?php

defined('MOODLE_INTERNAL') || die();

class block_fn_assignment_reset extends block_list {
    function init() {
        $this->title = get_string('fn_assignment_reset','block_fn_assignment_reset');
    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return true;
    }

    function instance_allow_config() {
        return true;
    }

    function applicable_formats() {
        return array('course' => true, 'course-category' => false, 'site' => true);
    }

    function specialization() {
        global $DB;

        if (!empty($this->config->fn_assignment_resetid)) {
            $fn_assignment_reset = $DB->get_record('fn_assignment_reset', array('id'=>$this->config->fn_assignment_resetid));
            if ($fn_assignment_reset) {
                $this->title = s($fn_assignment_reset->name);
            }
        }
    }

    function get_content() {
        global $CFG, $USER, $DB, $course;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->icons = array();
        /*
        if (!$this->import_fn_assignment_reset_plugin()) {
            $this->content->items = array(get_string('nofn_assignment_resetplugin','block_fn_assignment_reset'));
            return $this->content;
        }

        if (empty($this->config->fn_assignment_resetid)) {
            $this->content->items = array(get_string('nofn_assignment_reset','block_fn_assignment_reset'));
            return $this->content;
        }
        */
        
    

        /// Need the bigger course object.       
        $course = $this->page->course;       
         
        $context = get_context_instance(CONTEXT_COURSE, $course->id);
        
        $isteacheredit = has_capability('moodle/course:update', $context);       

        ///Course Teacher Menu:
        if (($this->page->course->id != SITEID)) {
           
            $settingformfile = file_exists($CFG->dirroot . '/blocks/fn_assignment_reset/assignment_reset.php');
            
            if ($settingformfile && has_capability('moodle/course:update', $context)) {              
                
                $this->content->items[] =  '<a href="'.$CFG->wwwroot.'/blocks/fn_assignment_reset/assignment_reset.php?id='.$course->id.'">'.
                                      get_string('setting', 'block_fn_assignment_reset').'</a>';
                $this->content->icons[] = '<img src="' . $CFG->wwwroot . '/blocks/fn_tabs/pix/setting.gif" height="16" width="16" alt="" STYLE="margin-right: 7px">';
                
            }
        }
        
        return $this->content;        
        
        
        
        
        
        
        
        $this->content->text   = 'The content of our FN Assignment Reset block!';
        
        return $this->content;
    }


}

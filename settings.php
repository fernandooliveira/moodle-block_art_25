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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * block_fn_assignment_reset block settings
 *
 * @package    block_fn_assignment_reset
 * @copyright  2013 Michael Gardener <mgardener@cissq.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    
    $gradetopass_options = array(10 => '10%', 
                                     20 => '20%', 
                                     30 => '30%', 
                                     40 => '40%', 
                                     50 => '50%', 
                                     60 => '60%', 
                                     70 => '70%', 
                                     80 => '80%', 
                                     90 => '90%');
                                     
    $settings->add(new admin_setting_configselect('block_fn_assignment_reset/gradetopass',new lang_string('gradetopass', 'block_fn_assignment_reset'), '', '60', $gradetopass_options));
    
    
    $round_options = array(1 => 'Round up', 
                               2 => 'Round down', 
                               3 => 'Do not round (use half mark)');        
        
         
    $settings->add(new admin_setting_configselect('block_fn_assignment_reset/roundtype',new lang_string('roundtype', 'block_fn_assignment_reset'), '', '3', $round_options));   
        
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/allowsubmissionsfromdate', new lang_string('allowsubmissionsfromdate', 'assign'), '', 1));
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/duedate', new lang_string('duedate', 'assign'), '', 1));
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/cutoffdate', new lang_string('cutoffdate', 'assign'), '', 1));
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/alwaysshowdescription', new lang_string('alwaysshowdescription', 'assign'), '', 1));
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/submissiondrafts', new lang_string('submissiondrafts', 'assign'), '', 0));
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/requiresubmissionstatement', new lang_string('requiresubmissionstatement', 'assign'), '', 0));
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/sendnotifications', new lang_string('sendnotifications', 'assign'), '', 1));
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/teamsubmission', new lang_string('teamsubmission', 'assign'), '', 0));
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/blindmarking', new lang_string('blindmarking', 'assign'), '', 0));
    
                                                                                       
    $choices = get_max_upload_sizes($CFG->maxbytes, 0, 0, null);
    $settings->add(new admin_setting_configselect('block_fn_assignment_reset/assignsubmission_file_maxsizebytes',new lang_string('maximumsubmissionsize', 'assignsubmission_file'), '', '1048576', $choices));
    
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/assignsubmission_comments_enabled', new lang_string('assignsubmission_comments_enabled', 'block_fn_assignment_reset'), '', 0)); 
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/assignfeedback_comments_enabled', new lang_string('assignfeedback_comments_enabled', 'block_fn_assignment_reset'), '', 1)); 
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/assignfeedback_offline_enabled', new lang_string('assignfeedback_offline_enable', 'block_fn_assignment_reset'), '', 0)); 
    $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/assignfeedback_file_enabled', new lang_string('assignfeedback_file_enabled', 'block_fn_assignment_reset'), '', 0)); 
        
    
    $settings->add(new admin_setting_configselect('block_fn_assignment_reset/completion',new lang_string('completion', 'completion'), '', '3', 
                array(COMPLETION_TRACKING_NONE=>get_string('completion_none', 'completion'),
                      COMPLETION_TRACKING_MANUAL=>get_string('completion_manual', 'completion'),
                      COMPLETION_TRACKING_AUTOMATIC=>get_string('completion_automatic', 'completion'))));
                      
     $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/completionview', new lang_string('completionview', 'completion'), new lang_string('completionview_desc', 'completion'), 0));                       
     $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/completionusegrade', new lang_string('completionusegrade', 'completion'), new lang_string('completionusegrade_desc', 'completion'), 0));                       
     $settings->add(new admin_setting_configcheckbox('block_fn_assignment_reset/completionsubmit', '', new lang_string('completionsubmit', 'assign'), 0));                       
        
}

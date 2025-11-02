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
 * Edit form for block_floatingbutton
 *
 * @package    block_floatingbutton
 * @copyright  2022-2025 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_floatingbutton_edit_form extends block_edit_form {
    /**
     * Array of course modules and sections
     *
     * @var array
     */
    protected $courselist;

    /**
     * Whether the page is a course page
     *
     * @var bool
     */
    protected $iscourse;

    /**
     * Form definition - call to parent definition() is avoided here to get
     * correct order.
     *
     * @return void
     */
    public function definition(): void {
    }

    /**
     * Add specific elements to the standard block form
     *
     * @param MoodleQuickForm $mform
     * @return void
     */
    protected function specific_definition($mform): void {
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));
        $mform->addElement(
            'text',
            'config_defaultbackgroundcolor',
            get_string('defaultbackgroundcolor', 'block_floatingbutton'),
            ['class' => 'block_floatingbutton-color-input']
        );
        $mform->setDefault('config_defaultbackgroundcolor', get_config('block_floatingbutton', 'defaultbackgroundcolor'));
        $mform->setType('config_defaultbackgroundcolor', PARAM_TEXT);
        $mform->addElement(
            'text',
            'config_defaulttextcolor',
            get_string('defaulttextcolor', 'block_floatingbutton'),
            ['class' => 'block_floatingbutton-color-input']
        );
        $mform->setDefault('config_defaulttextcolor', get_config('block_floatingbutton', 'defaulttextcolor'));
        $mform->setType('config_defaulttextcolor', PARAM_TEXT);
    }

    /**
     * Loads the modules of the corresponding course (if there is one).
     *
     * @return void
     */
    public function generate_course_module_list(): void {
        if (isset($this->courselist)) {
            return;
        }

        if (!is_null($this->page->course)) {
            $this->iscourse = true;
            $courseid = $this->page->course->id;
        }

        $cm = get_fast_modinfo($courseid);

        $courselist = [];

        foreach ($cm->sections as $sectionnum => $section) {
            $sectioninfo = $cm->get_section_info($sectionnum);
            $cmid = 'section=' . $sectionnum;
            $name = $sectioninfo->name;
            if (empty($name)) {
                if ($sectionnum == 0) {
                    $name = get_string('general');
                } else {
                    $name = get_string('section') . ' ' . $sectionnum;
                }
            }
            $courselist[$cmid] = '--- ' . $name . ' ---';

            foreach ($section as $cmid) {
                $module = $cm->get_cm($cmid);
                // Get only course modules which are not deleted.
                if ($module->deletioninprogress == 0) {
                    $courselist['cmid=' . $cmid] = $module->name;
                }
            }
        }
        $this->courselist = $courselist;
    }

    /**
     * Called when data / defaults are already loaded.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition_after_data(): void {
        // phpcs:disable
        global $PAGE;
        // phpcs:enable

        $types = [
            'internal' => get_string('type_internal', 'block_floatingbutton'),
            'external' => get_string('type_external', 'block_floatingbutton'),
            'special' => get_string('type_special', 'block_floatingbutton'),
        ];

        $speciallinks = $this->block->get_special_links();
        $speciallinkoptions = [];
        foreach ($speciallinks as $k) {
            $speciallinkoptions[$k] = get_string($k, 'block_floatingbutton');
        }

        $mform = $this->_form;

        $data = $mform->_defaultValues;

        $this->generate_course_module_list();

        $repeatarray = [];
        $repeatarray[] = $mform->createElement(
            'header',
            'config_header',
            get_string('icon', 'block_floatingbutton') . ' {no}'
        );
        $repeatarray[] = $mform->createElement(
            'text',
            'config_name',
            get_string('name', 'block_floatingbutton')
        );
        $repeatarray[] = $mform->createElement(
            'select',
            'config_type',
            get_string('type', 'block_floatingbutton'),
            $types
        );
        $repeatarray[] = $mform->createElement(
            'url',
            'config_externalurl',
            get_string('externalurl', 'block_floatingbutton')
        );
        $repeatarray[] = $mform->createElement(
            'select',
            'config_cmid',
            get_string('internalurl', 'block_floatingbutton'),
            $this->courselist
        );
        $repeatarray[] = $mform->createElement(
            'select',
            'config_speciallink',
            get_string('speciallink', 'block_floatingbutton'),
            $speciallinkoptions
        );
        $repeatarray[] = $mform->createElement(
            'text',
            'config_icon',
            get_string('icon', 'block_floatingbutton'),
            ['class' => 'block_floatingbutton-input']
        );
        $repeatarray[] = $mform->createElement(
            'text',
            'config_backgroundcolor',
            get_string('backgroundcolor', 'block_floatingbutton'),
            ['class' => 'block_floatingbutton-color-input']
        );
        $repeatarray[] = $mform->createElement(
            'text',
            'config_textcolor',
            get_string('textcolor', 'block_floatingbutton'),
            ['class' => 'block_floatingbutton-color-input']
        );
        $repeatarray[] = $mform->createElement(
            'advcheckbox',
            'config_customlayout',
            get_string('customlayout', 'block_floatingbutton')
        );
        $repeatarray[] = $mform->createElement(
            'submit',
            'icondelete',
            get_string('delete', 'block_floatingbutton'),
            ['class' => 'block_floatingbutton-edit']
        );
        $mform->registerNoSubmitButton('icondelete');

        $repeatedoptions = [];
        $repeatedoptions['config_name']['type'] = PARAM_RAW;
        $repeatedoptions['config_externalurl']['type'] = PARAM_URL;
        $repeatedoptions['config_cmid']['type'] = PARAM_RAW;
        $repeatedoptions['config_icon']['type'] = PARAM_ALPHANUMEXT;
        $repeatedoptions['config_customlayout']['type'] = PARAM_INT;
        $repeatedoptions['config_textcolor']['type'] = PARAM_TEXT;
        $repeatedoptions['config_backgroundcolor']['type'] = PARAM_TEXT;

        $repeatedoptions['config_type']['helpbutton'] = ['type', 'block_floatingbutton', '', true];
        $repeatedoptions['config_name']['helpbutton'] = ['name', 'block_floatingbutton', '', true];
        $repeatedoptions['config_externalurl']['helpbutton'] = ['externalurl', 'block_floatingbutton', '', true];
        $repeatedoptions['config_cmid']['helpbutton'] = ['internalurl', 'block_floatingbutton', '', true];
        $repeatedoptions['config_icon']['helpbutton'] = ['icon', 'block_floatingbutton', '', true];
        $repeatedoptions['config_customlayout']['helpbutton'] = ['customlayout', 'block_floatingbutton', '', true];
        $repeatedoptions['config_textcolor']['helpbutton'] = ['textcolor', 'block_floatingbutton', '', true];
        $repeatedoptions['config_backgroundcolor']['helpbutton'] = ['backgroundcolor', 'block_floatingbutton', '', true];
        // Hide URL types not currently selected.
        $repeatedoptions['config_cmid']['hideif'] = ['config_type', 'neq', 'internal'];
        $repeatedoptions['config_externalurl']['hideif'] = ['config_type', 'neq', 'external'];
        $repeatedoptions['config_speciallink']['hideif'] = ['config_type', 'neq', 'special'];
        // Only show individual colors if custom layout is activated.
        $repeatedoptions['config_textcolor']['hideif'] = ['config_customlayout', 'neq', 1];
        $repeatedoptions['config_backgroundcolor']['hideif'] = ['config_customlayout', 'neq', 1];

        $repeats = 1;

        // The value config_icon_number cannot be used for the number of repeats as it does _not_ refer to the
        // number of entries. Instead it refers to the maximum of array keys (+1) while some keys are not used in
        // the array (e.g. when an entry is deleted).
        if (isset($data['config_name'])) {
            $repeats = max(count($data['config_name']), $repeats);
        }

        $this->repeat_elements(
            $repeatarray,
            $repeats,
            $repeatedoptions,
            'config_icon_number',
            'config_add_more_icons_btn',
            1,
            get_string('addmoreicons', 'block_floatingbutton'),
            false,
            'icondelete'
        );

        // Global $PAGE has to be kept here because $this->page doesn't work to load the JS modules.
        // phpcs:disable
        $PAGE->requires->js_call_amd(
            'block_floatingbutton/iconpicker',
            'init',
            ['block_floatingbutton-iconpicker-button']
        );
        $PAGE->requires->js_call_amd('block_floatingbutton/colorpicker', 'init', []);
        // phpcs:enable

        // Calling this method here keeps the order with icons being shown first in form.
        parent::definition();

        if (!isset($data['config_defaulttextcolor'])) {
            $data['config_defaulttextcolor'] = get_config('block_floatingbutton', 'defaulttextcolor');
        }

        if (!isset($data['config_defaultbackgroundcolor'])) {
            $data['config_defaultbackgroundcolor'] = get_config('block_floatingbutton', 'defaultbackgroundcolor');
        }

        $skip = [];
        if (isset($mform->_submitValues['icondelete'])) {
            $skip = array_keys($mform->_submitValues['icondelete']);
        }
        if (isset($mform->_submitValues['icondelete-hidden'])) {
            $skip = array_merge($skip, array_keys($mform->_submitValues['icondelete-hidden']));
        }
        if (isset($mform->_submitValues['config_defaulttextcolor'])) {
            $data['config_defaulttextcolor'] = $mform->_submitValues['config_defaulttextcolor'];
        }
        if (isset($mform->_submitValues['config_defaultbackgroundcolor'])) {
            $data['config_defaultbackgroundcolor'] = $mform->_submitValues['config_defaultbackgroundcolor'];
        }
        // Renumber header entries to avoid gaps in numbering when an icon is deleted.
        $number = 1;
        for ($i = 0; $i < $mform->_constantValues['config_icon_number']; $i++) {
            if (!in_array($i, $skip)) {
                for ($j = 0; $j < count($mform->_elements); $j++) {
                    if (
                        $mform->_elements[$j]->_type == 'header' &&
                        $mform->_elements[$j]->_attributes['name'] == 'config_header[' . $i . ']'
                    ) {
                        $mform->_elements[$j]->_text = get_string('icon', 'block_floatingbutton') . ' ' . $number;
                        $number++;
                    }
                }
                // Choosing an icon is mandatory.
                $mform->addRule(
                    'config_icon[' . $i . ']',
                    get_string('icon_missing', 'block_floatingbutton'),
                    'required',
                    'client'
                );
            }
        }

        // Set default for custom colors - this is necessary because setting the default value doesn't work
        // when using it in definition_after_data().
        for ($i = 0; $i < $mform->_constantValues['config_icon_number']; $i++) {
            if (!in_array($i, $skip)) {
                if (!isset($data['config_customlayout'][$i]) || $data['config_customlayout'][$i] != 1) {
                    if (!isset($data['config_textcolor'][$i]) || $data['config_textcolor'][$i] == '') {
                        $this->set_value($mform, 'text', 'config_textcolor[' . $i . ']', $data['config_defaulttextcolor'], true);
                    }
                    if (!isset($data['config_backgroundcolor'][$i]) || $data['config_backgroundcolor'][$i] == '') {
                        $this->set_value(
                            $mform,
                            'text',
                            'config_backgroundcolor[' . $i . ']',
                            $data['config_defaultbackgroundcolor'],
                            true
                        );
                    }
                }
            }
        }
        $this->set_value($mform, 'text', 'config_defaultbackgroundcolor', $data['config_defaultbackgroundcolor']);
        $this->set_value($mform, 'text', 'config_defaulttextcolor', $data['config_defaulttextcolor']);
    }
    // phpcs:disable

    /**
     * Display edit form, when adding a floating button block.
     *
     * @return boolean
     */
    public static function display_form_when_adding(): bool {
        return true;
    }

    /**
     * Overrides the _process_submission() method to remove empty spaces in the arrays in the block config.
     *
     * @param string $method
     * @return void
     */
    function _process_submission($method): void {
        // phpcs:enable
        parent::_process_submission($method);
        $keys = ['icon', 'name', 'type', 'backgroundcolor', 'textcolor', 'externalurl', 'speciallink', 'customlayout', 'cmid'];
        foreach ($keys as $key) {
            if (isset($this->block->config->$key)) {
                $this->block->config->$key = array_values($this->block->config->$key);
            }
        }
    }

    /**
     * Sets a value directly in the moodleform object (to be called from definition_after_data)
     *
     * @param MoodleQuickForm $mform The MoodleQuickForm to change
     * @param string $type The type of the form element
     * @param string $name The name of the form element
     * @param string $value The new value of the form element
     * @param bool $onlyempty Only set value if empty
     * @return void
     */
    public function set_value(MoodleQuickForm $mform, string $type, string $name, string $value, bool $onlyempty = false): void {
        for ($i = 0; $i < $mform->_constantValues['config_icon_number']; $i++) {
            for ($j = 0; $j < count($mform->_elements); $j++) {
                if (
                    $mform->_elements[$j]->_type == $type &&
                    $mform->_elements[$j]->_attributes['name'] == $name
                ) {
                    if (
                        (!isset($mform->_elements[$j]->_attributes['value']) ||
                            empty($mform->_elements[$j]->_attributes['value'])) && $onlyempty
                        ||
                        !$onlyempty
                    ) {
                        $mform->_elements[$j]->_attributes['value'] = $value;
                    }
                }
            }
        }
    }

    /**
     * Validate form data.
     *
     * @param array $data
     * @param array $files
     * @throws coding_exception
     */
    public function validation($data, $files): array {
        $errors = [];
        if (!empty($data['config_name'])) {
            for ($i = 0; $i < count($data['config_name']); $i++) {
                if (
                    isset($data['config_type'][$i]) &&
                    $data['config_type'][$i] == 'external' &&
                    empty($data['config_externalurl'][$i])
                ) {
                    $errors['config_externalurl[' . $i . ']'] = get_string('missing_externalurl', 'block_floatingbutton');
                }
            }
        }
        return $errors;
    }
}

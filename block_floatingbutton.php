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
 * Block class for block_floatingbutton
 *
 * @package    block_floatingbutton
 * @copyright  2022 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_floatingbutton extends block_base {
    /**
     * Names of special link types
     *
     * @var array
     */
    protected $speciallinks = [
        'turn_editing_on',
        'next_section',
        'previous_section',
        'back_to_main_page',
        'back_to_activity_section',
        'change_editor',
        'toggle_distractionfree',
    ];

    /**
     * Initialize block
     *
     * @return void
     * @throws coding_exception
     */
    public function init(): void {
        $this->title = get_string('floatingbutton', 'block_floatingbutton');
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return bool
     */
    public function has_config(): bool {
        return true;
    }

    /**
     * Adds the block content to the page header.
     *
     * @return void
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function specialization(): void {
        if (!empty($this->instance->visible)) {
            $this->get_content();
            $this->page->add_header_action($this->content->text);
        }
    }

    /**
     * Returns true as block shouldn't be shown as block.
     *
     * @return bool
     */
    public function is_empty(): bool {
        return true;
    }

    /**
     * Returns the special link types, i.e. moodle functions.
     *
     * @return array
     */
    public function get_special_links(): array {
        return $this->speciallinks;
    }

    /**
     * Returns the block content. Content is cached for performance reasons.
     *
     * @return stdClass
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function get_content(): stdClass {
        global $OUTPUT, $CFG;
        $dummy = new stdClass();
        $dummy->text = '';
        if ($this->content !== null) {
            return $dummy;
        }

        $this->content = new stdClass();
        $context = new stdClass();

        if ($this->page->course) {
            $context->courseid = $this->page->course->id;
            $modinfo = get_fast_modinfo($context->courseid);

            if ($this->page->cm) {
                $context->cmid = $this->page->cm->id;
                $context->sectionnum = $this->page->cm->sectionnum;
            } else {
                $context->cmid = null;
                $context->sectionnum = optional_param('section', 0, PARAM_INT);
            }

            if ($context->sectionnum > 0) {
                $context->prevsectionnum = $context->sectionnum - 1;
            }
            if ($context->sectionnum < count($modinfo->get_section_info_all()) - 1) {
                $context->nextsectionnum = $context->sectionnum + 1;
            }
        }
        $context->sesskey = sesskey();

        if ($this->page->user_allowed_editing()) {
            $context->editing = $this->page->user_is_editing();
        }

        $context->icons = [];

        if (!is_null($this->config)) {
            $format = core_courseformat\base::instance($context->courseid);
            for ($i = 0; $i < $this->config->icon_number; $i++) {
                if (empty($this->config->icon[$i])) {
                    continue;
                }
                $url = null;
                $edit = false;
                $notavailable = false;
                $name = null;
                $section = null;
                // All sections are shown on one page.
                $options = $format->get_format_options();
                $tiles = $format->get_format() == 'tiles';
                /*
                * format_tiles is collapsed by default when user is not editing (but does not have COURSE_DISPLAY_SINGLEPAGE set).
                  format_topcoll is collapsed, but does neither have COURSE_DISPLAY_SINGLEPAGE set.
                  format_onetopic is never collapsed but has COURSE_DISPLAY_SINGLEPAGE (incorrectly) set.
                  Other formats should be maybe collapsed if coursedisplay is set to COURSE_DISPLAY_SINGLEPAGE.
                  If they are not collapsed, opening the section does have no effect and the corresponding anchor is jumped to.
                */
                $collapsed = is_null($this->page->cm) && (
                    $tiles && !$this->page->user_is_editing() ||
                    $format->get_format() == 'topcoll' ||
                    (isset($options['coursedisplay']) &&
                    $options['coursedisplay'] == COURSE_DISPLAY_SINGLEPAGE &&
                    $format->get_format() != 'onetopic'));
                $sectionnav = false;
                $anchor = '';
                $internal = false;
                $prevsection = false;
                $external = false;

                switch ($this->config->type[$i]) {
                    case 'internal':
                        // Skip empty internal links (this could happen, when a course module that is referenced by an icon
                        // is not included in backup).
                        if (!is_null($this->config->cmid[$i])) {
                            [$type, $id] = explode('=', $this->config->cmid[$i]);
                            switch ($type) {
                                case 'cmid':
                                    if (in_array($id, array_keys($modinfo->get_cms()))) {
                                        $module = $modinfo->get_cm($id);
                                        $name = $module->name;
                                        $notavailable = !$module->available;
                                        $section = $module->sectionnum;
                                        $internal = true;
                                        // Call get_url() so at least once obtain_dynamic_data() is called.
                                        if (!is_null($module->get_url())) {
                                            // Link modules that have a view page to their corresponding url.
                                            $url = $module->url->out();
                                            $collapsed = false;
                                        } else {
                                            // Other modules (like labels) are shown on the course page. Link to the corresponding
                                            // anchor.
                                            $url = $format->get_view_url($module->sectionnum);
                                            $anchor = 'module-' . $id;
                                            $url->set_anchor($anchor);
                                        }
                                    } else {
                                        $notavailable = true;
                                    }
                                    break;
                                case 'section':
                                    $sectioninfo = $modinfo->get_section_info($id);
                                    if (!is_null($sectioninfo)) {
                                        $name = $sectioninfo->name;
                                        if (empty($name)) {
                                            if ($id == 0) {
                                                $name = get_string('general');
                                            } else {
                                                $name = get_string('section') . ' ' . $id;
                                            }
                                        }
                                        $notavailable = !$sectioninfo->available;
                                        $url = $format->get_view_url($id);
                                        $internal = true;
                                        $anchor = 'section-' . $id;
                                        $section = $id;
                                    } else {
                                        $notavailable = true;
                                    }
                                    break;
                            }
                        }
                        break;
                    case 'external':
                        $url = $this->config->externalurl[$i];
                        // Be aware that stripos returns 0 in most cases when wwwroot is found
                        // as it is in the first position of the url.
                        $external = stripos($url, $CFG->wwwroot) === false;
                        break;
                    case 'special':
                        switch ($this->config->speciallink[$i]) {
                            case 'turn_editing_on':
                                $url = (new moodle_url('/course/view.php'))->out();
                                $edit = true;
                                $notavailable = !$this->page->user_allowed_editing();
                                break;
                            case 'next_section':
                                if (isset($context->nextsectionnum)) {
                                    $url = $format->get_view_url($context->nextsectionnum);
                                    $name = get_string('nextsection');
                                }
                                $sectionnav = true;
                                $internal = true;
                                break;
                            case 'previous_section':
                                if (isset($context->prevsectionnum)) {
                                    $url = $format->get_view_url($context->prevsectionnum);
                                    $name = get_string('previoussection');
                                }
                                $sectionnav = true;
                                $internal = true;
                                $prevsection = true;
                                break;
                            case 'back_to_main_page':
                                $url = (new moodle_url(
                                    '/course/view.php',
                                    ['id' => $context->courseid]
                                )
                                    )->out();
                                $name = get_string('back_to_main_page', 'block_floatingbutton');
                                break;
                            case 'back_to_activity_section':
                                if (!is_null($context->cmid)) {
                                    $url = $format->get_view_url($context->sectionnum);
                                    $name = get_string('back_to_activity_section', 'block_floatingbutton');
                                } else {
                                    $notavailable = true;
                                }
                                break;
                            case 'change_editor':
                                $url = (new moodle_url(
                                    '/user/editor.php',
                                    ['course' => $context->courseid]
                                )
                                    )->out();
                                $name = get_string('editorpreferences');
                                break;
                            case 'toggle_distractionfree':
                                $url = null;
                                $name = get_string('toggle_distractionfree', 'block_floatingbutton');
                                $this->page->requires->js_call_amd(
                                    'block_floatingbutton/distractionfree',
                                    'init',
                                    ['block_floatingbutton-' . $i]
                                );
                                break;
                        }
                }
                $backgroundcolor =
                    $this->config->customlayout[$i] == 1 ?
                    $this->config->backgroundcolor[$i] :
                    $this->config->defaultbackgroundcolor;
                $textcolor =
                    $this->config->customlayout[$i] == 1 ?
                    $this->config->textcolor[$i] :
                    $this->config->defaulttextcolor;
                $icon = [
                    'name' => empty($this->config->name[$i]) ? $name : $this->config->name[$i],
                    'url' => $url,
                    'icon' => $this->config->icon[$i],
                    'edit' => $edit,
                    'backgroundcolor' => $backgroundcolor,
                    'textcolor' => $textcolor,
                    'notavailable' => $notavailable,
                    'id' => $i,
                    'section' => $section,
                    'collapsed' => $collapsed,
                    'anchor' => $anchor,
                    'sectionnav' => $sectionnav,
                    'internal' => $internal,
                    'prevsection' => $prevsection,
                    'tiles' => $tiles,
                    'external' => $external,
                ];
                $context->icons[] = $icon;
            }
            $this->content->text = $OUTPUT->render_from_template('block_floatingbutton/icons', $context);
        } else {
            $this->content->text = '';
        }

        return $dummy;
    }

    /**
     * Returns false as there can be only one floating button block on one page to avoid collisions.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return false;
    }

    /**
     * Returns on which page formats this block can be used.
     *
     * @return array
     */
    public function applicable_formats(): array {
        return ['site-index' => false, 'course-view' => true, 'mod' => true];
    }
}

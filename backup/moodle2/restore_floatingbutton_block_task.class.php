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
 * Backup class for block_floatingbutton
 *
 * @package    block_floatingbutton
 * @copyright  2022 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_floatingbutton_block_task extends restore_block_task {
    /**
     * Does nothing.
     *
     * @return void
     */
    protected function define_my_settings() {
    }

    /**
     * Does nothing.
     *
     * @return void
     */
    protected function define_my_steps() {
    }

    /**
     * This plugin has no fileareas yet.
     *
     * @return array
     */
    public function get_fileareas() {
        return [];
    }

    /**
     * This function returns an empty array as the restore functions cannot handle
     * arrays in configdata. The link decoding is done in after_restore().
     *
     * @return array
     */
    public function get_configdata_encoded_attributes() {
        return [];
    }

    /**
     * Returns empty array
     *
     * @return array
     */
    public static function define_decode_contents() {
        return [];
    }

    /**
     * Returns empty array
     *
     * @return array
     */
    public static function define_decode_rules() {
        return [];
    }

    /**
     * This method is called after the complete restore process is done. It calls the
     * link decoders again to handle the arrays internalurl and externalurl in configdata.
     *
     * @return void
     */
    public function after_restore() {
        global $DB;

        $blockid = $this->get_blockid();

        $courseid = $this->get_courseid();
        $modinfo = get_fast_modinfo($courseid);

        if ($configdata = $DB->get_field('block_instances', 'configdata', ['id' => $blockid])) {
            $config = $this->decode_configdata($configdata);

            if (!isset($config->internalurl)) {
                $config->internalurl = [];
            }

            if (!isset($config->externalurl)) {
                $config->externalurl = [];
            }

            if (!isset($config->cmid)) {
                $config->cmid = [];
            }

            $decoder = $this->get_decoder();
            $rules = restore_course_task::define_decode_rules();

            foreach ($rules as $rule) {
                $decoder->add_rule($rule);
            }

            foreach ($config->internalurl as $key => $url) {
                $config->internalurl[$key] = $decoder->decode_content($url);
            }

            foreach ($config->externalurl as $key => $url) {
                $config->externalurl[$key] = $decoder->decode_content($url);
            }

            foreach ($config->cmid as $key => $url) {
                [$type, $id] = explode('=', $url);
                if ($type == 'cmid') {
                    $moduleid = restore_dbops::get_backup_ids_record($this->get_restoreid(), 'course_module', $id);
                    if ($moduleid) {
                        $config->cmid[$key] = 'cmid=' . $moduleid->newitemid;
                    } else {
                        if (!isset($modinfo->cms[$id])) {
                            $config->cmid[$key] = null;
                        }
                    }
                }
            }

            $configdata = base64_encode(serialize($config));
            $DB->set_field('block_instances', 'configdata', $configdata, ['id' => $blockid]);
        }
    }
}

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
 * Admin settings for block_floatingbutton
 *
 * @package    block_floatingbutton
 * @copyright  2022 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $distractionfreeselectorsdefault = [
        'nav.fixed-top',
        'header',
        '#nav-drawer',
        '#group_menu',
        'blocks-column',
        '.activity-navigation',
        '.drawer-left-toggle',
        '.drawer-right-toggle',
        'footer',
        '#page-header',
        '.secondary-navigation',
        'bycs-topbar',
        '.mbscontentheader',
    ];

    $nopaddingselectorsdefault = [
        '#page',
        '#topofscroll',
    ];

    $settings->add(new admin_setting_configtext(
        'block_floatingbutton/defaulttextcolor',
        get_string('defaulttextcolor', 'block_floatingbutton'),
        '',
        '#1C1D2F',
        PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext(
        'block_floatingbutton/defaultbackgroundcolor',
        get_string('defaultbackgroundcolor', 'block_floatingbutton'),
        '',
        '#A4A5AC',
        PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtextarea(
        'block_floatingbutton/distractionfreeselectors',
        get_string('distractionfreeselectors', 'block_floatingbutton'),
        get_string('distractionfreeselectors_desc', 'block_floatingbutton'),
        implode("\n", $distractionfreeselectorsdefault),
        PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtextarea(
        'block_floatingbutton/nopaddingselectors',
        get_string('nopaddingselectors', 'block_floatingbutton'),
        get_string('nopaddingselectors_desc', 'block_floatingbutton'),
        implode("\n", $nopaddingselectorsdefault),
        PARAM_TEXT
    ));
    $settings->add(new admin_setting_configcheckbox(
        'block_floatingbutton/closedrawers',
        get_string('close_drawers', 'block_floatingbutton'),
        get_string('close_drawers_desc', 'block_floatingbutton'),
        1
    ));
}

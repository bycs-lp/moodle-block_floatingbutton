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
 * Toggles distraction-free mode.
 *
 * @module     block_floatingbutton/distractionfree
 * @copyright  2025 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = (name) => {
    let button = document.getElementById(name);
    button.addEventListener('click', () => {
        const state = sessionStorage.getItem('block_floatingbutton-distraction-free-button-state');
        if (state === 'true') {
            showDistractions();
            sessionStorage.setItem('block_floatingbutton-distraction-free-button-state', 'false');
        } else {
            hideDistractions();
            sessionStorage.setItem('block_floatingbutton-distraction-free-button-state', 'true');
        }
    });
    const state = sessionStorage.getItem('block_floatingbutton-distraction-free-button-state');
    if (state === 'true') {
        hideDistractions();
    }
};

/**
 * Hides distractions on the page.
 */
function hideDistractions() {
    let selectors = ['nav.fixed-top', 'header', '#nav-drawer',
        '#group_menu', 'blocks-column', '.activity-navigation',
        '.drawer-left-toggle', '.drawer-right-toggle', 'footer',
        '#page-header', '.secondary-navigation', 'bycs-topbar', '.mbscontentheader'
    ];
    let drawers = ['left', 'right'];
    drawers.forEach((s) => {
        const showdrawer = document.querySelector(`#page.show-drawer-${s}`);
        if (showdrawer) {
            const toggles = document.querySelectorAll(`.drawertoggle[data-action="closedrawer"]`);
            if (toggles) {
                toggles.forEach((toggle) => toggle.click());
            } else {
                selectors.push(`.drawer-${s}`);
            }
        }
    });
    selectors.forEach((s) => {
        const els = document.querySelectorAll(s);
        els.forEach((el) => {
            el.classList.add('block_floatingbutton-hidden');
        });
    });
    selectors = ['#page', '#topofscroll'];
    selectors.forEach((s) => {
        const els = document.querySelectorAll(s);
        els.forEach((el) => {
            el.classList.add('block_floatingbutton-nopadding');
        });
    });
}

/**
 * Shows distractions on the page.
 */
function showDistractions() {
    const els = document.querySelectorAll('.block_floatingbutton-hidden, .block_floatingbutton-nopadding');
    els.forEach((el) => {
        el.classList.remove('block_floatingbutton-hidden');
        el.classList.remove('block_floatingbutton-nopadding');
    });
}

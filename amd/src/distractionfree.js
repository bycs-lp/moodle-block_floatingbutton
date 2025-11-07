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

import * as Storage from 'core/sessionstorage';

/**
 * Initializes the distraction-free mode toggle button.
 *
 * @param {string} toggleButtonId - The ID of the toggle button element.
 * @param {Array} distractionSelectors - An array of CSS selectors for distractions to hide.
 * @param {Array} nopaddingSelectors - An array of CSS selectors for elements to remove padding / margin from.
 * @param {boolean} closedrawers - Whether to close open drawers.
 */
export const init = (toggleButtonId, distractionSelectors, nopaddingSelectors, closedrawers) => {
    let button = document.getElementById(toggleButtonId);
    button.addEventListener('click', () => {
        const someHiddenElement = document.querySelector('.block_floatingbutton-hidden, .block_floatingbutton-nopadding');
        if (someHiddenElement) {
            showDistractions();
            Storage.set('block_floatingbutton/distraction-free-button-state', 'false');
        } else {
            hideDistractions(distractionSelectors, nopaddingSelectors, closedrawers);
            Storage.set('block_floatingbutton/distraction-free-button-state', 'true');
        }
    });
    const state = Storage.get('block_floatingbutton/distraction-free-button-state');
    if (state === 'true') {
        hideDistractions(distractionSelectors, nopaddingSelectors, closedrawers);
    }
};

/**
 * Hides distractions on the page.
 * @param {Array} distractionSelectors - An array of CSS selectors for distractions to hide.
 * @param {Array} nopaddingSelectors - An array of CSS selectors for elements to remove padding / margin from.
 * @param {boolean} closedrawers - Whether to close open drawers.
 */
const hideDistractions = (distractionSelectors, nopaddingSelectors, closedrawers = true) => {
    let drawers = ['left', 'right'];
    drawers.forEach((s) => {
        const showdrawer = document.querySelector(`#page.show-drawer-${s}`);
        if (showdrawer && closedrawers) {
            const toggles = document.querySelectorAll(`.drawertoggle[data-action="closedrawer"]`);
            if (toggles) {
                toggles.forEach((toggle) => toggle.click());
            } else {
                distractionSelectors.push(`.drawer-${s}`);
            }
        }
    });
    distractionSelectors.forEach((s) => {
        const els = document.querySelectorAll(s);
        els.forEach((el) => {
            el.classList.add('block_floatingbutton-hidden');
        });
    });
    nopaddingSelectors.forEach((s) => {
        const els = document.querySelectorAll(s);
        els.forEach((el) => {
            el.classList.add('block_floatingbutton-nopadding');
        });
    });
};

/**
 * Shows distractions on the page.
 */
const showDistractions = () => {
    const els = document.querySelectorAll('.block_floatingbutton-hidden, .block_floatingbutton-nopadding');
    els.forEach((el) => {
        el.classList.remove('block_floatingbutton-hidden');
        el.classList.remove('block_floatingbutton-nopadding');
    });
};

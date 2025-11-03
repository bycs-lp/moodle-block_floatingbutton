import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import Modal from 'core/modal';
import ICON_SET from 'block_floatingbutton/iconset';

const SELECTORS = {
  iconpicker: ".block_floatingbutton-iconpicker",
  iconpicker_input: ".block_floatingbutton-input input",
  iconpicker_search_input: ".block_floatingbutton-iconpicker-search-input",
  iconpicker_icon: ".block_floatingbutton-iconpicker-icon",
};

export const init = (iconpickerclass) => {
    // Add index to icon set to make producing an unique id easier.
    ICON_SET.forEach(function(v, i) {
        v.index = i;
    });

    // Make inputs of the moodle form invisible and add button for iconpicker.
    let inputs = Array.from(document.querySelectorAll(SELECTORS.iconpicker_input));
    inputs.forEach(function(input) {
        input.setAttribute('style', 'visibility: collapse; width: 0; margin: 0; padding: 0; position: absolute;');
        input.insertAdjacentHTML(
            'afterend',
            '<button class="' + iconpickerclass + ' btn btn-secondary btn-icon" type="button" id="' + input.name +
            '_button" data-iconpicker-input="' + input.name + '"><i class="' + input.value + '"></i></button>'
        );
    });

    // Attach click listener to each iconpicker buton. The callback function also sets data-iconpicker
    // and data-iconpicker-input attributes.
    let iconpickers = Array.from(document.querySelectorAll("." + iconpickerclass));
    iconpickers.forEach(function(picker) {
        picker.addEventListener('click', function(event) {
            let target = event.target.closest('.' + iconpickerclass);
            buildModal(target.id, target.getAttribute('data-iconpicker-input'));
        });
    });
};

/**
 * Build modal for iconpicker.
 * @param {*} target
 * @param {*} input
 */
const buildModal = async(target, input) => {
    try {
        const {html} = await Templates.renderForPromise('block_floatingbutton/iconpicker', {
            target: target,
            input: input,
            icons: ICON_SET
        });

        const modal = await Modal.create({
            title: 'Icon picker',
            body: html,
            footer: '',
        });

        modal.getRoot().on('modal:shown', function(event) {
            const iconpicker = event.target.querySelector(SELECTORS.iconpicker);
            // Listeners for the icons and the search input have to be registered when modal is shown for the first
            // time because modal doesn't exist in the DOM before.
            const search = iconpicker.querySelector(SELECTORS.iconpicker_search_input);
            search.addEventListener('input', searchicon);
            const icons = Array.from(iconpicker.querySelectorAll(SELECTORS.iconpicker_icon));
            icons.forEach(function(icon) {
                icon.addEventListener('click', (e) => {
                    iconclick(e);
                    modal.hide();
                });
            });
        });

        modal.show();
        highlightselected();
    } catch (ex) {
        displayException(ex);
    }
};

/**
 * Show only icons that match the search value.
 * @param {*} event
 */
function searchicon(event) {
    let modal = event.target.closest(SELECTORS.iconpicker);
    let search = event.target.closest(SELECTORS.iconpicker_search_input);
    let icons = Array.from(modal.querySelectorAll(SELECTORS.iconpicker_icon));
    icons.forEach(function(icon) {
        if (
            icon.getAttribute('data-search').includes(search.value)
        ) {
            icon.setAttribute('style', '');
        } else {
            icon.setAttribute('style', 'display: none;');
        }
    });
}

/**
 * Adds class "highlight" to currently selected icon.
 */
function highlightselected() {
    let icons = Array.from(document.getElementsByClassName(SELECTORS.iconpicker_icon));
    let input = document.querySelector(SELECTORS.iconpicker).getAttribute('data-iconpicker-input');
    if (!(input === null)) {
        let iconclass = document.querySelector(`[name="${input}"]`).getAttribute('value');
        icons.forEach(function(icondiv) {
            icondiv.classList.remove('highlight');
            if (icondiv.querySelector('i').classList == iconclass) {
                icondiv.classList.add('highlight');
            }
        });
    }
}

/**
 * Function called when user clicks on an icon
 * @param {*} event
 */
function iconclick(event) {
    let modal = event.target.closest(SELECTORS.iconpicker);
    let target = modal.getAttribute('data-iconpicker');
    let input = modal.getAttribute('data-iconpicker-input');
    if (target) {
        let icondiv = event.target.closest(SELECTORS.iconpicker_icon);
        let iconclass = document.querySelector('#' + icondiv.id + ' i').classList;
        document.getElementById(target).innerHTML = '<i class="' + iconclass + '"></i>';
        document.querySelector(`[name="${input}"]`).setAttribute('value', iconclass);
    }
    highlightselected(modal);
}

import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import ICON_SET from 'block_floatingbutton/iconset';

export const init = (iconpickerclass) => {
    // Add index to icon set to make producing an unique id easier.
    ICON_SET.forEach(function(v, i) {
        v.index = i;
    });

    // Make inputs of the moodle form invisible and add button for iconpicker.
    let inputs = Array.from(document.querySelectorAll('.block_floatingbutton-input input'));
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
function buildModal(target, input) {
    // Build iconpicker modal with moodle modal factory
    Templates.renderForPromise('block_floatingbutton/iconpicker', {target: target, input: input, icons: ICON_SET})
        .then(({html}) => {
            require(['jquery', 'core/modal_factory'], function($, ModalFactory) {
                var trigger = $('#create-modal');
                ModalFactory.create({
                    title: 'Icon picker',
                    body: html,
                    footer: '',
                }, trigger)
                .done(function(modal) {
                    modal.getRoot().on('modal:shown', function(event) {
                        let iconpicker = event.target.querySelector('.block_floatingbutton-iconpicker');
                        // Listeners for the icons and the search input have to be registered when modal is shown for the first
                        // time because modal doesn't exist in the DOM before.
                        let search = iconpicker.querySelector('.block_floatingbutton-iconpicker-search-input');
                        search.addEventListener('input', searchicon);
                        let icons = Array.from(iconpicker.querySelectorAll('.block_floatingbutton-iconpicker-icon'));
                        icons.forEach(function(icon) {
                            icon.addEventListener('click', (e) => {
                                iconclick(e);
                                modal.hide();
                            });
                        });
                    });
                    modal.show();
                    highlightselected();
                });
            });
            return true;
        }).catch(ex => displayException(ex));
}

/**
 * Show only icons that match the search value.
 * @param {*} event
 */
function searchicon(event) {
    let modal = event.target.closest('.block_floatingbutton-iconpicker');
    let search = event.target.closest('.block_floatingbutton-iconpicker-search-input');
    let icons = Array.from(modal.querySelectorAll('.block_floatingbutton-iconpicker-icon'));
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
    let icons = Array.from(document.getElementsByClassName('block_floatingbutton-iconpicker-icon'));
    let input = document.querySelector('.block_floatingbutton-iconpicker').getAttribute('data-iconpicker-input');
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
    let modal = event.target.closest('.block_floatingbutton-iconpicker');
    let target = modal.getAttribute('data-iconpicker');
    let input = modal.getAttribute('data-iconpicker-input');
    if (target) {
        let icondiv = event.target.closest('.block_floatingbutton-iconpicker-icon');
        let iconclass = document.querySelector('#' + icondiv.id + ' i').classList;
        document.getElementById(target).innerHTML = '<i class="' + iconclass + '"></i>';
        document.querySelector(`[name="${input}"]`).setAttribute('value', iconclass);
    }
    highlightselected(modal);
}

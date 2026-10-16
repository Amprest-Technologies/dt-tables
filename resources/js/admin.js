import './utils/toastr';
import hljs from 'highlight.js/lib/core';
import php from 'highlight.js/lib/languages/php';
import xml from 'highlight.js/lib/languages/xml';
import json from 'highlight.js/lib/languages/json';
import bash from 'highlight.js/lib/languages/bash';
import javascript from 'highlight.js/lib/languages/javascript';

//  Register the languages the documentation is written in
hljs.registerLanguage('php', php);
hljs.registerLanguage('xml', xml);
hljs.registerLanguage('json', json);
hljs.registerLanguage('bash', bash);
hljs.registerLanguage('javascript', javascript);

/* -----------------------------------------------------------------
 * Set up the dialog that registers a table
 * ------------------------------------------------------------------
 */
let setupTableDialog = function () {
    //  Get the dialog
    let dialog = document.getElementById('new-table');

    //  Nothing to set up on pages without the dialog
    if (! dialog) { return; }

    //  Open the dialog from any button that asks for it
    document.querySelectorAll('[data-open-dialog]').forEach(button => {
        button.addEventListener('click', () => {
            //  Show the dialog
            dialog.showModal();

            //  Start on the key
            dialog.querySelector('input[name="key"]').focus();
        });
    });

    //  Close the dialog from its cancel button
    dialog.querySelector('[data-close-dialog]').addEventListener('click', () => dialog.close());

    //  Close the dialog by clicking the backdrop
    dialog.addEventListener('click', event => event.target === dialog && dialog.close());

    //  Reopen the dialog when the last attempt failed validation
    if (dialog.hasAttribute('data-open')) {
        dialog.showModal();
    }
};

/* -----------------------------------------------------------------
 * Set up the search and mode filter of the table cards
 * ------------------------------------------------------------------
 */
let setupTableFilter = function () {
    //  Get the search box, the cards and the mode tabs
    let search = document.getElementById('table-search');
    let cards = [...document.querySelectorAll('.table-card')];
    let tabs = [...document.querySelectorAll('[data-mode-filter]')];

    //  Nothing to filter on pages without cards
    if (! search) { return; }

    //  Track the mode being shown, where an empty one shows every mode
    let selectedMode = '';

    //  Show the cards that match, and the empty message when none do
    let applyFilter = function () {
        //  Get the text being searched for
        let text = search.value.trim().toLowerCase();

        //  Count the cards left showing
        let visible = 0;

        //  Show or hide each card
        cards.forEach(card => {
            //  A card matches on its key and the selected mode
            let matches = card.dataset.key.includes(text)
                && (! selectedMode || card.dataset.mode === selectedMode);

            //  Toggle the card
            card.classList.toggle('hidden', ! matches);

            //  Count the card
            visible += matches ? 1 : 0;
        });

        //  Show the message when no card is left
        document.getElementById('no-results').classList.toggle('hidden', visible > 0);
    };

    //  Mark the tab of the selected mode, which the markup styles
    let selectTab = function () {
        tabs.forEach(tab => tab.setAttribute('aria-selected', tab.dataset.modeFilter === selectedMode));
    };

    //  Filter as the user types
    search.addEventListener('input', applyFilter);

    //  Filter when a mode tab is chosen
    tabs.forEach(tab => tab.addEventListener('click', () => {
        //  Remember the mode
        selectedMode = tab.dataset.modeFilter;

        //  Mark the tab
        selectTab();

        //  Filter the cards
        applyFilter();
    }));
};

/* -----------------------------------------------------------------
 * Set up the section navigation of the edit page
 * ------------------------------------------------------------------
 */
let setupSectionNav = function () {
    //  Get the page that holds the sections
    let page = document.querySelector('[data-section-page]');

    //  Nothing to set up on pages without sections
    if (! page) { return; }

    //  Get the sections and the links that open them
    let sections = [...page.querySelectorAll('[data-section]')];
    let links = [...page.querySelectorAll('[data-section-link]')];

    //  Get the section the server opened, which is the one holding any error
    let opened = sections.find(section => ! section.classList.contains('hidden'))?.dataset.section;

    //  Read the section last used for this table, where storage may be blocked
    let remembered = function () {
        try { return sessionStorage.getItem(page.dataset.storageKey); } catch (error) { return null; }
    };

    //  Show one section and hide the rest
    let show = function (id) {
        //  Toggle each section
        sections.forEach(section => section.classList.toggle('hidden', section.dataset.section !== id));

        //  Mark its link
        links.forEach(link => link.setAttribute('aria-selected', link.dataset.sectionLink === id));

        //  Keep the section in the address so the page can be shared and reloaded
        history.replaceState(null, '', `#${id}`);

        //  Remember it for the next visit
        try { sessionStorage.setItem(page.dataset.storageKey, id); } catch (error) { /* Storage is optional */ }
    };

    //  Open the section of a link when it is clicked
    links.forEach(link => link.addEventListener('click', () => show(link.dataset.sectionLink)));

    //  Pick the section to start on, where a section with errors always wins
    let hasErrors = document.querySelector('[data-section]:not(.hidden) small.text-red-700') !== null;
    let wanted = hasErrors ? opened : (location.hash.slice(1) || remembered() || opened);

    //  Open it when it exists
    if (sections.some(section => section.dataset.section === wanted)) {
        show(wanted);
    }
};

/* -----------------------------------------------------------------
 * Set up the buttons that copy a block of code
 * ------------------------------------------------------------------
 */
let setupCopyButtons = function () {
    document.querySelectorAll('[data-copy]').forEach(button => {
        button.addEventListener('click', () => {
            //  Get the text to copy
            let text = document.querySelector(button.dataset.copy).textContent;

            //  Copy it, falling back to a hidden field where the clipboard is not available
            let copied = navigator.clipboard
                ? navigator.clipboard.writeText(text)
                : new Promise(resolve => {
                    //  Create the field
                    let field = document.createElement('textarea');

                    //  Fill and select it
                    field.value = text;
                    document.body.appendChild(field);
                    field.select();

                    //  Copy and remove it
                    document.execCommand('copy');
                    field.remove();
                    resolve();
                });

            //  Confirm with the label of the button
            copied.then(() => {
                //  Keep the label to restore it
                let label = button.textContent;

                //  Show the confirmation
                button.textContent = 'Copied';

                //  Restore the label
                setTimeout(() => button.textContent = label, 1500);
            });
        });
    });
};

/* -----------------------------------------------------------------
 * Set up a page of the documentation: colour its code and list its headings
 * ------------------------------------------------------------------
 */
let setupDocs = function () {
    //  Get the article
    let article = document.querySelector('[data-docs-article]');

    //  Nothing to set up on pages without an article
    if (! article) { return; }

    //  Colour every code block
    article.querySelectorAll('pre code').forEach(block => hljs.highlightElement(block));

    //  Get the list that holds the headings of the page
    let list = document.querySelector('[data-docs-toc]');

    //  Give each heading a link to itself, and a place in the list
    article.querySelectorAll('h2, h3').forEach(heading => {
        //  Build an id from the text of the heading
        heading.id = heading.id || heading.textContent.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');

        //  Add the heading to the list
        if (list) {
            //  Create the item
            let item = document.createElement('li');

            //  Indent the sub-headings
            item.className = heading.tagName === 'H3' ? 'pl-3' : '';

            //  Create the link
            let link = document.createElement('a');

            //  Point it at the heading
            link.href = `#${heading.id}`;
            link.textContent = heading.textContent;
            link.className = 'block py-1 text-gray-500 hover:text-primary-darker';

            //  Put the link in the list
            item.append(link);
            list.append(item);
        }
    });

    //  Hide the column of headings on pages that have none
    if (list && ! list.children.length) {
        list.closest('aside').classList.add('hidden!');
    }
};

//  Set up the table list page
setupTableDialog();
setupTableFilter();

//  Set up the table edit page
setupSectionNav();

//  Set up the documentation and the copy buttons its code blocks use
setupDocs();
setupCopyButtons();

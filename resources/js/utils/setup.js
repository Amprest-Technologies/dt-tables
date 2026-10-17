/* -----------------------------------------------------------------
 * Set up the dialog that registers a table
 * ------------------------------------------------------------------
 */
export let setupTableDialog = function () {
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
 * Set up the search, the mode filter and the pages of the table cards
 * ------------------------------------------------------------------
 */
export let setupTableFilter = function () {
    //  Get the search box, the grid, the cards and the mode tabs
    let search = document.getElementById('table-search');
    let grid = document.getElementById('table-grid');
    let cards = [...document.querySelectorAll('.table-card')];
    let tabs = [...document.querySelectorAll('[data-mode-filter]')];

    //  Nothing to filter on pages without cards
    if (! search) { return; }

    //  Get the pager, the template of its page buttons and the size of a page
    let pager = document.getElementById('table-pager');
    let template = document.getElementById('pager-page');
    let perPage = Number(grid.dataset.perPage) || 6;

    //  Track the mode being shown, where an empty one shows every mode, and the page being shown
    let selectedMode = '';
    let page = 1;

    //  Draw the pager for the cards that match
    let drawPager = function (total, pages) {
        //  Only offer pages when there is more than one
        pager.classList.toggle('hidden', pages <= 1);

        //  Say which cards are showing
        let first = (page - 1) * perPage + 1;
        let last = Math.min(page * perPage, total);
        pager.querySelector('[data-pager-summary]').textContent = `Showing ${first}\u2013${last} of ${total} tables`;

        //  Switch off the ends
        pager.querySelector('[data-pager="prev"]').disabled = page === 1;
        pager.querySelector('[data-pager="next"]').disabled = page === pages;

        //  Replace the page buttons
        pager.querySelectorAll('[data-page]').forEach(button => button.remove());

        //  Add one button per page, before the next button
        for (let number = 1; number <= pages; number++) {
            //  Create the button from the template
            let button = template.content.firstElementChild.cloneNode(true);

            //  Fill it in
            button.textContent = number;
            button.dataset.page = number;
            button.setAttribute('aria-current', number === page ? 'page' : 'false');

            //  Put it in the pager
            pager.querySelector('[data-pager="next"]').before(button);
        }
    };

    //  Show the page of cards that match, and the empty message when none do
    let render = function () {
        //  Get the text being searched for
        let text = search.value.trim().toLowerCase();

        //  Find the cards that match on their key and the selected mode
        let matching = cards.filter(card => card.dataset.key.includes(text)
            && (! selectedMode || card.dataset.mode === selectedMode));

        //  Work out the pages, and stay on one that exists
        let pages = Math.max(1, Math.ceil(matching.length / perPage));
        page = Math.min(page, pages);

        //  Hide every card, then show the ones on this page
        cards.forEach(card => card.classList.add('hidden'));
        matching.slice((page - 1) * perPage, page * perPage).forEach(card => card.classList.remove('hidden'));

        //  Show the message when no card matches
        document.getElementById('no-results').classList.toggle('hidden', matching.length > 0);

        //  Draw the pager
        drawPager(matching.length, pages);
    };

    //  Mark the tab of the selected mode, which the markup styles
    let selectTab = function () {
        tabs.forEach(tab => tab.setAttribute('aria-selected', tab.dataset.modeFilter === selectedMode));
    };

    //  Filter as the user types, starting again from the first page
    search.addEventListener('input', () => {
        page = 1;
        render();
    });

    //  Filter when a mode tab is chosen
    tabs.forEach(tab => tab.addEventListener('click', () => {
        //  Remember the mode and start from the first page
        selectedMode = tab.dataset.modeFilter;
        page = 1;

        //  Mark the tab
        selectTab();

        //  Show the cards
        render();
    }));

    //  Move between pages
    pager.addEventListener('click', event => {
        //  Find the button that was clicked
        let button = event.target.closest('button');

        //  Ignore clicks that are not on a button
        if (! button) { return; }

        //  Work out the page it leads to
        if (button.dataset.pager === 'prev') { page--; }
        else if (button.dataset.pager === 'next') { page++; }
        else { page = Number(button.dataset.page); }

        //  Show it
        render();
    });

    //  Show the first page
    render();
};

/* -----------------------------------------------------------------
 * Set up the section navigation of the edit page
 * ------------------------------------------------------------------
 */
export let setupSectionNav = function () {
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

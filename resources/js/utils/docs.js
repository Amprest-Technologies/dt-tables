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
 * Set up a page of the documentation: colour its code and list its headings
 * ------------------------------------------------------------------
 */
export let setupDocs = function () {
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

/* -----------------------------------------------------------------
 * Set up the buttons that copy a block of code
 * ------------------------------------------------------------------
 */
export let setupCopyButtons = function () {
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

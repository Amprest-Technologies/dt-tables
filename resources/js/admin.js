import './utils/toastr';
import { setupDocs, setupCopyButtons } from './utils/docs';
import { setupTableDialog, setupTableFilter, setupSectionNav } from './utils/setup';

//  Set up the dialog that registers a table on the list page
setupTableDialog();

//  Set up the search, the mode filter and the pages of the table cards on the list page
setupTableFilter();

//  Set up the section navigation of the edit page
setupSectionNav();

//  Colour the code and list the headings of a documentation page
setupDocs();

//  Set up the buttons that copy a block of code in the documentation
setupCopyButtons();

import './utils/toastr';
import { setupTableDialog, setupTableFilter, setupSectionNav } from './utils/setup';

//  Set up the dialog that registers a table on the list page
setupTableDialog();

//  Set up the search, the mode filter and the pages of the table cards on the list page
setupTableFilter();

//  Set up the section navigation of the edit page
setupSectionNav();

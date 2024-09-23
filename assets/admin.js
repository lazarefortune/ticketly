import './bootstrap.js';
import './scss/admin.scss'

import {createIcons, icons} from 'lucide';

createIcons({icons});
/* Elements */
import './elements/index'
import '@grafikart/drop-files-element'
/* Libs */
import './libs/flatpickr'
import './libs/select2'
/* Modules */
import './modules/modal.js'
import './modules/dropdown.js'
import './modules/scrollreveal.js'
import './modules/admin/sidebar.js'
import './modules/admin/sidebar-dropdown.js'
import './modules/address-autocomplete.js'
/* ===== Pages ===== */
import './pages/index.js'

// start the Stimulus application
import './bootstrap'

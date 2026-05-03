/**
 * ============================================================
 * FITXER: resources/js/bootstrap.js
 * ============================================================
 * ROL DINS L'ECOSISTEMA:
 *   Configura les llibreries i dependències necessàries per a 
 *   Laravel. Per defecte, carrega Axios per gestionar les 
 *   peticions HTTP asíncrones.
 * ============================================================
 */

import axios from 'axios';

// Posem Axios en l'àmbit global per facilitar el seu ús si cal 
// scripting a les vistes Blade.
window.axios = axios;

// Configurem la capçalera estàndard per a peticions AJAX de Laravel.
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

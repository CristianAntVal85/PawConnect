import './bootstrap';
import Alpine from 'alpinejs';
import * as API from './api.js';
import * as Favorites from './favorites.js';
import * as Messages from './messages.js';
import * as Walks from './walks.js';
import './marketplace.js';

// Exponer módulos globalmente para uso en vistas Blade
window.Alpine = Alpine;
window.API = API;
window.Favorites = Favorites;
window.Messages = Messages;
window.Walks = Walks;

Alpine.start();

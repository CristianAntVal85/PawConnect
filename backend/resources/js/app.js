import './bootstrap';
import Alpine from 'alpinejs';
import * as API from './api.js';
import * as Favorites from './favorites.js';
import * as Messages from './messages.js';
import * as Walks from './walks.js';
import breedSelectorComponent from './breed-selector';

// Exponer módulos globalmente para uso en vistas Blade ANTES de registrar Alpine.data
window.Alpine = Alpine;
window.API = API;
window.Favorites = Favorites;
window.Messages = Messages;
window.Walks = Walks;

// Hacer que breedSelector esté disponible globalmente antes de Alpine.start()
window.breedSelector = breedSelectorComponent;

// Registrar componentes Alpine - la función se llamará con los argumentos de x-data
Alpine.data('breedSelector', (initialSelected = 'all') => breedSelectorComponent(initialSelected));

Alpine.start();

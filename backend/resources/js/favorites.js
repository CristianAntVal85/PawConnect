/**
 * Favorite / Place Management
 * Acciones sin recarga para guardar/eliminar lugares favoritos
 */

import { apiPost, apiDelete, getErrorMessage } from './api.js';

/**
 * Guarda un lugar como favorito
 * @param {number} placeId - ID del lugar
 * @returns {Promise<object>} Respuesta de la API
 */
export async function addFavorite(placeId) {
    try {
        const response = await apiPost(`/places/${placeId}/favorites`);
        return { success: true, data: response };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

/**
 * Elimina un lugar de favoritos
 * @param {number} placeId - ID del lugar
 * @returns {Promise<object>} Respuesta de la API
 */
export async function removeFavorite(placeId) {
    try {
        const response = await apiDelete(`/places/${placeId}/favorites`);
        return { success: true, data: response };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

/**
 * Toggle favorito (agrega si no existe, elimina si existe)
 * Útil para botones que alternan estado
 * @param {number} placeId - ID del lugar
 * @param {boolean} isFavorited - Estado actual
 * @returns {Promise<object>} Respuesta con nuevo estado
 */
export async function toggleFavorite(placeId, isFavorited) {
    if (isFavorited) {
        return removeFavorite(placeId);
    } else {
        return addFavorite(placeId);
    }
}

/**
 * Maneja click del botón favorito en la UI
 * Actualiza visualmente mientras se envía a la API
 * @param {HTMLElement} button - Elemento botón
 * @param {number} placeId - ID del lugar
 * @param {boolean} isFavorited - Estado actual
 */
export async function handleFavoriteClick(button, placeId, isFavorited) {
    // Deshabilitar botón durante la petición
    button.disabled = true;
    const originalContent = button.innerHTML;

    try {
        const result = await toggleFavorite(placeId, isFavorited);

        if (result.success) {
            // Cambiar ícono/estado visualmente
            button.classList.toggle('text-red-500');
            button.classList.toggle('text-gray-300');
            button.setAttribute('aria-pressed', !isFavorited);
        } else {
            // Mostrar error
            alert(`Error: ${result.message}`);
            button.disabled = false;
        }
    } catch (error) {
        alert(`Error al guardar favorito: ${error.message}`);
        button.disabled = false;
    }
}

export default {
    addFavorite,
    removeFavorite,
    toggleFavorite,
    handleFavoriteClick,
};

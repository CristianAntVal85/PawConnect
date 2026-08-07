/**
 * Walks Management
 * Unirse/abandonar paseos sin recarga de página
 */

import { apiPost, apiDelete, getErrorMessage } from './api.js';

/**
 * Se une a un paseo con una mascota
 * @param {number} walkId - ID del paseo
 * @param {number} petId - ID de la mascota del usuario
 * @returns {Promise<object>} Respuesta de la API
 */
export async function joinWalk(walkId, petId) {
    try {
        const response = await apiPost(`/walks/${walkId}/join`, {
            pet_id: petId,
        });
        return { success: true, data: response };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

/**
 * Abandona un paseo
 * @param {number} walkId - ID del paseo
 * @returns {Promise<object>} Respuesta de la API
 */
export async function leaveWalk(walkId) {
    try {
        const response = await apiDelete(`/walks/${walkId}/leave`);
        return { success: true, data: response };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

/**
 * Toggle participación en paseo
 * @param {number} walkId - ID del paseo
 * @param {number} petId - ID de la mascota
 * @param {boolean} isJoined - ¿Ya está en el paseo?
 * @returns {Promise<object>}
 */
export async function toggleWalkParticipation(walkId, petId, isJoined) {
    if (isJoined) {
        return leaveWalk(walkId);
    } else {
        return joinWalk(walkId, petId);
    }
}

/**
 * Maneja click del botón unirse/abandonar en la UI
 * @param {HTMLElement} button - Elemento botón
 * @param {number} walkId - ID del paseo
 * @param {number} petId - ID de la mascota (requerido para join)
 * @param {boolean} isJoined - Estado actual
 */
export async function handleWalkToggleClick(button, walkId, petId, isJoined) {
    // Validar petId si se quiere unir
    if (!isJoined && !petId) {
        alert('Debes seleccionar una mascota para unirte al paseo');
        return;
    }

    button.disabled = true;
    const originalContent = button.textContent;

    try {
        const result = await toggleWalkParticipation(walkId, petId, isJoined);

        if (result.success) {
            // Cambiar texto y estilo del botón
            if (isJoined) {
                button.textContent = 'Unirse';
                button.classList.remove('bg-red-500', 'hover:bg-red-600');
                button.classList.add('bg-blue-500', 'hover:bg-blue-600');
            } else {
                button.textContent = 'Abandonar';
                button.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                button.classList.add('bg-red-500', 'hover:bg-red-600');
            }

            // Actualizar atributo de estado
            button.setAttribute('data-joined', !isJoined);
        } else {
            alert(`Error: ${result.message}`);
            button.disabled = false;
        }
    } catch (error) {
        alert(`Error: ${error.message}`);
        button.disabled = false;
    }
}

/**
 * Obtiene los participantes de un paseo
 * @param {number} walkId - ID del paseo
 * @returns {Promise<object>}
 */
export async function getWalkParticipants(walkId) {
    try {
        const response = await fetch(`/api/v1/walks/${walkId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`,
            },
        });
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Error al obtener paseo');
        }

        return { success: true, data: data.data };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

/**
 * Actualiza el contador de participantes
 * @param {HTMLElement} container - Elemento que muestra el contador
 * @param {number} walkId - ID del paseo
 */
export async function updateParticipantCount(container, walkId) {
    const result = await getWalkParticipants(walkId);
    
    if (result.success) {
        const count = result.data.walk_pet?.length || 0;
        const max = result.data.max_pets || '∞';
        container.textContent = `${count}/${max} participantes`;
    }
}

export default {
    joinWalk,
    leaveWalk,
    toggleWalkParticipation,
    handleWalkToggleClick,
    getWalkParticipants,
    updateParticipantCount,
};

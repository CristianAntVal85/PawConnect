/**
 * Message Management
 * Envío de mensajes sin recarga de página
 * Integración futura con Laravel Reverb para tiempo real
 */

import { apiPost, getErrorMessage } from './api.js';

/**
 * Envía un mensaje en una conversación
 * @param {number} matchId - ID del match/conversación
 * @param {string} body - Contenido del mensaje
 * @returns {Promise<object>} Mensaje creado
 */
export async function sendMessage(matchId, body) {
    try {
        const response = await apiPost(`/matches/${matchId}/messages`, {
            body: body.trim(),
        });
        return { success: true, data: response };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

/**
 * Marca un mensaje como leído
 * @param {number} matchId - ID del match
 * @param {number} messageId - ID del mensaje
 * @returns {Promise<object>}
 */
export async function markMessageAsRead(matchId, messageId) {
    try {
        const response = await apiPost(`/matches/${matchId}/messages/${messageId}/read`);
        return { success: true, data: response };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

/**
 * Obtiene los mensajes sin recarga
 * @param {number} matchId - ID del match
 * @param {object} options - { page, per_page }
 * @returns {Promise<object>}
 */
export async function fetchMessages(matchId, options = {}) {
    try {
        const params = new URLSearchParams(options);
        const response = await fetch(`/api/v1/matches/${matchId}/messages?${params}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`,
            },
        });
        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.message || 'Error al obtener mensajes');
        }

        return { success: true, data };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

/**
 * Maneja el envío de mensaje en un formulario
 * @param {HTMLFormElement} form - Formulario del mensaje
 * @param {number} matchId - ID del match
 * @param {HTMLElement} messagesContainer - Contenedor de mensajes (opcional)
 */
export async function handleMessageSubmit(form, matchId, messagesContainer = null) {
    const textarea = form.querySelector('textarea[name="body"]');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (!textarea || !textarea.value.trim()) {
        alert('El mensaje no puede estar vacío');
        return;
    }

    // Deshabilitar mientras se envía
    submitBtn.disabled = true;
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Enviando...';

    try {
        const result = await sendMessage(matchId, textarea.value);

        if (result.success) {
            // Limpiar textarea
            textarea.value = '';
            textarea.focus();

            // Agregar mensaje a la UI si se proporciona contenedor
            if (messagesContainer) {
                appendMessageToUI(messagesContainer, result.data.data);
                // Scroll al último mensaje
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        } else {
            alert(`Error: ${result.message}`);
        }
    } catch (error) {
        alert(`Error al enviar mensaje: ${error.message}`);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
}

/**
 * Agrega un mensaje a la UI sin recarga
 * @param {HTMLElement} container - Contenedor de mensajes
 * @param {object} message - Datos del mensaje
 */
function appendMessageToUI(container, message) {
    const messageEl = document.createElement('div');
    messageEl.className = 'mb-4 flex gap-2';
    
    // Alternancia derecha/izquierda basada en sender_id
    const isOwn = message.sender_id === parseInt(document.body.dataset.userId);
    if (isOwn) {
        messageEl.classList.add('justify-end');
    }

    messageEl.innerHTML = `
        <div class="max-w-xs px-3 py-2 rounded-lg ${isOwn ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'}">
            <p class="text-sm">${escapeHtml(message.body)}</p>
            <span class="text-xs opacity-70">${formatTime(message.created_at)}</span>
        </div>
    `;

    container.appendChild(messageEl);
}

/**
 * Escapa HTML para evitar XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Formatea timestamp a hora legible
 */
function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

export default {
    sendMessage,
    markMessageAsRead,
    fetchMessages,
    handleMessageSubmit,
};

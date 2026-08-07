/**
 * API Wrapper with Fetch API + Sanctum Token Management
 * Centraliza todas las llamadas AJAX con autenticación automática
 */

const API_BASE = '/api/v1';

/**
 * Obtiene el token CSRF del DOM (si existe, para compatibilidad)
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/**
 * Obtiene el token Sanctum del localStorage
 * Se guardó en el login/registro
 */
function getAuthToken() {
    return localStorage.getItem('auth_token') || '';
}

/**
 * Wrapper central para todas las peticiones fetch
 * @param {string} endpoint - Ruta de la API (ej: /pets, /matches/like/1)
 * @param {object} options - Configuración fetch adicional
 * @returns {Promise<object>} Respuesta JSON de la API
 */
async function apiFetch(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`;
    const token = getAuthToken();

    const defaultHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };

    // Agregar token de autenticación si existe
    if (token) {
        defaultHeaders['Authorization'] = `Bearer ${token}`;
    }

    // Agregar CSRF para peticiones que modifiquen datos (si existe en meta)
    const csrfToken = getCsrfToken();
    if (csrfToken && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(options.method?.toUpperCase())) {
        defaultHeaders['X-CSRF-Token'] = csrfToken;
    }

    const config = {
        ...options,
        headers: {
            ...defaultHeaders,
            ...options.headers,
        },
    };

    try {
        const response = await fetch(url, config);

        // Manejo de respuestas sin contenido
        if (response.status === 204) {
            return { success: true, data: null };
        }

        const data = await response.json();

        // Si la respuesta no es exitosa, lanzar error
        if (!response.ok) {
            const error = new Error(data.message || `HTTP ${response.status}`);
            error.status = response.status;
            error.data = data;
            throw error;
        }

        return data;
    } catch (error) {
        // Log para debugging
        console.error(`[API Error] ${options.method || 'GET'} ${url}:`, error);
        throw error;
    }
}

/**
 * GET - Obtener datos
 */
export async function apiGet(endpoint) {
    return apiFetch(endpoint, {
        method: 'GET',
    });
}

/**
 * POST - Crear recurso
 */
export async function apiPost(endpoint, body = {}) {
    return apiFetch(endpoint, {
        method: 'POST',
        body: JSON.stringify(body),
    });
}

/**
 * PUT - Actualizar recurso
 */
export async function apiPut(endpoint, body = {}) {
    return apiFetch(endpoint, {
        method: 'PUT',
        body: JSON.stringify(body),
    });
}

/**
 * DELETE - Eliminar recurso
 */
export async function apiDelete(endpoint) {
    return apiFetch(endpoint, {
        method: 'DELETE',
    });
}

/**
 * PATCH - Actualización parcial
 */
export async function apiPatch(endpoint, body = {}) {
    return apiFetch(endpoint, {
        method: 'PATCH',
        body: JSON.stringify(body),
    });
}

/**
 * Guarda el token de autenticación (usado en login/registro)
 */
export function setAuthToken(token) {
    if (token) {
        localStorage.setItem('auth_token', token);
    }
}

/**
 * Limpia el token (usado en logout)
 */
export function clearAuthToken() {
    localStorage.removeItem('auth_token');
}

/**
 * Verifica si el usuario está autenticado
 */
export function isAuthenticated() {
    return !!getAuthToken();
}

/**
 * Manejador de errores genérico para mostrar en UI
 * @param {Error} error - Error de fetch
 * @returns {string} Mensaje de error para mostrar al usuario
 */
export function getErrorMessage(error) {
    if (error.data?.message) {
        return error.data.message;
    }
    if (error.data?.errors) {
        // Errores de validación (Laravel Form Requests)
        return Object.values(error.data.errors)
            .flat()
            .join(', ');
    }
    if (error.status === 401) {
        return 'No autorizado. Por favor inicia sesión.';
    }
    if (error.status === 403) {
        return 'No tienes permiso para realizar esta acción.';
    }
    if (error.status === 404) {
        return 'Recurso no encontrado.';
    }
    if (error.status >= 500) {
        return 'Error del servidor. Intenta más tarde.';
    }
    return error.message || 'Error desconocido';
}

export default {
    apiGet,
    apiPost,
    apiPut,
    apiDelete,
    apiPatch,
    setAuthToken,
    clearAuthToken,
    isAuthenticated,
    getErrorMessage,
};

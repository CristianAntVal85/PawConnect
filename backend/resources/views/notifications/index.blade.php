@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Notificaciones</h1>
            <p class="text-gray-600 mt-2">Mantente actualizado sobre tu mascotas y pedidos</p>
        </div>

        <!-- Filtros -->
        <div class="mb-6 flex gap-2">
            <button 
                @click="filter = 'all'"
                :class="filter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'"
                class="px-4 py-2 rounded-lg transition-colors"
            >
                Todas
            </button>
            <button 
                @click="filter = 'unread'"
                :class="filter === 'unread' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'"
                class="px-4 py-2 rounded-lg transition-colors"
            >
                Sin leer
            </button>
        </div>

        <!-- Lista de notificaciones -->
        <div class="space-y-4" x-data="notificationsPage()">
            <template x-if="filteredNotifications.length === 0">
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <div class="text-5xl mb-4">📭</div>
                    <p class="text-gray-500">No tienes notificaciones</p>
                </div>
            </template>

            <template x-if="filteredNotifications.length > 0">
                <template x-for="notification in filteredNotifications" :key="notification.id">
                    <div 
                        :class="notification.read_at ? 'bg-white' : 'bg-blue-50 border-l-4 border-blue-600'"
                        class="bg-white rounded-lg shadow p-6 transition-all"
                    >
                        <!-- Contenido para notificaciones de salud -->
                        <template x-if="notification.data.type === 'App\\\\Notifications\\\\HealthReminderNotification'">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 text-2xl">🏥</div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="font-semibold text-gray-900">
                                                Recordatorio de salud para 
                                                <span x-text="notification.data.pet_name" class="text-blue-600"></span>
                                            </h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <span x-text="notification.data.type_label"></span>
                                                próxima el
                                                <span x-text="formatDateFull(notification.data.next_due_date)" class="font-medium"></span>
                                            </p>
                                        </div>
                                        <template x-if="!notification.read_at">
                                            <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                                Sin leer
                                            </span>
                                        </template>
                                    </div>
                                    
                                    <p class="text-sm text-gray-700 mt-3">
                                        <span x-text="notification.data.message"></span>
                                    </p>

                                    <div class="mt-4 flex gap-3">
                                        <a 
                                            :href="`/pets/${notification.data.pet_id}/health`"
                                            class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors"
                                        >
                                            Ver historial →
                                        </a>
                                        <template x-if="!notification.read_at">
                                            <button 
                                                @click="markAsRead(notification.id)"
                                                class="inline-flex items-center px-3 py-2 text-gray-600 text-sm hover:bg-gray-100 rounded-lg transition-colors"
                                            >
                                                Marcar como leída
                                            </button>
                                        </template>
                                    </div>

                                    <p class="text-xs text-gray-500 mt-4">
                                        <span x-text="formatDate(notification.created_at)"></span>
                                    </p>
                                </div>
                            </div>
                        </template>

                        <!-- Notificación genérica -->
                        <template x-if="notification.data.type !== 'App\\\\Notifications\\\\HealthReminderNotification'">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 text-2xl">🔔</div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-semibold text-gray-900" x-text="notification.type"></h3>
                                            <p class="text-sm text-gray-600 mt-1">Nueva notificación</p>
                                        </div>
                                        <template x-if="!notification.read_at">
                                            <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                                                Sin leer
                                            </span>
                                        </template>
                                    </div>

                                    <p class="text-xs text-gray-500 mt-4">
                                        <span x-text="formatDate(notification.created_at)"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </template>
        </div>

        <!-- Paginación -->
        <div class="mt-8">
            <a href="{{ route('web.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                ← Volver al dashboard
            </a>
        </div>
    </div>
</div>

<script>
function notificationsPage() {
    return {
        notifications: [],
        filter: 'all',

        async init() {
            await this.loadAllNotifications();
        },

        async loadAllNotifications() {
            try {
                const response = await fetch('/api/v1/notifications?per_page=100', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]')?.content || ''}`
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.data.data || [];
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        },

        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/api/v1/notifications/${notificationId}/mark-as-read`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]')?.content || ''}`
                    }
                });

                if (response.ok) {
                    await this.loadAllNotifications();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        get filteredNotifications() {
            if (this.filter === 'unread') {
                return this.notifications.filter(n => !n.read_at);
            }
            return this.notifications;
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'Hace unos segundos';
            if (diffMins < 60) return `Hace ${diffMins} min`;
            if (diffHours < 24) return `Hace ${diffHours}h`;
            if (diffDays < 7) return `Hace ${diffDays}d`;
            
            return date.toLocaleDateString('es-ES');
        },

        formatDateFull(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', { 
                weekday: 'short', 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
    };
}
</script>
@endsection

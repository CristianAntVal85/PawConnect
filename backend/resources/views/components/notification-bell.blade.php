@props(['class' => ''])

<div x-data="notificationBell()" class="{{ $class }}">
    <!-- Campana de notificaciones -->
    <div class="relative inline-block">
        <button 
            @click="open = !open"
            @click.outside="open = false"
            class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
            title="Notificaciones"
        >
            <!-- Icono de campana -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>

            <!-- Badge con contador -->
            <template x-if="unreadCount > 0">
                <span 
                    class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1 -translate-y-1 bg-red-600 rounded-full"
                    x-text="unreadCount"
                >
                </span>
            </template>
        </button>

        <!-- Dropdown de notificaciones -->
        <div 
            x-show="open"
            x-transition
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50"
        >
            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
                    <template x-if="unreadCount > 0">
                        <span class="text-xs text-gray-500">
                            <span x-text="unreadCount"></span> sin leer
                        </span>
                    </template>
                </div>
            </div>

            <!-- Lista de notificaciones -->
            <div class="max-h-96 overflow-y-auto">
                <template x-if="notifications.length === 0">
                    <div class="px-4 py-8 text-center text-gray-500 text-sm">
                        No tienes notificaciones
                    </div>
                </template>

                <template x-if="notifications.length > 0">
                    <div class="divide-y divide-gray-200">
                        <template x-for="notification in notifications" :key="notification.id">
                            <div 
                                :class="notification.read_at ? 'bg-white' : 'bg-blue-50'"
                                class="px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
                                @click="markAsRead(notification.id)"
                            >
                                <!-- Contenido según el tipo -->
                                <template x-if="notification.data.type === 'App\\\\Notifications\\\\HealthReminderNotification'">
                                    <div class="flex gap-2">
                                        <div class="flex-shrink-0">
                                            <span class="text-lg">🏥</span>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                Recordatorio de salud
                                            </p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                <span x-text="notification.data.message"></span>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <span x-text="formatDate(notification.created_at)"></span>
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <!-- Notificación genérica -->
                                <template x-if="notification.data.type !== 'App\\\\Notifications\\\\HealthReminderNotification'">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="notification.type"></p>
                                        <p class="text-sm text-gray-600 mt-1">Nueva notificación</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <span x-text="formatDate(notification.created_at)"></span>
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="px-4 py-2 border-t border-gray-200">
                <a href="{{ route('web.notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Ver todas las notificaciones →
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open: false,
        notifications: [],
        unreadCount: 0,
        
        init() {
            this.loadNotifications();
            // Recargar notificaciones cada 30 segundos
            setInterval(() => this.loadNotifications(), 30000);
        },

        async loadNotifications() {
            try {
                const response = await fetch('/api/v1/notifications/unread-count', {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]')?.content || ''}`
                    }
                });
                
                if (!response.ok) return;
                
                const data = await response.json();
                this.unreadCount = data.unread_count;

                // Si hay notificaciones no leídas, cargar la lista
                if (this.unreadCount > 0) {
                    const notifResponse = await fetch('/api/v1/notifications', {
                        headers: {
                            'Accept': 'application/json',
                            'Authorization': `Bearer ${document.querySelector('meta[name="api-token"]')?.content || ''}`
                        }
                    });

                    if (notifResponse.ok) {
                        const notifData = await notifResponse.json();
                        this.notifications = notifData.data.data || [];
                    }
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
                    // Recargar notificaciones
                    this.loadNotifications();
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
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
        }
    };
}
</script>

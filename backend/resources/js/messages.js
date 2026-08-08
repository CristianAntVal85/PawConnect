/**
 * Message Management
 * Vista de chat con tiempo real, auto-scroll e indicador de escritura.
 */

import { apiGet, apiPost, getErrorMessage } from './api.js';

let activeMatchId = null;
let activeChannelName = null;
let typingCooldown = false;

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

export async function fetchMessages(matchId) {
    try {
        const response = await apiGet(`/matches/${matchId}/messages`);
        return { success: true, data: response };
    } catch (error) {
        return { success: false, message: getErrorMessage(error) };
    }
}

function scrollToBottom(container) {
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatTime(timestamp) {
    if (!timestamp) {
        return '';
    }

    const date = new Date(timestamp);
    return date.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
    });
}

function renderMessageBubble(container, message, currentUserId) {
    const isOwn = Number(message.sender_id) === Number(currentUserId);
    const wrapper = document.createElement('div');
    wrapper.className = `flex ${isOwn ? 'justify-end' : 'justify-start'}`;

    wrapper.innerHTML = `
        <div class="max-w-[80%] rounded-2xl px-4 py-2 shadow-sm ${isOwn ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-800'}">
            <p class="text-sm leading-relaxed">${escapeHtml(message.body)}</p>
            <div class="mt-1 text-right text-[11px] ${isOwn ? 'text-emerald-100' : 'text-slate-500'}">${formatTime(message.created_at)}</div>
        </div>
    `;

    container.appendChild(wrapper);
}

function renderMessages(container, messages, currentUserId) {
    container.innerHTML = '';

    messages.forEach((message) => {
        renderMessageBubble(container, message, currentUserId);
    });

    scrollToBottom(container);
}

function setTypingVisible(typingIndicator, visible, senderName = '') {
    if (!typingIndicator) {
        return;
    }

    const text = typingIndicator.querySelector('[data-typing-text]');
    if (text) {
        text.textContent = senderName ? `${senderName} esta escribiendo...` : 'Escribiendo...';
    }

    typingIndicator.classList.toggle('hidden', !visible);
}

function subscribeToConversation(matchId, currentUserId, messagesContainer, typingIndicator, onRemoteMessage) {
    if (!window.Echo) {
        return;
    }

    if (activeChannelName) {
        window.Echo.leave(activeChannelName);
    }

    activeChannelName = `private-match.${matchId}`;

    window.Echo.private(`match.${matchId}`)
        .listen('.MessageSent', (payload) => {
            if (Number(payload.sender_id) === Number(currentUserId)) {
                return;
            }

            renderMessageBubble(messagesContainer, payload, currentUserId);
            scrollToBottom(messagesContainer);
            setTypingVisible(typingIndicator, false);

            if (typeof onRemoteMessage === 'function') {
                onRemoteMessage(matchId, payload);
            }
        })
        .listenForWhisper('typing', (payload) => {
            if (!payload || Number(payload.user_id) === Number(currentUserId)) {
                return;
            }

            setTypingVisible(typingIndicator, true, payload.user_name ?? 'Alguien');

            window.clearTimeout(typingIndicator._typingTimeout);
            typingIndicator._typingTimeout = window.setTimeout(() => {
                setTypingVisible(typingIndicator, false);
            }, 1400);
        });
}

function whisperTyping(matchId, userId, userName) {
    if (!window.Echo || typingCooldown) {
        return;
    }

    typingCooldown = true;

    window.Echo.private(`match.${matchId}`).whisper('typing', {
        user_id: Number(userId),
        user_name: userName,
    });

    window.setTimeout(() => {
        typingCooldown = false;
    }, 900);
}

function markConversationAsActive(listItems, matchId) {
    listItems.forEach((item) => {
        const isActive = Number(item.dataset.conversationId) === Number(matchId);

        item.classList.toggle('border-emerald-500', isActive);
        item.classList.toggle('bg-emerald-50', isActive);
        item.classList.toggle('border-transparent', !isActive);
        item.classList.toggle('bg-white', !isActive);

        const badge = item.querySelector('[data-unread-badge]');
        if (isActive && badge) {
            badge.textContent = '0';
            badge.classList.add('hidden');
        }
    });
}

function updateHeader(headerName, headerMeta, conversationItem) {
    if (!conversationItem) {
        return;
    }

    headerName.textContent = conversationItem.dataset.conversationTitle || 'Conversacion';
    headerMeta.textContent = conversationItem.dataset.conversationSubtitle || '';
}

async function openConversation(matchId, context) {
    const {
        currentUserId,
        listItems,
        messagesContainer,
        typingIndicator,
        headerName,
        headerMeta,
    } = context;

    const conversationItem = listItems.find(
        (item) => Number(item.dataset.conversationId) === Number(matchId)
    );

    updateHeader(headerName, headerMeta, conversationItem);
    markConversationAsActive(listItems, matchId);
    setTypingVisible(typingIndicator, false);

    const result = await fetchMessages(matchId);
    if (!result.success) {
        messagesContainer.innerHTML = `<p class="text-sm text-red-600">${escapeHtml(result.message)}</p>`;
        return;
    }

    const messages = result.data.data ?? [];
    renderMessages(messagesContainer, messages, currentUserId);

    subscribeToConversation(
        matchId,
        currentUserId,
        messagesContainer,
        typingIndicator,
        () => {
            markConversationAsActive(listItems, matchId);
        }
    );

    activeMatchId = Number(matchId);
}

export function initChatPage() {
    const root = document.querySelector('[data-chat-root]');
    if (!root) {
        return;
    }

    const currentUserId = Number(root.dataset.userId);
    const currentUserName = root.dataset.userName || 'Usuario';
    const initialMatchId = Number(root.dataset.activeMatchId || 0) || null;

    const listItems = Array.from(document.querySelectorAll('[data-conversation-id]'));
    const messagesContainer = document.querySelector('[data-chat-messages]');
    const typingIndicator = document.querySelector('[data-typing-indicator]');
    const form = document.querySelector('[data-chat-form]');
    const textarea = form?.querySelector('textarea[name="body"]');
    const submitBtn = form?.querySelector('button[type="submit"]');
    const headerName = document.querySelector('[data-chat-header-name]');
    const headerMeta = document.querySelector('[data-chat-header-meta]');

    if (!messagesContainer || !form || !textarea || !submitBtn || !headerName || !headerMeta) {
        return;
    }

    const context = {
        currentUserId,
        listItems,
        messagesContainer,
        typingIndicator,
        headerName,
        headerMeta,
    };

    listItems.forEach((item) => {
        item.addEventListener('click', async () => {
            const matchId = Number(item.dataset.conversationId);
            await openConversation(matchId, context);
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!activeMatchId || !textarea.value.trim()) {
            return;
        }

        submitBtn.disabled = true;

        const result = await sendMessage(activeMatchId, textarea.value);

        submitBtn.disabled = false;

        if (!result.success) {
            alert(result.message);
            return;
        }

        textarea.value = '';
        textarea.focus();

        renderMessageBubble(messagesContainer, result.data.data, currentUserId);
        scrollToBottom(messagesContainer);
        setTypingVisible(typingIndicator, false);
    });

    textarea.addEventListener('input', () => {
        if (!activeMatchId || !textarea.value.trim()) {
            return;
        }

        // Echo whisper('typing') emite internamente el evento client-typing.
        whisperTyping(activeMatchId, currentUserId, currentUserName);
    });

    if (initialMatchId) {
        openConversation(initialMatchId, context);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initChatPage();
});

export default {
    sendMessage,
    fetchMessages,
    initChatPage,
};

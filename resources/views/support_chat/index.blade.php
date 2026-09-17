@extends('layouts.app')

@section('content')
<div class="page-wrapper" style="padding: 6px 10px 6px 10px;">
    <!-- Compact Top Bar Header -->
    <div class="row page-titles mb-2 py-1 align-items-center">
        <div class="col-md-5 align-self-center">
            <h4 class="text-themecolor mb-0 font-weight-bold" style="font-size: 16px;">
                <i class="mdi mdi-forum text-primary mr-1"></i> Support Live Chat
            </h4>
        </div>
        <div class="col-md-7 align-self-center text-right d-flex align-items-center justify-content-end flex-wrap" style="gap: 6px;">
            <!-- Sound Alert Toggle -->
            <button id="btnToggleAudioAlert" type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm py-1" onclick="toggleAudioAlert()" style="font-size: 11.5px;">
                <i class="mdi mdi-volume-high mr-1" id="audioAlertIcon"></i> Alert Sound: <span id="audioAlertStatus" class="font-weight-bold">ON</span>
            </button>
            <!-- Test Sound Button -->
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 py-1 shadow-sm text-muted" onclick="testIncomingAlertSound()" style="font-size: 11.5px;" title="Test Incoming Message Chime">
                <i class="mdi mdi-bell-ring-outline text-warning mr-1"></i> Test Sound
            </button>
            <!-- Quick Questions -->
            <a href="{{ route('support.questions.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm py-1" style="font-size: 11.5px;">
                <i class="mdi mdi-help-circle-outline mr-1"></i> Manage Quick Questions
            </a>
        </div>
    </div>

    <!-- Floating Incoming Alert Toast Banner Container -->
    <div id="incomingAlertBannerContainer" style="position: fixed; top: 75px; right: 20px; z-index: 9999; max-width: 380px; width: 100%; pointer-events: none;"></div>

    <div class="container-fluid px-0">
        <!-- Main Chat Card: Maximized Height & Width -->
        <div class="card shadow-sm border-0 mb-0" style="border-radius: 12px; overflow: hidden; height: calc(100vh - 120px); min-height: 580px;">
            <div class="card-body p-0 d-flex flex-column h-100">
                <div class="row no-gutters flex-grow-1 h-100">
                    
                    <!-- Left Sidebar: Conversations List (Compact 25-30% Width) -->
                    <div class="col-xl-3 col-lg-4 col-md-4 border-right d-flex flex-column h-100 bg-white" style="border-color: #e2e8f0 !important; min-width: 280px;">
                        
                        <!-- Tabs Header (Compact) -->
                        <div class="p-2 px-3 border-bottom bg-light">
                            <ul class="nav nav-pills nav-fill" id="chatTabs" role="tablist" style="gap: 4px;">
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold rounded-pill py-1 px-2 {{ $tab === 'customer' ? 'active' : '' }}" id="tab-customer" href="javascript:void(0)" onclick="switchTab('customer')" style="font-size: 12px;">
                                        <i class="mdi mdi-account-circle mr-1"></i> Customers
                                        <span class="badge badge-pill badge-danger ml-1" id="badge-customer-unread" style="display: {{ $customerUnread > 0 ? 'inline-block' : 'none' }}; font-size: 10px;">{{ $customerUnread }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold rounded-pill py-1 px-2 {{ $tab === 'business' ? 'active' : '' }}" id="tab-business" href="javascript:void(0)" onclick="switchTab('business')" style="font-size: 12px;">
                                        <i class="mdi mdi-car mr-1"></i> Drivers / Partners
                                        <span class="badge badge-pill badge-danger ml-1" id="badge-business-unread" style="display: {{ $businessUnread > 0 ? 'inline-block' : 'none' }}; font-size: 10px;">{{ $businessUnread }}</span>
                                    </a>
                                </li>
                            </ul>

                            <!-- Search & Filter Controls (Compact) -->
                            <div class="mt-2">
                                <div class="input-group input-group-sm mb-1">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0 py-0" style="height: 30px;"><i class="mdi mdi-magnify text-muted" style="font-size: 14px;"></i></span>
                                    </div>
                                    <input type="text" id="chatSearchInput" class="form-control border-left-0" placeholder="Search name, phone, ticket..." oninput="handleSearch(this.value)" style="height: 30px; font-size: 12px;">
                                </div>
                                <div class="btn-group btn-group-toggle btn-group-sm w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-secondary active btn-sm py-0" style="font-size: 11px; height: 26px; line-height: 24px;" onclick="filterStatus('all')">
                                        <input type="radio" name="statusFilter" checked> All
                                    </label>
                                    <label class="btn btn-outline-secondary btn-sm py-0" style="font-size: 11px; height: 26px; line-height: 24px;" onclick="filterStatus('active')">
                                        <input type="radio" name="statusFilter"> Active
                                    </label>
                                    <label class="btn btn-outline-secondary btn-sm py-0" style="font-size: 11px; height: 26px; line-height: 24px;" onclick="filterStatus('resolved')">
                                        <input type="radio" name="statusFilter"> Resolved
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket List Container -->
                        <div class="flex-grow-1 overflow-auto" id="ticketListContainer" style="overflow-y: auto;">
                            <div class="text-center py-4 text-muted" id="ticketListLoading">
                                <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div> Loading conversations...
                            </div>
                            <div id="ticketListItems"></div>
                        </div>
                    </div>

                    <!-- Right Pane: Active Chat Conversation (Expanded 70-75% Width) -->
                    <div class="col-xl-9 col-lg-8 col-md-8 d-flex flex-column h-100 bg-light">
                        
                        <!-- Chat Header (Visible when ticket is selected - Sleek) -->
                        <div id="chatHeader" class="px-4 py-2 bg-white border-bottom d-flex align-items-center justify-content-between shadow-sm" style="display: none !important; min-height: 54px;">
                            <div class="d-flex align-items-center">
                                <div class="mr-3">
                                    <div class="avatar-circle shadow-sm bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%; font-size: 16px; font-weight: 600;" id="chatHeaderAvatarCircle">
                                        <span id="chatHeaderInitials">U</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0 font-weight-bold text-dark mr-2" id="chatHeaderName" style="font-size: 15px;">User Name</h5>
                                        <span class="badge badge-info mr-2 px-2 py-0" id="chatHeaderTypeBadge" style="font-size: 10px;">Customer</span>
                                        <span class="badge badge-success px-2 py-0" id="chatHeaderStatusBadge" style="font-size: 10px;">Active</span>
                                    </div>
                                    <div class="small text-muted mt-1 d-flex align-items-center flex-wrap" style="font-size: 11.5px; gap: 14px;">
                                        <span><i class="mdi mdi-ticket-confirmation text-primary mr-1"></i> <span id="chatHeaderTicketNum" class="font-weight-medium">TIC-001</span></span>
                                        <span><i class="mdi mdi-phone text-success mr-1"></i> <a href="#" id="chatHeaderPhone" class="text-muted font-weight-medium"></a></span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button id="btnToggleStatus" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm py-1" style="font-size: 12px;" onclick="toggleActiveTicketStatus()">
                                    <i class="mdi mdi-check-circle mr-1"></i> Mark as Resolved
                                </button>
                            </div>
                        </div>

                        <!-- Empty Placeholder (When no ticket is selected) -->
                        <div id="chatPlaceholder" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="bg-white rounded-circle p-4 shadow-sm mb-3" style="width: 86px; height: 86px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-chat-processing-outline text-primary" style="font-size: 44px;"></i>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-1" style="font-size: 18px;">Select a conversation to start chatting</h4>
                            <p class="text-muted small mb-0" style="max-width: 400px; font-size: 12.5px;">Choose a customer or driver partner from the list on the left to view messages and reply in real time.</p>
                        </div>

                        <!-- Messages Thread Scroll Area (Spacious & Clean) -->
                        <div id="chatMessagesArea" class="flex-grow-1 p-3 p-md-4 overflow-auto" style="display: none; overflow-y: auto; background-color: #f8fafc;">
                            <div id="chatMessagesList" class="d-flex flex-column" style="min-height: 100%;"></div>
                        </div>

                        <!-- Chat Input Footer -->
                        <div id="chatInputFooter" class="px-3 px-md-4 py-2 bg-white border-top shadow-sm" style="display: none;">
                            
                            <!-- Quick Canned Response Pills -->
                            <div class="mb-2 d-flex flex-wrap align-items-center" style="gap: 5px;">
                                <span class="text-muted mr-1 font-weight-bold" style="font-size: 11px;"><i class="mdi mdi-lightning-bolt text-warning"></i> Quick:</span>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 11px;" onclick="insertCanned('Hello! How can I assist you today?')">👋 Greeting</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 11px;" onclick="insertCanned('We are reviewing your request and will resolve it shortly.')">⏳ Checking</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 11px;" onclick="insertCanned('Your refund/payout has been processed successfully.')">💳 Refund/Payout</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 11px;" onclick="insertCanned('Please provide your booking ID and registered phone number.')">📋 Ask Info</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 11px;" onclick="insertCanned('Thank you for contacting Fiinway Support! Have a great day.')">✅ Thank You</button>
                            </div>

                            <!-- Input Box (Spacious) -->
                            <form id="chatReplyForm" onsubmit="event.preventDefault(); sendAdminReply();" class="d-flex align-items-center">
                                <input type="text" id="chatMessageInput" class="form-control rounded-pill px-3 py-2 border mr-2" placeholder="Type your reply here... (Press Enter to send)" autocomplete="off" style="height: 42px; font-size: 13.5px;">
                                <button type="submit" id="btnSendMessage" class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; flex-shrink: 0;" title="Send Reply">
                                    <i class="mdi mdi-send text-white" style="font-size: 18px;"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ticket-item {
    cursor: pointer;
    transition: background-color 0.15s ease-in-out;
    border-bottom: 1px solid #f1f5f9;
    padding: 8px 12px;
}
.ticket-item:hover {
    background-color: #f8fafc;
}
.ticket-item.active {
    background-color: #eff6ff !important;
    border-left: 3px solid #4f46e5 !important;
}
.ticket-item.pulse-unread {
    animation: pulseBorder 1.5s infinite;
}
@keyframes pulseBorder {
    0% { background-color: #eff6ff; }
    50% { background-color: #fef2f2; }
    100% { background-color: #eff6ff; }
}
.ticket-user-name {
    font-size: 13px;
    line-height: 1.2;
}
.unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ef4444;
    display: inline-block;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
}
.msg-bubble-user {
    background-color: #ffffff;
    color: #1e293b;
    border-radius: 14px 14px 14px 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    max-width: 82%;
    border: 1px solid #e2e8f0;
}
.msg-bubble-admin {
    background: linear-gradient(135deg, #4f46e5, #4338ca);
    color: #ffffff;
    border-radius: 14px 14px 4px 14px;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    max-width: 82%;
}
.btn-xs {
    padding: 2px 9px;
    font-size: 11px;
}
.incoming-toast {
    pointer-events: auto;
    background: #ffffff;
    border-left: 4px solid #4f46e5;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    padding: 12px 14px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    animation: slideInRight 0.3s ease-out;
}
.incoming-toast:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.2);
}
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<script>
let currentTab = '{{ $tab }}';
let currentStatus = 'all';
let currentSearch = '';
let activeTicketId = null;
let activeTicketData = null;
let lastMessageId = 0;
let ticketPollTimer = null;
let messagePollTimer = null;

// Alert & Notification Tracking State
let audioAlertEnabled = (localStorage.getItem('fiinway_support_audio_alert') !== 'false');
let lastSeenTotalUnread = null;
let originalPageTitle = document.title;
let titleBlinkInterval = null;
let audioContextInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    updateAudioAlertButtonUI();
    loadTickets();
    startPolling();
    requestNotificationPermission();

    // Reset title flash when window gains focus
    window.addEventListener('focus', function() {
        stopTitleBlink();
    });
    document.addEventListener('click', function() {
        stopTitleBlink();
        // Resume Web Audio Context if suspended
        if (audioContextInstance && audioContextInstance.state === 'suspended') {
            audioContextInstance.resume();
        }
    });
});

/* ── Web Audio API Incoming Message Chime Synthesizer ── */
function playIncomingAlertSound() {
    if (!audioAlertEnabled) return;

    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;

        if (!audioContextInstance || audioContextInstance.state === 'closed') {
            audioContextInstance = new AudioCtx();
        }

        if (audioContextInstance.state === 'suspended') {
            audioContextInstance.resume();
        }

        const ctx = audioContextInstance;
        const now = ctx.currentTime;

        // Tone 1: High crisp bell note (E5 = 659.25Hz)
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(659.25, now);
        gain1.gain.setValueAtTime(0.001, now);
        gain1.gain.linearRampToValueAtTime(0.32, now + 0.02);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.35);

        // Tone 2: Harmonious resolve bell note (A5 = 880.00Hz)
        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(880.00, now + 0.12);
        gain2.gain.setValueAtTime(0.001, now + 0.12);
        gain2.gain.linearRampToValueAtTime(0.38, now + 0.14);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.58);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.12);
        osc2.stop(now + 0.58);

    } catch (e) {
        console.warn('Web Audio Playback notice:', e);
    }
}

function testIncomingAlertSound() {
    playIncomingAlertSound();
    showIncomingToast({
        user_name: 'Test Customer',
        user_type: 'customer',
        last_message: 'Hi support team! This is a test sound alert.'
    }, true);
}

function toggleAudioAlert() {
    audioAlertEnabled = !audioAlertEnabled;
    localStorage.setItem('fiinway_support_audio_alert', audioAlertEnabled ? 'true' : 'false');
    updateAudioAlertButtonUI();
    if (audioAlertEnabled) {
        playIncomingAlertSound();
    }
}

function updateAudioAlertButtonUI() {
    const btn = document.getElementById('btnToggleAudioAlert');
    const status = document.getElementById('audioAlertStatus');
    const icon = document.getElementById('audioAlertIcon');
    if (!btn || !status || !icon) return;

    if (audioAlertEnabled) {
        btn.className = 'btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm py-1';
        icon.className = 'mdi mdi-volume-high mr-1';
        status.textContent = 'ON';
    } else {
        btn.className = 'btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-sm py-1';
        icon.className = 'mdi mdi-volume-off mr-1';
        status.textContent = 'MUTED';
    }
}

/* ── Tab Title Flash Notification ── */
function triggerTitleBlink(senderName) {
    stopTitleBlink();
    let isOriginal = false;
    titleBlinkInterval = setInterval(() => {
        document.title = isOriginal ? originalPageTitle : `🔔 New Message from ${senderName || 'User'}!`;
        isOriginal = !isOriginal;
    }, 1000);
}

function stopTitleBlink() {
    if (titleBlinkInterval) {
        clearInterval(titleBlinkInterval);
        titleBlinkInterval = null;
    }
    document.title = originalPageTitle;
}

/* ── Floating Alert Toast Banner ── */
function showIncomingToast(item, isTest = false) {
    const container = document.getElementById('incomingAlertBannerContainer');
    if (!container) return;

    const senderRole = item.user_type === 'business' ? 'Driver Partner' : 'Customer';
    const toast = document.createElement('div');
    toast.className = 'incoming-toast';

    toast.innerHTML = `
        <div class="d-flex align-items-start justify-content-between">
            <div class="d-flex align-items-center" style="gap: 8px;">
                <span class="badge ${item.user_type === 'business' ? 'badge-primary' : 'badge-info'} px-2 py-0" style="font-size: 10px;">${senderRole}</span>
                <strong class="text-dark" style="font-size: 13px;">${escapeHtml(item.user_name || 'User')}</strong>
            </div>
            <button type="button" class="close text-muted" style="font-size: 16px; outline: none;" onclick="this.closest('.incoming-toast').remove(); event.stopPropagation();">&times;</button>
        </div>
        <div class="text-secondary mt-1 text-truncate" style="font-size: 12px; max-width: 320px;">
            ${escapeHtml(item.last_message || 'Sent a new message')}
        </div>
        <div class="mt-2 text-right">
            <span class="btn btn-xs btn-primary rounded-pill px-2 py-0" style="font-size: 11px;">
                ${isTest ? 'Close Test' : 'Open Chat &rarr;'}
            </span>
        </div>
    `;

    toast.onclick = function() {
        if (!isTest && item.id) {
            if (item.user_type && item.user_type !== currentTab) {
                switchTab(item.user_type);
            }
            selectTicket(item.id);
        }
        toast.remove();
    };

    container.appendChild(toast);
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.transition = 'opacity 0.4s, transform 0.4s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 400);
        }
    }, 8000);
}

/* ── Desktop Notification API ── */
function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

function showDesktopNotification(title, body) {
    if ('Notification' in window && Notification.permission === 'granted' && document.hidden) {
        try {
            new Notification(title, {
                body: body,
                icon: '/assets/images/logo-small.png'
            });
        } catch (e) {}
    }
}

/* ── Chat Switching & Filtering ── */
function switchTab(tab) {
    if (currentTab === tab) return;
    currentTab = tab;
    
    document.getElementById('tab-customer').classList.toggle('active', tab === 'customer');
    document.getElementById('tab-business').classList.toggle('active', tab === 'business');
    
    activeTicketId = null;
    activeTicketData = null;
    hideChatArea();
    loadTickets();
}

function filterStatus(status) {
    currentStatus = status;
    loadTickets();
}

function handleSearch(val) {
    currentSearch = val;
    loadTickets();
}

function startPolling() {
    ticketPollTimer = setInterval(() => {
        loadTickets(true);
    }, 4000);

    messagePollTimer = setInterval(() => {
        if (activeTicketId) {
            fetchNewMessages();
        }
    }, 2500);
}

function loadTickets(silent = false) {
    if (!silent) {
        document.getElementById('ticketListLoading').style.display = 'block';
    }

    const url = `/support-chats/tickets?user_type=${currentTab}&status=${currentStatus}&search=${encodeURIComponent(currentSearch)}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            document.getElementById('ticketListLoading').style.display = 'none';
            if (data.success) {
                renderTicketList(data.tickets);
                updateBadges(data.counts);

                // Alert Detection on incoming messages
                handleIncomingAlertCheck(data);
            }
        })
        .catch(err => {
            console.error('Error fetching tickets:', err);
            document.getElementById('ticketListLoading').style.display = 'none';
        });
}

function handleIncomingAlertCheck(data) {
    if (!data.counts) return;

    const currentTotalUnread = (data.counts.customer_unread || 0) + (data.counts.business_unread || 0);

    if (lastSeenTotalUnread !== null && currentTotalUnread > lastSeenTotalUnread) {
        // A new unread message arrived!
        playIncomingAlertSound();

        if (data.latest_incoming) {
            const senderName = data.latest_incoming.user_name || 'User';
            const role = data.latest_incoming.user_type === 'business' ? 'Driver Partner' : 'Customer';
            showIncomingToast(data.latest_incoming);
            triggerTitleBlink(senderName);
            showDesktopNotification(`New message from ${role} ${senderName}`, data.latest_incoming.last_message || '');
        } else {
            playIncomingAlertSound();
            triggerTitleBlink('User');
        }
    }

    lastSeenTotalUnread = currentTotalUnread;
}

function updateBadges(counts) {
    if (!counts) return;
    const cBadge = document.getElementById('badge-customer-unread');
    const bBadge = document.getElementById('badge-business-unread');
    
    cBadge.textContent = counts.customer_unread || 0;
    cBadge.style.display = (counts.customer_unread > 0) ? 'inline-block' : 'none';

    bBadge.textContent = counts.business_unread || 0;
    bBadge.style.display = (counts.business_unread > 0) ? 'inline-block' : 'none';
}

function renderTicketList(tickets) {
    const container = document.getElementById('ticketListItems');
    if (!tickets || tickets.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="mdi mdi-inbox-outline" style="font-size: 28px;"></i>
                <p class="mt-2 mb-0 small">No support tickets found</p>
            </div>`;
        return;
    }

    let html = '';
    tickets.forEach(t => {
        const isActive = activeTicketId === t.id;
        const unreadCount = t.unread_admin_count || 0;
        const statusBadge = t.status === 'resolved' 
            ? '<span class="badge badge-light text-muted border px-1 py-0" style="font-size: 9.5px; font-weight: 600;">Resolved</span>' 
            : '<span class="badge badge-success px-1 py-0" style="font-size: 9.5px; font-weight: 600;">Active</span>';
        
        const timeAgo = formatTime(t.updated_at || t.created_at);

        html += `
            <div class="ticket-item ${isActive ? 'active' : ''} ${unreadCount > 0 ? 'pulse-unread' : ''}" onclick="selectTicket(${t.id})">
                <div class="d-flex align-items-center justify-content-between mb-1" style="gap: 8px;">
                    <div class="d-flex align-items-center text-truncate" style="min-width: 0;">
                        ${unreadCount > 0 ? '<span class="unread-dot mr-1 flex-shrink-0"></span>' : ''}
                        <span class="ticket-user-name font-weight-bold text-truncate ${unreadCount > 0 ? 'text-primary' : 'text-dark'}">${escapeHtml(t.user_name || 'User')}</span>
                        ${unreadCount > 0 ? `<span class="badge badge-danger ml-1 px-1 py-0 flex-shrink-0" style="font-size: 9px; border-radius: 6px;">${unreadCount}</span>` : ''}
                    </div>
                    <small class="text-muted flex-shrink-0" style="font-size: 10.5px;">${timeAgo}</small>
                </div>
                <div class="d-flex align-items-center justify-content-between" style="gap: 8px;">
                    <div class="text-muted text-truncate" style="font-size: 11.5px; max-width: calc(100% - 55px); line-height: 1.2;">
                        ${t.last_sender === 'admin' ? '<strong class="text-primary">You: </strong>' : ''}${escapeHtml(t.last_message || 'Started a conversation')}
                    </div>
                    <div class="flex-shrink-0">${statusBadge}</div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function selectTicket(ticketId) {
    activeTicketId = ticketId;
    lastMessageId = 0;
    
    // Highlight item in sidebar
    document.querySelectorAll('.ticket-item').forEach(el => el.classList.remove('active'));
    
    // Show chat area
    document.getElementById('chatPlaceholder').style.setProperty('display', 'none', 'important');
    document.getElementById('chatHeader').style.setProperty('display', 'flex', 'important');
    document.getElementById('chatMessagesArea').style.display = 'block';
    document.getElementById('chatInputFooter').style.display = 'block';
    document.getElementById('chatMessagesList').innerHTML = '<div class="text-center py-5"><div class="spinner-border spinner-border-sm text-primary"></div><p class="mt-2 text-muted small">Loading messages...</p></div>';

    fetch(`/support-chats/messages/${ticketId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                activeTicketData = data.ticket;
                renderHeader(data.ticket);
                renderAllMessages(data.messages);
                loadTickets(true); // update badge counts
                document.getElementById('chatMessageInput').focus();
            }
        });
}

function hideChatArea() {
    document.getElementById('chatPlaceholder').style.setProperty('display', 'flex', 'important');
    document.getElementById('chatHeader').style.setProperty('display', 'none', 'important');
    document.getElementById('chatMessagesArea').style.display = 'none';
    document.getElementById('chatInputFooter').style.display = 'none';
}

function renderHeader(ticket) {
    document.getElementById('chatHeaderName').textContent = ticket.user_name || 'User';
    document.getElementById('chatHeaderTicketNum').textContent = ticket.ticket_number || '';
    
    const initials = (ticket.user_name || 'U').trim().charAt(0).toUpperCase();
    document.getElementById('chatHeaderInitials').textContent = initials;

    const phoneEl = document.getElementById('chatHeaderPhone');
    phoneEl.textContent = ticket.user_phone || 'No phone provided';
    phoneEl.href = ticket.user_phone ? `tel:${ticket.user_phone}` : '#';

    const typeBadge = document.getElementById('chatHeaderTypeBadge');
    typeBadge.textContent = ticket.user_type === 'business' ? 'Driver Partner' : 'Customer';
    typeBadge.className = ticket.user_type === 'business' ? 'badge badge-primary mr-2' : 'badge badge-info mr-2';

    const statusBadge = document.getElementById('chatHeaderStatusBadge');
    statusBadge.textContent = ticket.status === 'resolved' ? 'Resolved' : 'Active';
    statusBadge.className = ticket.status === 'resolved' ? 'badge badge-secondary' : 'badge badge-success';

    const btnStatus = document.getElementById('btnToggleStatus');
    if (ticket.status === 'resolved') {
        btnStatus.innerHTML = '<i class="mdi mdi-refresh mr-1"></i> Reopen Ticket';
        btnStatus.className = 'btn btn-sm btn-outline-warning rounded-pill px-3 shadow-sm py-1';
    } else {
        btnStatus.innerHTML = '<i class="mdi mdi-check-circle mr-1"></i> Mark as Resolved';
        btnStatus.className = 'btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm py-1';
    }
}

function renderAllMessages(messages) {
    const list = document.getElementById('chatMessagesList');
    list.innerHTML = '';

    if (!messages || messages.length === 0) {
        list.innerHTML = '<div class="text-center py-5 text-muted my-auto"><i class="mdi mdi-message-outline" style="font-size: 32px;"></i><p class="mt-2 small">No messages yet. Send a reply below.</p></div>';
        return;
    }

    messages.forEach(m => {
        appendMessageBubble(m);
        if (m.id > lastMessageId) lastMessageId = m.id;
    });

    scrollToBottom();
}

function fetchNewMessages() {
    if (!activeTicketId) return;

    fetch(`/support-chats/messages/${activeTicketId}?after_id=${lastMessageId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                let hasUserMsg = false;
                data.messages.forEach(m => {
                    appendMessageBubble(m);
                    if (m.id > lastMessageId) lastMessageId = m.id;
                    if (m.sender_type !== 'admin') {
                        hasUserMsg = true;
                    }
                });

                scrollToBottom();

                if (hasUserMsg) {
                    playIncomingAlertSound();
                    triggerTitleBlink(activeTicketData?.user_name || 'User');
                }
            }
        });
}

function appendMessageBubble(m) {
    const list = document.getElementById('chatMessagesList');
    const isAdmin = m.sender_type === 'admin';
    const timeStr = formatTime(m.created_at);

    const div = document.createElement('div');
    div.className = `d-flex mb-3 ${isAdmin ? 'justify-content-end' : 'justify-content-start'}`;

    div.innerHTML = `
        <div class="${isAdmin ? 'msg-bubble-admin' : 'msg-bubble-user'} p-3">
            <div class="d-flex align-items-center justify-content-between mb-1" style="gap: 16px;">
                <strong style="font-size: 12px; opacity: ${isAdmin ? '0.95' : '0.8'};">${escapeHtml(m.sender_name || (isAdmin ? 'Support Team' : 'User'))}</strong>
                <small style="font-size: 10.5px; opacity: ${isAdmin ? '0.85' : '0.6'};">${timeStr}</small>
            </div>
            <div style="font-size: 14px; line-height: 1.55; white-space: pre-wrap; word-break: break-word;">${escapeHtml(m.message)}</div>
        </div>
    `;

    list.appendChild(div);
}

function sendAdminReply() {
    const input = document.getElementById('chatMessageInput');
    const msg = input.value.trim();
    if (!msg || !activeTicketId) return;

    const btn = document.getElementById('btnSendMessage');
    btn.disabled = true;

    fetch('/support-chats/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            ticket_id: activeTicketId,
            message: msg
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        if (data.success && data.message) {
            input.value = '';
            appendMessageBubble(data.message);
            if (data.message.id > lastMessageId) lastMessageId = data.message.id;
            scrollToBottom();
            loadTickets(true);
        }
    })
    .catch(err => {
        btn.disabled = false;
        console.error('Error sending message:', err);
    });
}

function toggleActiveTicketStatus() {
    if (!activeTicketId || !activeTicketData) return;
    const newStatus = activeTicketData.status === 'resolved' ? 'active' : 'resolved';

    fetch('/support-chats/toggle-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            ticket_id: activeTicketId,
            status: newStatus
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            activeTicketData.status = data.status;
            renderHeader(activeTicketData);
            loadTickets(true);
        }
    });
}

function insertCanned(text) {
    const input = document.getElementById('chatMessageInput');
    input.value = text;
    input.focus();
}

function scrollToBottom() {
    const area = document.getElementById('chatMessagesArea');
    if (area) {
        area.scrollTop = area.scrollHeight;
    }
}

function formatTime(dateStr) {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } catch(e) {
        return '';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
</script>
@endsection

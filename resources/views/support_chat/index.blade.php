@extends('layouts.app')

@section('content')
<div class="page-wrapper">
    <div class="row page-titles mb-3">
        <div class="col-md-6 align-self-center">
            <h3 class="text-themecolor mb-0 font-weight-bold"><i class="mdi mdi-forum text-primary mr-2"></i> Support Live Chat</h3>
            <small class="text-muted">Real-time chat with customers and driver partners</small>
        </div>
        <div class="col-md-6 align-self-center text-right">
            <a href="{{ route('support.questions.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                <i class="mdi mdi-help-circle-outline mr-1"></i> Manage Quick Questions
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Main Chat Card -->
        <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden; height: calc(100vh - 190px); min-height: 580px;">
            <div class="card-body p-0 d-flex flex-column h-100">
                <div class="row no-gutters flex-grow-1 h-100">
                    
                    <!-- Left Sidebar: Conversations List -->
                    <div class="col-lg-4 col-md-5 border-right d-flex flex-column h-100 bg-white" style="border-color: #e2e8f0 !important;">
                        
                        <!-- Tabs Header -->
                        <div class="p-3 border-bottom bg-light">
                            <ul class="nav nav-pills nav-fill" id="chatTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'customer' ? 'active' : '' }}" id="tab-customer" href="javascript:void(0)" onclick="switchTab('customer')">
                                        <i class="mdi mdi-account-circle mr-1"></i> Customers
                                        <span class="badge badge-pill badge-danger ml-1" id="badge-customer-unread" style="display: {{ $customerUnread > 0 ? 'inline-block' : 'none' }};">{{ $customerUnread }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link font-weight-bold rounded-pill {{ $tab === 'business' ? 'active' : '' }}" id="tab-business" href="javascript:void(0)" onclick="switchTab('business')">
                                        <i class="mdi mdi-car mr-1"></i> Drivers / Partners
                                        <span class="badge badge-pill badge-danger ml-1" id="badge-business-unread" style="display: {{ $businessUnread > 0 ? 'inline-block' : 'none' }};">{{ $businessUnread }}</span>
                                    </a>
                                </li>
                            </ul>

                            <!-- Search & Filter Controls -->
                            <div class="mt-3">
                                <div class="input-group input-group-sm mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="mdi mdi-magnify text-muted"></i></span>
                                    </div>
                                    <input type="text" id="chatSearchInput" class="form-control border-left-0" placeholder="Search by name, phone, ticket..." oninput="handleSearch(this.value)">
                                </div>
                                <div class="btn-group btn-group-toggle btn-group-sm w-100" data-toggle="buttons">
                                    <label class="btn btn-outline-secondary active btn-sm" onclick="filterStatus('all')">
                                        <input type="radio" name="statusFilter" checked> All
                                    </label>
                                    <label class="btn btn-outline-secondary btn-sm" onclick="filterStatus('active')">
                                        <input type="radio" name="statusFilter"> Active
                                    </label>
                                    <label class="btn btn-outline-secondary btn-sm" onclick="filterStatus('resolved')">
                                        <input type="radio" name="statusFilter"> Resolved
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket List Container -->
                        <div class="flex-grow-1 overflow-auto" id="ticketListContainer" style="overflow-y: auto;">
                            <div class="text-center py-5 text-muted" id="ticketListLoading">
                                <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div> Loading conversations...
                            </div>
                            <div id="ticketListItems"></div>
                        </div>
                    </div>

                    <!-- Right Pane: Active Chat Conversation -->
                    <div class="col-lg-8 col-md-7 d-flex flex-column h-100 bg-light">
                        
                        <!-- Chat Header (Visible when ticket is selected) -->
                        <div id="chatHeader" class="p-3 bg-white border-bottom d-flex align-items-center justify-content-between shadow-sm" style="display: none !important;">
                            <div class="d-flex align-items-center">
                                <div class="position-relative mr-3">
                                    <img id="chatHeaderAvatar" src="/assets/images/users/default-user.png" class="rounded-circle" style="width: 46px; height: 46px; object-fit: cover; border: 2px solid #e2e8f0;">
                                    <span id="chatHeaderStatusDot" class="position-absolute bg-success rounded-circle" style="width: 12px; height: 12px; bottom: 0; right: 0; border: 2px solid white;"></span>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0 font-weight-bold text-dark mr-2" id="chatHeaderName">User Name</h5>
                                        <span class="badge badge-info mr-2" id="chatHeaderTypeBadge">Customer</span>
                                        <span class="badge badge-success" id="chatHeaderStatusBadge">Active</span>
                                    </div>
                                    <div class="small text-muted mt-1 d-flex align-items-center">
                                        <span class="mr-3"><i class="mdi mdi-ticket-confirmation text-primary mr-1"></i> <span id="chatHeaderTicketNum">TIC-001</span></span>
                                        <span class="mr-3"><i class="mdi mdi-phone text-success mr-1"></i> <a href="#" id="chatHeaderPhone" class="text-muted"></a></span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button id="btnToggleStatus" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm" onclick="toggleActiveTicketStatus()">
                                    <i class="mdi mdi-check-circle mr-1"></i> Mark as Resolved
                                </button>
                            </div>
                        </div>

                        <!-- Empty Placeholder (When no ticket is selected) -->
                        <div id="chatPlaceholder" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-5">
                            <div class="bg-white rounded-circle p-4 shadow-sm mb-3" style="width: 90px; height: 90px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="mdi mdi-chat-processing-outline text-primary" style="font-size: 44px;"></i>
                            </div>
                            <h4 class="font-weight-bold text-dark">Select a conversation</h4>
                            <p class="text-muted" style="max-width: 360px;">Choose a customer or driver from the list on the left to start chatting and replying in real time.</p>
                        </div>

                        <!-- Messages Thread Scroll Area -->
                        <div id="chatMessagesArea" class="flex-grow-1 p-3 overflow-auto" style="display: none; overflow-y: auto; background-color: #f8fafc;">
                            <div id="chatMessagesList" class="d-flex flex-column"></div>
                        </div>

                        <!-- Chat Input Footer -->
                        <div id="chatInputFooter" class="p-3 bg-white border-top shadow-sm" style="display: none;">
                            
                            <!-- Quick Canned Response Pills -->
                            <div class="mb-2 d-flex flex-wrap" style="gap: 6px;">
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="insertCanned('Hello! How can I assist you today?')">👋 Greeting</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="insertCanned('We are reviewing your request and will resolve it shortly.')">⏳ Checking</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="insertCanned('Your refund/payout has been processed.')">💳 Refund/Payout</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="insertCanned('Thank you for reaching out to Fiinway Support!')">✅ Thank You</button>
                            </div>

                            <!-- Input Box -->
                            <form id="chatReplyForm" onsubmit="event.preventDefault(); sendAdminReply();" class="d-flex align-items-center">
                                <input type="text" id="chatMessageInput" class="form-control rounded-pill px-4 py-2 border mr-2" placeholder="Type your reply here... (Press Enter to send)" autocomplete="off">
                                <button type="submit" id="btnSendMessage" class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                                    <i class="mdi mdi-send text-white font-18"></i>
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
}
.ticket-item:hover {
    background-color: #f8fafc;
}
.ticket-item.active {
    background-color: #eff6ff !important;
    border-left: 4px solid #3b82f6 !important;
}
.msg-bubble-user {
    background-color: #ffffff;
    color: #1e293b;
    border-radius: 16px 16px 16px 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    max-width: 75%;
    border: 1px solid #e2e8f0;
}
.msg-bubble-admin {
    background: linear-gradient(135deg, #4f46e5, #4338ca);
    color: #ffffff;
    border-radius: 16px 16px 4px 16px;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
    max-width: 75%;
}
.btn-xs {
    padding: 2px 10px;
    font-size: 11px;
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

document.addEventListener('DOMContentLoaded', function() {
    loadTickets();
    startPolling();
});

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
            }
        })
        .catch(err => {
            console.error('Error fetching tickets:', err);
            document.getElementById('ticketListLoading').style.display = 'none';
        });
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
            <div class="text-center py-5 text-muted">
                <i class="mdi mdi-inbox-outline" style="font-size: 32px;"></i>
                <p class="mt-2 mb-0 small">No support tickets found</p>
            </div>`;
        return;
    }

    let html = '';
    tickets.forEach(t => {
        const isActive = activeTicketId === t.id;
        const unreadCount = t.unread_admin_count || 0;
        const statusBadge = t.status === 'resolved' 
            ? '<span class="badge badge-light text-muted border">Resolved</span>' 
            : '<span class="badge badge-success">Active</span>';
        
        const photo = t.user_photo && t.user_photo.trim() !== '' ? t.user_photo : '/assets/images/users/default-user.png';
        const timeAgo = formatTime(t.updated_at || t.created_at);

        html += `
            <div class="p-3 ticket-item ${isActive ? 'active' : ''}" onclick="selectTicket(${t.id})">
                <div class="d-flex align-items-center">
                    <div class="position-relative mr-3">
                        <img src="${photo}" class="rounded-circle" style="width: 44px; height: 44px; object-fit: cover; border: 1px solid #cbd5e1;" onerror="this.src='/assets/images/users/default-user.png'">
                        ${unreadCount > 0 ? `<span class="badge badge-danger position-absolute" style="top: -4px; right: -4px; font-size: 10px; border-radius: 10px;">${unreadCount}</span>` : ''}
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="font-weight-bold mb-0 text-dark text-truncate" style="max-width: 140px;">${escapeHtml(t.user_name || 'User')}</h6>
                            <small class="text-muted" style="font-size: 11px;">${timeAgo}</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0 text-muted small text-truncate" style="max-width: 170px;">
                                ${t.last_sender === 'admin' ? '<strong class="text-primary">You: </strong>' : ''}${escapeHtml(t.last_message || 'Started a conversation')}
                            </p>
                            ${statusBadge}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function selectTicket(ticketId) {
    activeTicketId = ticketId;
    lastMessageId = 0;
    
    // Highlight item
    document.querySelectorAll('.ticket-item').forEach(el => el.classList.remove('active'));
    
    // Show chat area
    document.getElementById('chatPlaceholder').style.setProperty('display', 'none', 'important');
    document.getElementById('chatHeader').style.setProperty('display', 'flex', 'important');
    document.getElementById('chatMessagesArea').style.display = 'block';
    document.getElementById('chatInputFooter').style.display = 'block';
    document.getElementById('chatMessagesList').innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    fetch(`/support-chats/messages/${ticketId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                activeTicketData = data.ticket;
                renderHeader(data.ticket);
                renderAllMessages(data.messages);
                loadTickets(true); // update badge counts
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
    
    const phoneEl = document.getElementById('chatHeaderPhone');
    phoneEl.textContent = ticket.user_phone || 'No phone';
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
        btnStatus.className = 'btn btn-sm btn-outline-warning rounded-pill px-3 shadow-sm';
    } else {
        btnStatus.innerHTML = '<i class="mdi mdi-check-circle mr-1"></i> Mark as Resolved';
        btnStatus.className = 'btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm';
    }

    if (ticket.user_photo && ticket.user_photo.trim() !== '') {
        document.getElementById('chatHeaderAvatar').src = ticket.user_photo;
    } else {
        document.getElementById('chatHeaderAvatar').src = '/assets/images/users/default-user.png';
    }
}

function renderAllMessages(messages) {
    const list = document.getElementById('chatMessagesList');
    list.innerHTML = '';

    if (!messages || messages.length === 0) {
        list.innerHTML = '<div class="text-center py-5 text-muted"><i class="mdi mdi-message-outline font-24"></i><p class="mt-2 small">No messages yet. Send a reply below.</p></div>';
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
                data.messages.forEach(m => {
                    appendMessageBubble(m);
                    if (m.id > lastMessageId) lastMessageId = m.id;
                });
                scrollToBottom();
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
            <div class="d-flex align-items-center justify-content-between mb-1" style="gap: 12px;">
                <strong style="font-size: 12px; opacity: ${isAdmin ? '0.9' : '0.75'};">${escapeHtml(m.sender_name || (isAdmin ? 'Support Team' : 'User'))}</strong>
                <small style="font-size: 10px; opacity: ${isAdmin ? '0.8' : '0.6'};">${timeStr}</small>
            </div>
            <div style="font-size: 14px; line-height: 1.5; white-space: pre-wrap;">${escapeHtml(m.message)}</div>
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
    area.scrollTop = area.scrollHeight;
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

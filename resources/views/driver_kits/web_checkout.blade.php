<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Order Partner Kit | Fiinway</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root {
            --primary: #15803d;
            --primary-dark: #166534;
            --primary-light: #f0fdf4;
            --primary-border: #bbf7d0;
            --text-main: #111827;
            --text-muted: #6b7280;
            --text-sub: #9ca3af;
            --bg-page: #f3f4f6;
            --bg-card: #ffffff;
            --border-color: #e5e7eb;
            --border-focus: #15803d;
            --danger: #dc2626;
            --danger-bg: #fef2f2;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: calc(90px + env(safe-area-inset-bottom));
        }

        /* Top App Bar */
        .app-bar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .app-bar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-back {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: var(--text-main);
        }

        .btn-back:active {
            background: #f3f4f6;
        }

        .app-bar-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
        }

        .secure-tag {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: var(--primary);
            background: var(--primary-light);
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid var(--primary-border);
        }

        .main-content {
            max-width: 480px;
            margin: 0 auto;
            padding: 12px 14px;
        }

        /* Standard Card */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 12px;
        }

        .card-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
        }

        .card-subtitle {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Dropdown select box */
        .kit-select-box {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-main);
            background-color: #ffffff;
            outline: none;
            cursor: pointer;
            transition: border-color 0.15s ease;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
        }

        .kit-select-box:focus {
            border-color: var(--border-focus);
        }

        /* Product Overview Card */
        .product-card {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .product-img-box {
            width: 68px;
            height: 68px;
            border-radius: 10px;
            background: #f3f4f6;
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .product-img-box svg {
            width: 34px;
            height: 34px;
            stroke: var(--primary);
        }

        .product-info {
            flex: 1;
        }

        .product-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-main);
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .product-category-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            color: #4b5563;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 6px;
        }

        .price-row {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }

        .price-final {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
        }

        .price-mrp {
            font-size: 13px;
            color: var(--text-sub);
            text-decoration: line-through;
        }

        .price-save {
            font-size: 11px;
            font-weight: 700;
            color: var(--primary);
            background: var(--primary-light);
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Inclusions List */
        .inclusions-box {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed var(--border-color);
        }

        .inclusions-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .inclusion-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: #374151;
            margin-bottom: 6px;
        }

        .inclusion-item:last-child {
            margin-bottom: 0;
        }

        .check-icon {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            border: 1px solid var(--primary-border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
            flex-shrink: 0;
        }

        /* Size Selector */
        .size-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-top: 6px;
        }

        .size-pill input {
            display: none;
        }

        .size-pill label {
            display: block;
            text-align: center;
            padding: 10px 0;
            background: #ffffff;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #374151;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .size-pill input:checked + label {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 12px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-main);
            background: #ffffff;
            outline: none;
            transition: border-color 0.15s ease;
        }

        .form-input:focus, .form-textarea:focus {
            border-color: var(--border-focus);
        }

        .form-textarea {
            resize: none;
        }

        /* Payment Options */
        .payment-option {
            border: 1.5px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 8px;
            cursor: pointer;
            background: #ffffff;
            transition: border-color 0.15s ease;
        }

        .payment-option:last-child {
            margin-bottom: 0;
        }

        .payment-option.active {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .payment-option-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .payment-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-icon-box {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #374151;
        }

        .payment-option.active .payment-icon-box {
            background: #ffffff;
            color: var(--primary);
        }

        .payment-texts {
            display: flex;
            flex-direction: column;
        }

        .payment-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
        }

        .payment-sub {
            font-size: 11.5px;
            color: var(--text-muted);
        }

        .custom-radio {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-option.active .custom-radio {
            border-color: var(--primary);
        }

        .payment-option.active .custom-radio::after {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
        }

        .wallet-pin-container {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
        }

        /* Error Banner */
        .error-banner {
            background: var(--danger-bg);
            border: 1px solid #fecaca;
            color: var(--danger);
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Fixed Bottom Action Bar */
        .bottom-action-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #ffffff;
            border-top: 1px solid var(--border-color);
            padding: 10px 16px calc(10px + env(safe-area-inset-bottom));
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 100;
        }

        .bottom-bar-inner {
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .price-summary {
            display: flex;
            flex-direction: column;
        }

        .price-summary-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .price-summary-val {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-main);
        }

        .btn-submit-order {
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.15s ease;
            flex: 1;
            max-width: 220px;
            text-align: center;
        }

        .btn-submit-order:active {
            background: var(--primary-dark);
        }

        /* Live Courier Tracking View (image3.png) */
        .tracking-header-card {
            background: linear-gradient(135deg, #15803d 0%, #166534 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 20px 18px;
            margin-bottom: 12px;
            box-shadow: 0 4px 16px rgba(21, 128, 61, 0.2);
            position: relative;
            overflow: hidden;
        }

        .tracking-header-card::after {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .tracking-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .tracking-order-badge {
            font-size: 11px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        .tracking-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 700;
            background: #ffffff;
            color: #15803d;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .status-dot-pulse {
            width: 8px;
            height: 8px;
            background: #15803d;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(21, 128, 61, 0.7);
            animation: pulse-dot 1.6s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(21, 128, 61, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(21, 128, 61, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(21, 128, 61, 0); }
        }

        .tracking-eta-box {
            margin-top: 8px;
        }

        .tracking-eta-label {
            font-size: 11.5px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tracking-eta-val {
            font-size: 19px;
            font-weight: 800;
            margin-top: 2px;
        }

        /* Courier & AWB Card */
        .courier-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .courier-top-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--border-color);
            margin-bottom: 12px;
        }

        .courier-badge-name {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .courier-icon-box {
            width: 36px;
            height: 36px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #15803d;
            font-weight: 800;
        }

        .courier-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
        }

        .courier-type-tag {
            font-size: 11px;
            color: var(--text-muted);
        }

        .awb-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9fafb;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }

        .awb-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }

        .awb-code {
            font-size: 14px;
            font-weight: 800;
            color: #111827;
            font-family: monospace;
            letter-spacing: 0.5px;
        }

        .btn-copy-awb {
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s ease;
        }

        .btn-copy-awb:active {
            background: #f3f4f6;
        }

        /* Vertical Tracking Timeline */
        .timeline-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 18px 16px;
            margin-bottom: 12px;
        }

        .timeline-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            margin-bottom: 18px;
        }

        .timeline-steps {
            position: relative;
            padding-left: 28px;
        }

        .timeline-steps::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 8px;
            bottom: 22px;
            width: 2px;
            background: #e5e7eb;
        }

        .timeline-step {
            position: relative;
            margin-bottom: 22px;
        }

        .timeline-step:last-child {
            margin-bottom: 0;
        }

        .timeline-node {
            position: absolute;
            left: -28px;
            top: 2px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #9ca3af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #ffffff;
            z-index: 2;
        }

        .timeline-step.completed .timeline-node {
            background: #15803d;
            border-color: #15803d;
        }

        .timeline-step.active .timeline-node {
            background: #ffffff;
            border-color: #15803d;
            box-shadow: 0 0 0 4px rgba(21, 128, 61, 0.2);
        }

        .timeline-step.active .timeline-node::after {
            content: '';
            width: 8px;
            height: 8px;
            background: #15803d;
            border-radius: 50%;
        }

        .timeline-step-content {
            display: flex;
            flex-direction: column;
        }

        .timeline-step-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 2px;
        }

        .timeline-step-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text-main);
        }

        .timeline-step.pending .timeline-step-title {
            color: #9ca3af;
        }

        .timeline-step-time {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .timeline-step-desc {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* Delivery Executive Card */
        .executive-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .executive-info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .executive-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #eff6ff;
            color: #2563eb;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #bfdbfe;
            flex-shrink: 0;
        }

        .executive-details {
            flex: 1;
        }

        .executive-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
        }

        .executive-role {
            font-size: 11.5px;
            color: var(--text-muted);
        }

        .executive-vehicle {
            font-size: 11px;
            color: #15803d;
            font-weight: 600;
            margin-top: 1px;
        }

        .executive-actions {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .btn-exec-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 9px 6px;
            background: #f9fafb;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-main);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-exec-action:active {
            background: #e5e7eb;
        }

        .btn-exec-action svg {
            width: 16px;
            height: 16px;
        }

        /* Order Details Summary Card */
        .info-summary-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 12.5px;
            padding: 6px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-row-label {
            color: var(--text-muted);
        }

        .info-row-val {
            font-weight: 600;
            color: var(--text-main);
            text-align: right;
            max-width: 65%;
        }

        /* Cashback Banner */
        .cashback-banner {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cashback-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #10b981;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .cashback-title {
            font-size: 13px;
            font-weight: 700;
            color: #065f46;
        }

        .cashback-sub {
            font-size: 11.5px;
            color: #047857;
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #1f2937;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 30px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
            z-index: 9999;
            pointer-events: none;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>

    <!-- App Header -->
    <div class="app-bar">
        <div class="app-bar-left">
            <button type="button" class="btn-back" onclick="closeWebView()" aria-label="Go Back">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </button>
            <span class="app-bar-title">Partner Starter Kit</span>
        </div>
        <div class="secure-tag">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            Official Fiinway Desk
        </div>
    </div>

    <div class="main-content">
        @if(session('error'))
            <div class="error-banner">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($existingOrder)
            @php
                $deliveryStatus = strtolower($existingOrder->delivery_status ?? 'booked');
                $statusMap = [
                    'booked' => ['label' => 'Order Confirmed', 'level' => 1],
                    'processing' => ['label' => 'Processing', 'level' => 1],
                    'picked_up' => ['label' => 'Packed & Dispatched', 'level' => 2],
                    'in_transit' => ['label' => 'In Transit', 'level' => 3],
                    'out_for_delivery' => ['label' => 'Out for Delivery', 'level' => 4],
                    'delivered' => ['label' => 'Delivered', 'level' => 5],
                ];
                $currentStatusInfo = $statusMap[$deliveryStatus] ?? ['label' => ucfirst(str_replace('_', ' ', $deliveryStatus)), 'level' => 2];
                $currentLevel = $currentStatusInfo['level'];

                $rawTimeline = is_array($existingOrder->status_timeline) ? $existingOrder->status_timeline : (json_decode($existingOrder->status_timeline ?? '[]', true) ?: []);

                $defaultTimeline = [
                    [
                        'status' => 'booked',
                        'title' => 'Order Confirmed',
                        'date' => $existingOrder->purchased_at ? \Carbon\Carbon::parse($existingOrder->purchased_at)->format('d M Y, h:i A') : date('d M Y, h:i A'),
                        'description' => 'Your partner marketing kit order has been verified and registered.',
                        'level' => 1,
                    ],
                    [
                        'status' => 'picked_up',
                        'title' => 'Packed & Dispatched',
                        'date' => ($currentLevel >= 2) ? date('d M Y, h:i A', strtotime(($existingOrder->created_at ?? now()) . ' +8 hours')) : 'Estimated within 24 hrs',
                        'description' => 'Kit packed at Fiinway Central Fulfillment Hub and handed over to BlueDart.',
                        'level' => 2,
                    ],
                    [
                        'status' => 'in_transit',
                        'title' => 'In Transit',
                        'date' => ($currentLevel >= 3) ? date('d M Y, h:i A', strtotime(($existingOrder->created_at ?? now()) . ' +20 hours')) : 'Pending hub handover',
                        'description' => 'Shipment departed Central Sorting Facility to your regional hub.',
                        'level' => 3,
                    ],
                    [
                        'status' => 'out_for_delivery',
                        'title' => 'Out for Delivery',
                        'date' => ($currentLevel >= 4) ? date('d M Y, h:i A', strtotime(($existingOrder->created_at ?? now()) . ' +32 hours')) : 'Pending local dispatch',
                        'description' => 'Courier delivery associate is out for delivery in your area.',
                        'level' => 4,
                    ],
                    [
                        'status' => 'delivered',
                        'title' => 'Delivered',
                        'date' => ($currentLevel >= 5) ? date('d M Y, h:i A', strtotime(($existingOrder->created_at ?? now()) . ' +36 hours')) : ($existingOrder->expected_delivery_date ?: date('D, d M Y', strtotime('+3 days'))),
                        'description' => 'Kit safely delivered with OTP verification.',
                        'level' => 5,
                    ],
                ];

                $trackingTimeline = !empty($rawTimeline) ? $rawTimeline : $defaultTimeline;
            @endphp

            <!-- 1. Live Tracking Gradient Header -->
            <div class="tracking-header-card">
                <div class="tracking-top-row">
                    <span class="tracking-order-badge">#{{ $existingOrder->order_number }}</span>
                    <span class="tracking-status-pill">
                        <span class="status-dot-pulse"></span>
                        {{ $currentStatusInfo['label'] }}
                    </span>
                </div>
                <div class="tracking-eta-box">
                    <div class="tracking-eta-label">Estimated Delivery</div>
                    <div class="tracking-eta-val">
                        {{ $existingOrder->expected_delivery_date ?: date('l, d M Y', strtotime(($existingOrder->created_at ?? now()) . ' +3 days')) }}
                    </div>
                </div>
            </div>

            <!-- 2. Courier Logistics & AWB Code Card -->
            <div class="courier-card">
                <div class="courier-top-info">
                    <div class="courier-badge-name">
                        <div class="courier-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="1" y="3" width="15" height="13"></rect>
                                <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                                <circle cx="5.5" cy="18.5" r="2.5"></circle>
                                <circle cx="18.5" cy="18.5" r="2.5"></circle>
                            </svg>
                        </div>
                        <div>
                            <div class="courier-name">{{ $existingOrder->courier_partner ?: 'BlueDart Express' }}</div>
                            <div class="courier-type-tag">Official Logistics Partner • Express Air</div>
                        </div>
                    </div>
                    <span style="font-size: 11px; font-weight: 700; color: #15803d; background: #f0fdf4; padding: 4px 8px; border-radius: 6px; border: 1px solid #bbf7d0;">
                        Priority
                    </span>
                </div>

                <div class="awb-container">
                    <div>
                        <div class="awb-label">AWB Tracking Code</div>
                        <div class="awb-code" id="awbCodeText">{{ $existingOrder->tracking_code ?: ($existingOrder->tracking_number ?: ('FWP' . ($existingOrder->id + 84920194))) }}</div>
                    </div>
                    <button type="button" class="btn-copy-awb" onclick="copyAWB()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        Copy
                    </button>
                </div>
            </div>

            <!-- 3. Real-Time Vertical Tracking Milestone Stepper -->
            <div class="timeline-card">
                <div class="timeline-title">Delivery Progress</div>
                <div class="timeline-steps">
                    @foreach($trackingTimeline as $step)
                        @php
                            $stepLevel = $step['level'] ?? ($statusMap[$step['status']]['level'] ?? 1);
                            $isCompleted = ($step['is_completed'] ?? false) || ($stepLevel < $currentLevel);
                            $isActive = ($step['is_current'] ?? false) || ($stepLevel === $currentLevel);
                            $stepClass = $isCompleted ? 'completed' : ($isActive ? 'active' : 'pending');
                        @endphp
                        <div class="timeline-step {{ $stepClass }}">
                            <div class="timeline-node">
                                @if($isCompleted)
                                    ✓
                                @endif
                            </div>
                            <div class="timeline-step-content">
                                <div class="timeline-step-header">
                                    <span class="timeline-step-title">{{ $step['title'] ?? 'Milestone' }}</span>
                                    <span class="timeline-step-time">{{ $step['date'] ?? '' }}</span>
                                </div>
                                <div class="timeline-step-desc">{{ $step['description'] ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 4. Delivery Executive Contact Card -->
            <div class="executive-card">
                <div class="card-title" style="margin-bottom: 12px;">Delivery Associate</div>
                <div class="executive-info-row">
                    <div class="executive-avatar">
                        {{ strtoupper(substr($existingOrder->delivery_partner_name ?: 'RK', 0, 2)) }}
                    </div>
                    <div class="executive-details">
                        <div class="executive-name">{{ $existingOrder->delivery_partner_name ?: 'Ramesh Kumar' }}</div>
                        <div class="executive-role">BlueDart Field Associate</div>
                        <div class="executive-vehicle">{{ $existingOrder->delivery_partner_vehicle ?: 'Delivery Van (KA-01-EE-4521)' }}</div>
                    </div>
                </div>
                <div class="executive-actions">
                    <a href="tel:{{ $existingOrder->delivery_partner_phone ?: '+919876543210' }}" class="btn-exec-action">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <span>Call</span>
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $existingOrder->delivery_partner_phone ?? '919876543210') }}?text=Hi%20regarding%20my%20Fiinway%20Kit%20delivery%20{{ $existingOrder->order_number }}" target="_blank" class="btn-exec-action">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span>WhatsApp</span>
                    </a>
                    <button type="button" class="btn-exec-action" onclick="shareTracking()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="18" cy="5" r="3"></circle>
                            <circle cx="6" cy="12" r="3"></circle>
                            <circle cx="18" cy="19" r="3"></circle>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                        </svg>
                        <span>Share</span>
                    </button>
                </div>
            </div>

            <!-- 5. Order Package Details & Delivery Address Summary -->
            <div class="info-summary-card">
                <div class="card-title" style="margin-bottom: 12px;">Order Summary</div>
                <div class="info-row">
                    <span class="info-row-label">Kit Package</span>
                    <span class="info-row-val">{{ $existingOrder->kit_title ?: 'Partner Starter Kit' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Selected Size</span>
                    <span class="info-row-val">{{ strtoupper($existingOrder->selected_size ?: ($existingOrder->tshirt_size ?: 'L')) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Payment Status</span>
                    <span class="info-row-val" style="color: #15803d;">₹{{ number_format($existingOrder->amount, 2) }} (Paid via {{ strtoupper($existingOrder->payment_method) }})</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Recipient</span>
                    <span class="info-row-val">{{ $existingOrder->receiver_name }} • {{ $existingOrder->receiver_phone }}</span>
                </div>
                <div class="info-row">
                    <span class="info-row-label">Shipping Address</span>
                    <span class="info-row-val">{{ $existingOrder->shipping_address }}</span>
                </div>
            </div>

            <!-- 6. Action Buttons -->
            <div style="margin-top: 18px; margin-bottom: 24px; display: flex; flex-direction: column; gap: 10px; align-items: center;">
                <button type="button" class="btn-submit-order" style="width: 100%; max-width: 100%; padding: 14px;" onclick="closeWebView()">
                    Return to Dashboard
                </button>
                <a href="{{ route('driver-kits.webCheckout', ['driver_id' => $driver ? $driver->id : '', 'force_new' => 1]) }}" style="font-size: 12.5px; font-weight: 600; color: #15803d; text-decoration: none; padding: 6px;">
                    Need another kit? Order New Package →
                </a>
            </div>
        @else
            <form id="checkoutForm" action="{{ route('driver-kits.webSubmit') }}" method="POST">
                @csrf
                <input type="hidden" name="driver_id" value="{{ $driver ? $driver->id : 1 }}">
                <input type="hidden" name="kit_id" id="kitIdInput" value="{{ $kit ? $kit->id : 1 }}">
                <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="razorpay">
                <input type="hidden" name="transaction_id" id="transactionId" value="">

                <!-- 1. Kit Selection Dropdown Card -->
                <div class="card">
                    <div class="card-title" style="margin-bottom: 8px;">Select Partner Package</div>
                    <select id="kitDropdown" class="kit-select-box" onchange="onKitSelected(this.value)">
                        @foreach($allKits as $k)
                            <option value="{{ $k->id }}" {{ ($kit && $kit->id == $k->id) ? 'selected' : '' }}>
                                {{ $k->title }} — ₹{{ number_format($k->price, 0) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Cashback Callout Banner -->
                <div class="cashback-banner">
                    <div class="cashback-icon">₹</div>
                    <div>
                        <div class="cashback-title">₹100 Wallet Cashback Reward</div>
                        <div class="cashback-sub">₹100 will be credited directly to your Fiinway wallet upon kit delivery.</div>
                    </div>
                </div>

                <!-- 2. Product Summary Card -->
                <div class="card">
                    <div class="product-card">
                        <div class="product-img-box">
                            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </div>
                        <div class="product-info">
                            <span class="product-category-tag" id="kitCategoryTag">
                                {{ strtoupper($kit->category_code ?? 'PARTNER') }}
                            </span>
                            <h1 class="product-title" id="kitTitle">{{ $kit ? $kit->title : 'Partner Starter Kit' }}</h1>
                            <div class="price-row">
                                <span class="price-final" id="priceFinal">₹{{ number_format($kit->price ?? 499, 2) }}</span>
                                <span class="price-mrp" id="priceMrp">₹{{ number_format(($kit->price ?? 499) * 2, 0) }}</span>
                                <span class="price-save">50% OFF</span>
                            </div>
                        </div>
                    </div>

                    <!-- Included Items List -->
                    <div class="inclusions-box">
                        <div class="inclusions-title">Package Inclusions</div>
                        <div id="kitInclusionsList">
                            @php
                                $items = is_array($kit->items_included) ? $kit->items_included : (json_decode($kit->items_included, true) ?? ['Fiinway Branded T-Shirt', 'Official ID Card & Lanyard']);
                            @endphp
                            @foreach($items as $item)
                                <div class="inclusion-item">
                                    <span class="check-icon">✓</span>
                                    <span>{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 3. T-Shirt Size Selector -->
                <div class="card">
                    <div class="card-header-row">
                        <span class="card-title">Select T-Shirt Size</span>
                        <span style="font-size: 11px; color: var(--text-muted);">Regular Fit</span>
                    </div>
                    <div class="size-grid">
                        <div class="size-pill">
                            <input type="radio" name="tshirt_size" id="size_s" value="S">
                            <label for="size_s">S</label>
                        </div>
                        <div class="size-pill">
                            <input type="radio" name="tshirt_size" id="size_m" value="M">
                            <label for="size_m">M</label>
                        </div>
                        <div class="size-pill">
                            <input type="radio" name="tshirt_size" id="size_l" value="L" checked>
                            <label for="size_l">L</label>
                        </div>
                        <div class="size-pill">
                            <input type="radio" name="tshirt_size" id="size_xl" value="XL">
                            <label for="size_xl">XL</label>
                        </div>
                        <div class="size-pill">
                            <input type="radio" name="tshirt_size" id="size_xxl" value="XXL">
                            <label for="size_xxl">2XL</label>
                        </div>
                    </div>
                </div>

                <!-- 4. Delivery Address Form -->
                <div class="card">
                    <div class="card-title" style="margin-bottom: 10px;">Delivery Address</div>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="receiver_name" class="form-input" value="{{ $driver ? trim(($driver->prenom ?? '') . ' ' . ($driver->nom ?? '')) : '' }}" required placeholder="Receiver name">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" name="receiver_phone" class="form-input" value="{{ $driver ? ($driver->phone ?? '') : '' }}" required placeholder="10-digit mobile number">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Complete Shipping Address</label>
                        <textarea name="shipping_address" class="form-textarea" rows="2" required placeholder="House/Flat no, Street, Landmark, City & Pincode">{{ $driver ? ($driver->address ?? '') : '' }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Postal Pincode</label>
                        <input type="text" name="pincode" class="form-input" value="560001" required placeholder="6-digit postal code" maxlength="6">
                    </div>
                </div>

                <!-- 5. Payment Method Selection -->
                <div class="card">
                    <div class="card-title" style="margin-bottom: 10px;">Payment Method</div>

                    <!-- Online UPI / Card Option -->
                    <div class="payment-option active" id="optRazorpay" onclick="selectPayment('razorpay')">
                        <div class="payment-option-header">
                            <div class="payment-left">
                                <div class="payment-icon-box">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                        <line x1="2" y1="10" x2="22" y2="10"></line>
                                    </svg>
                                </div>
                                <div class="payment-texts">
                                    <span class="payment-name">UPI / Cards / NetBanking</span>
                                    <span class="payment-sub">Google Pay, PhonePe, Paytm, All Cards</span>
                                </div>
                            </div>
                            <div class="custom-radio"></div>
                        </div>
                    </div>

                    <!-- Wallet Option -->
                    <div class="payment-option" id="optWallet" onclick="selectPayment('wallet')">
                        <div class="payment-option-header">
                            <div class="payment-left">
                                <div class="payment-icon-box">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path>
                                        <path d="M4 6v12a2 2 0 0 0 2 2h14v-4"></path>
                                        <path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"></path>
                                    </svg>
                                </div>
                                <div class="payment-texts">
                                    <span class="payment-name">Fiinway Wallet Balance</span>
                                    <span class="payment-sub">
                                        Available: ₹{{ number_format($walletBalance ?? 0, 2) }}
                                        <span id="walletInsufficientTag" style="display: {{ ($walletBalance ?? 0) < ($kit->price ?? 499) ? 'inline' : 'none' }}; color: var(--danger); font-weight: 600;">(Insufficient)</span>
                                    </span>
                                </div>
                            </div>
                            <div class="custom-radio"></div>
                        </div>

                        <div id="walletPinSection" class="wallet-pin-container" style="display: none;">
                            <label class="form-label">Enter Driver M-PIN</label>
                            <input type="password" name="mpin" id="walletMpinInput" class="form-input" placeholder="Enter M-PIN to verify" maxlength="6">
                        </div>
                    </div>
                </div>

                <!-- Sticky Bottom Checkout Bar -->
                <div class="bottom-action-bar">
                    <div class="bottom-bar-inner">
                        <div class="price-summary">
                            <span class="price-summary-label">Total Amount</span>
                            <span class="price-summary-val" id="priceSummaryVal">₹{{ number_format($kit->price ?? 499, 2) }}</span>
                        </div>
                        <button type="button" id="btnSubmitOrder" onclick="handlePaymentSubmission()" class="btn-submit-order">
                            Pay ₹{{ number_format($kit->price ?? 499, 2) }}
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>

    <script>
        const allKitsData = @json($allKits ?? []);
        let currentKit = @json($kit ?? null) || (allKitsData.length > 0 ? allKitsData[0] : { id: 1, price: 499 });
        let kitAmount = parseFloat(currentKit.price || 499);
        const walletBalance = {{ (float)($walletBalance ?? 0) }};
        const razorpayKey = "{{ $razorpayKey ?? 'rzp_test_key' }}";
        let currentPayment = 'razorpay';

        function onKitSelected(kitId) {
            const found = allKitsData.find(k => k.id == kitId);
            if (!found) return;

            currentKit = found;
            kitAmount = parseFloat(found.price || 499);

            // Update hidden input
            document.getElementById('kitIdInput').value = found.id;

            // Update UI elements
            document.getElementById('kitTitle').textContent = found.title;
            document.getElementById('kitCategoryTag').textContent = (found.category_code || 'PARTNER').toUpperCase();
            document.getElementById('priceFinal').textContent = '₹' + kitAmount.toFixed(2);
            document.getElementById('priceMrp').textContent = '₹' + (kitAmount * 2).toFixed(0);
            document.getElementById('priceSummaryVal').textContent = '₹' + kitAmount.toFixed(2);
            document.getElementById('btnSubmitOrder').textContent = 'Pay ₹' + kitAmount.toFixed(2);

            // Update inclusions
            let items = [];
            if (Array.isArray(found.items_included)) {
                items = found.items_included;
            } else if (typeof found.items_included === 'string') {
                try { items = JSON.parse(found.items_included); } catch(e) { items = [found.items_included]; }
            }
            if (!items || items.length === 0) {
                items = ['Fiinway Branded T-Shirt', 'Official ID Card & Lanyard'];
            }

            const inclusionsContainer = document.getElementById('kitInclusionsList');
            inclusionsContainer.innerHTML = items.map(item => `
                <div class="inclusion-item">
                    <span class="check-icon">✓</span>
                    <span>${item}</span>
                </div>
            `).join('');

            // Update wallet balance indicator
            const tag = document.getElementById('walletInsufficientTag');
            if (tag) {
                tag.style.display = (walletBalance < kitAmount) ? 'inline' : 'none';
            }
        }

        function selectPayment(method) {
            currentPayment = method;
            document.getElementById('selectedPaymentMethod').value = method;

            document.getElementById('optRazorpay').classList.toggle('active', method === 'razorpay');
            document.getElementById('optWallet').classList.toggle('active', method === 'wallet');

            const pinSection = document.getElementById('walletPinSection');
            if (method === 'wallet') {
                pinSection.style.display = 'block';
            } else {
                pinSection.style.display = 'none';
            }
        }

        function handlePaymentSubmission() {
            const form = document.getElementById('checkoutForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            if (currentPayment === 'wallet') {
                if (walletBalance < kitAmount) {
                    alert('Insufficient wallet balance. Please select UPI / Cards option.');
                    return;
                }
                const mpin = document.getElementById('walletMpinInput').value.trim();
                if (!mpin) {
                    alert('Please enter your M-PIN to authorize payment.');
                    document.getElementById('walletMpinInput').focus();
                    return;
                }
                form.submit();
            } else {
                if (typeof Razorpay !== 'undefined' && razorpayKey && razorpayKey !== '') {
                    const receiverName = form.elements['receiver_name'].value;
                    const receiverPhone = form.elements['receiver_phone'].value;

                    const options = {
                        "key": razorpayKey,
                        "amount": Math.round(kitAmount * 100),
                        "currency": "INR",
                        "name": "Fiinway Partner Desk",
                        "description": currentKit.title || "Partner Starter Kit",
                        "prefill": {
                            "name": receiverName,
                            "contact": receiverPhone,
                        },
                        "theme": {
                            "color": "#15803d"
                        },
                        "handler": function (response) {
                            document.getElementById('transactionId').value = response.razorpay_payment_id || ('TXN-' + Date.now());
                            form.submit();
                        },
                        "modal": {
                            "ondismiss": function() {
                                console.log('Payment window closed');
                            }
                        }
                    };
                    const rzp = new Razorpay(options);
                    rzp.on('payment.failed', function (response){
                        alert('Payment Failed: ' + (response.error.description || 'Transaction could not be processed'));
                    });
                    rzp.open();
                } else {
                    form.submit();
                }
            }
        }

        function closeWebView() {
            if (window.AppBridge) {
                window.AppBridge.postMessage('close');
            } else {
                window.history.back();
            }
        }

        function copyAWB() {
            const el = document.getElementById('awbCodeText');
            if (!el) return;
            const text = el.innerText.trim();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast('Tracking AWB copied to clipboard!');
                }).catch(() => {
                    showToast('AWB: ' + text);
                });
            } else {
                showToast('AWB: ' + text);
            }
        }

        function shareTracking() {
            const el = document.getElementById('awbCodeText');
            const text = el ? el.innerText.trim() : '';
            if (navigator.share) {
                navigator.share({
                    title: 'Track My Fiinway Partner Kit',
                    text: 'Live tracking for my partner welcome kit. AWB: ' + text,
                    url: window.location.href
                }).catch(() => {});
            } else {
                copyAWB();
            }
        }

        function showToast(msg) {
            let toast = document.getElementById('toast');
            if (!toast) return;
            toast.innerText = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2600);
        }
    </script>
    <div id="toast" class="toast"></div>
</body>
</html>


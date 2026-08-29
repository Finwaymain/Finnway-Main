<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Order Confirmed | Fiinway Partner Kit</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #15803d;
            --primary-dark: #166534;
            --primary-light: #f0fdf4;
            --primary-border: #bbf7d0;
            --text-main: #111827;
            --text-muted: #6b7280;
            --bg-page: #f3f4f6;
            --bg-card: #ffffff;
            --border-color: #e5e7eb;
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 16px;
        }

        .success-card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 28px 20px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        }

        .icon-circle {
            width: 56px;
            height: 56px;
            background: var(--primary-light);
            color: var(--primary);
            border: 1.5px solid var(--primary-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
        }

        .icon-circle svg {
            width: 28px;
            height: 28px;
            stroke: var(--primary);
        }

        h1 {
            font-size: 19px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        p {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .order-summary-box {
            background: #f9fafb;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 20px;
            text-align: left;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 12.5px;
        }

        .summary-row:last-child {
            margin-bottom: 0;
        }

        .summary-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .summary-val {
            color: var(--text-main);
            font-weight: 700;
        }

        .btn-done {
            display: block;
            width: 100%;
            background: var(--primary);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 0;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background-color 0.15s ease;
        }

        .btn-done:active {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>

    <div class="success-card">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h1>Kit Order Confirmed</h1>
        <p>Your partner kit has been ordered and is being processed for dispatch to your registered address.</p>

        <div class="order-summary-box">
            <div class="summary-row">
                <span class="summary-label">Order Number:</span>
                <span class="summary-val" style="color: #2563eb;">#{{ $order->order_number }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Package:</span>
                <span class="summary-val">{{ $order->kit_title }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">T-Shirt Size:</span>
                <span class="summary-val">{{ $order->tshirt_size }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Amount Paid:</span>
                <span class="summary-val" style="color: var(--primary);">₹{{ number_format($order->amount, 2) }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Delivery Status:</span>
                <span class="summary-val">Processing for Dispatch</span>
            </div>
        </div>

        <button type="button" onclick="closeAndReturn()" class="btn-done">
            Return to Dashboard
        </button>
    </div>

    <script>
        function closeAndReturn() {
            if (window.AppBridge) {
                window.AppBridge.postMessage(JSON.stringify({ action: 'kit_purchased' }));
                window.AppBridge.postMessage('close');
            } else {
                window.history.back();
            }
        }

        // Notify AppBridge on load
        if (window.AppBridge) {
            window.AppBridge.postMessage(JSON.stringify({ action: 'kit_purchased' }));
        }
    </script>
</body>
</html>

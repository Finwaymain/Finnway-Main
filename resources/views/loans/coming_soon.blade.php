<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $selectedProduct['title'] }} - Coming Soon</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f9fafb;
            color: #111827;
            padding: 16px;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Top Header Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            font-size: 13px;
            font-weight: 700;
            color: #4f46e5;
            background: #eef2ff;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 0.02em;
        }

        .status-badge {
            font-size: 12px;
            font-weight: 700;
            background: #fef3c7;
            color: #92400e;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Main Hero Card */
        .hero-card {
            background: {{ $selectedProduct['bg_color'] }};
            border: 1px solid {{ $selectedProduct['border_color'] }};
            border-radius: 16px;
            padding: 24px 20px;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            text-align: center;
        }

        .product-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: {{ $selectedProduct['color'] }};
            margin-bottom: 8px;
        }

        .product-title {
            font-size: 24px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
        }

        .product-limit {
            font-size: 18px;
            font-weight: 700;
            color: {{ $selectedProduct['color'] }};
            margin-bottom: 12px;
        }

        .product-desc {
            font-size: 13px;
            color: #4b5563;
            max-width: 360px;
            margin: 0 auto;
        }

        /* User Profile / Digital Pocket Card */
        .profile-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .profile-header {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .verified-badge {
            color: #16a34a;
            font-size: 11px;
            font-weight: 600;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .profile-item {
            background: #f9fafb;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
        }

        .profile-item.full-width {
            grid-column: span 2;
        }

        .item-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .item-value {
            font-size: 14px;
            color: #111827;
            font-weight: 700;
            word-break: break-all;
        }

        /* Features List */
        .features-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 18px 16px;
            margin-bottom: 20px;
        }

        .features-header {
            font-size: 13px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            font-size: 13px;
            color: #374151;
            font-weight: 500;
        }

        .feature-item:last-child {
            margin-bottom: 0;
        }

        .feature-bullet {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #f3f4f6;
            color: {{ $selectedProduct['color'] }};
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            margin-right: 10px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Call To Action */
        .cta-section {
            margin-top: auto;
            padding-top: 12px;
        }

        .cta-btn {
            display: block;
            width: 100%;
            background: #111827;
            color: #ffffff;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: all 0.15s ease-in-out;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .cta-btn:active {
            transform: scale(0.98);
        }

        .cta-btn.registered {
            background: #16a34a;
            color: #ffffff;
            pointer-events: none;
        }

        .disclaimer {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 12px;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Top Bar -->
        <div class="top-bar">
            <span class="brand-badge">FIINWAY LOANS & CREDIT</span>
            <span class="status-badge">COMING SOON</span>
        </div>

        <!-- Hero Card -->
        <div class="hero-card">
            <div class="product-tag">{{ $selectedProduct['tag'] }}</div>
            <h1 class="product-title">{{ $selectedProduct['title'] }}</h1>
            <div class="product-limit">{{ $selectedProduct['limit'] }}</div>
            <p class="product-desc">{{ $selectedProduct['description'] }}</p>
        </div>

        <!-- Applicant Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <span>Applicant Information</span>
                <span class="verified-badge">Pre-Registration Active</span>
            </div>
            <div class="profile-grid">
                <div class="profile-item full-width">
                    <div class="item-label">Applicant Name</div>
                    <div class="item-value">{{ $name }}</div>
                </div>
                <div class="profile-item">
                    <div class="item-label">Mobile Number</div>
                    <div class="item-value">{{ $mobile ?: 'Registered Mobile' }}</div>
                </div>
                <div class="profile-item">
                    <div class="item-label">Digital Pocket #</div>
                    <div class="item-value">{{ $pocketNumber ?: 'Active Pocket' }}</div>
                </div>
            </div>
        </div>

        <!-- Product Highlights -->
        <div class="features-card">
            <div class="features-header">Credit Highlights</div>
            @foreach($selectedProduct['features'] as $f)
                <div class="feature-item">
                    <span class="feature-bullet">✓</span>
                    <span>{{ $f }}</span>
                </div>
            @endforeach
        </div>

        <!-- Action Section -->
        <div class="cta-section">
            <button class="cta-btn" id="notifyBtn" onclick="registerInterest()">
                Notify Me When Launched
            </button>
            <p class="disclaimer">
                Loans & Credit are provided in partnership with RBI-regulated NBFC and banking partners. Terms & conditions apply upon formal rollout.
            </p>
        </div>
    </div>

    <script>
        function registerInterest() {
            const btn = document.getElementById('notifyBtn');
            btn.classList.add('registered');
            btn.innerHTML = '✓ You are on the Early Access List!';
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#FF4B2B">
    <title>Fiinway Food - Coming Soon</title>
    <style>
        :root {
            --primary: #FF4B2B;
            --primary-dark: #E23744;
            --primary-glow: rgba(255, 75, 43, 0.25);
            --bg-dark: #0B0F19;
            --card-bg: rgba(255, 255, 255, 0.05);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #FFFFFF;
            --text-muted: #94A3B8;
            --accent-green: #10B981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(circle at 50% 10%, rgba(255, 75, 43, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 107, 107, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 20% 70%, rgba(59, 130, 246, 0.06) 0%, transparent 40%);
            color: var(--text-main);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 24px 20px 32px;
            overflow-x: hidden;
            position: relative;
        }

        /* Floating background food emojis */
        .ambient-emoji {
            position: absolute;
            font-size: 28px;
            opacity: 0.18;
            user-select: none;
            pointer-events: none;
            animation: float 6s ease-in-out infinite alternate;
        }
        .e1 { top: 12%; left: 8%; animation-delay: 0s; }
        .e2 { top: 18%; right: 10%; animation-delay: 1.5s; font-size: 32px; }
        .e3 { top: 48%; left: 6%; animation-delay: 2.2s; font-size: 24px; }
        .e4 { top: 62%; right: 8%; animation-delay: 0.8s; font-size: 30px; }
        .e5 { bottom: 15%; left: 14%; animation-delay: 3s; font-size: 26px; }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-15px) rotate(8deg); }
        }

        /* Container */
        .wrapper {
            width: 100%;
            max-width: 460px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            z-index: 2;
        }

        /* Header / Logo */
        .brand-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .brand-logo {
            height: 38px;
            width: auto;
            object-fit: contain;
        }

        .brand-badge {
            background: linear-gradient(135deg, #FF4B2B, #FF416C);
            color: #FFF;
            font-size: 11px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* Hero Graphic */
        .hero-art-container {
            position: relative;
            margin: 12px 0 24px;
            width: 140px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-glow {
            position: absolute;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, var(--primary-glow) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulseGlow 3s ease-in-out infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(0.95); opacity: 0.6; }
            50% { transform: scale(1.15); opacity: 1; }
        }

        .hero-icon-card {
            width: 100px;
            height: 100px;
            border-radius: 28px;
            background: linear-gradient(145deg, rgba(255, 75, 43, 0.15), rgba(255, 255, 255, 0.05));
            border: 1px solid rgba(255, 75, 43, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        .hero-icon {
            font-size: 48px;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.3));
            animation: bounceDish 2.5s ease-in-out infinite;
        }

        @keyframes bounceDish {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-6px) scale(1.05); }
        }

        /* Pill Badge */
        .pill-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 75, 43, 0.12);
            border: 1px solid rgba(255, 75, 43, 0.3);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            color: #FF7654;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .pill-dot {
            width: 8px;
            height: 8px;
            background: #FF4B2B;
            border-radius: 50%;
            box-shadow: 0 0 8px #FF4B2B;
            animation: blink 1.5s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }

        /* Headings */
        h1 {
            font-size: 26px;
            font-weight: 800;
            line-height: 1.25;
            color: #FFFFFF;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        h1 span {
            background: linear-gradient(135deg, #FF6B4A, #FFA36C);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.lead {
            font-size: 14px;
            line-height: 1.55;
            color: var(--text-muted);
            margin-bottom: 24px;
            padding: 0 8px;
        }

        /* Feature Cards */
        .features-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            margin-bottom: 24px;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 14px;
            text-align: left;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: transform 0.2s ease;
        }

        .feature-icon-box {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .feature-text h3 {
            font-size: 14px;
            font-weight: 700;
            color: #F1F5F9;
            margin-bottom: 2px;
        }

        .feature-text p {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.35;
        }

        /* Progress Box */
        .progress-box {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 20px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .progress-header .badge-status {
            color: var(--accent-green);
            font-weight: 700;
        }

        .progress-bar-bg {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            width: 85%;
            background: linear-gradient(90deg, #FF4B2B, #FF8A00);
            border-radius: 10px;
            position: relative;
        }

        /* Footer & Actions */
        .footer-note {
            margin-top: auto;
            width: 100%;
            max-width: 440px;
            text-align: center;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            z-index: 2;
        }

        .partner-link-box {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .partner-link-box a {
            color: #FF7654;
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }

        .partner-link-box a:active {
            text-decoration: underline;
        }

        .copyright {
            font-size: 11px;
            color: #64748B;
            letter-spacing: 0.2px;
        }
    </style>
</head>
<body>

    <!-- Ambient Food Elements -->
    <div class="ambient-emoji e1">🍔</div>
    <div class="ambient-emoji e2">🍕</div>
    <div class="ambient-emoji e3">🍜</div>
    <div class="ambient-emoji e4">🍱</div>
    <div class="ambient-emoji e5">🍰</div>

    <!-- Main Content Wrapper -->
    <div class="wrapper">
        <!-- Brand Header -->
        <div class="brand-header">
            <img src="/images/fiinway_logo.png" alt="Fiinway" class="brand-logo" onerror="this.style.display='none'">
            <span class="brand-badge">FOOD</span>
        </div>

        <!-- Hero Dish Graphic -->
        <div class="hero-art-container">
            <div class="hero-glow"></div>
            <div class="hero-icon-card">
                <span class="hero-icon">🍲</span>
            </div>
        </div>

        <!-- Pill Status -->
        <div class="pill-tag">
            <span class="pill-dot"></span>
            Launching Soon In Your City
        </div>

        <!-- Heading & Intro -->
        <h1>We're Cooking Up <span>Something Delicious!</span></h1>
        <p class="lead">
            Fiinway Food is preparing to bring top-rated local restaurants, cloud kitchens, and gourmet dining straight to your doorstep.
        </p>

        <!-- Feature Highlights -->
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-box" style="background: rgba(255, 107, 107, 0.1); color: #FF6B6B;">🍕</div>
                <div class="feature-text">
                    <h3>Curated Restaurants</h3>
                    <p>Handpicked dining spots, authentic cuisines, and best city treats.</p>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon-box" style="background: rgba(59, 130, 246, 0.1); color: #60A5FA;">⚡</div>
                <div class="feature-text">
                    <h3>Lightning-Fast Delivery</h3>
                    <p>Live order tracking with insulated packaging for hot, fresh meals.</p>
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #34D399;">🎁</div>
                <div class="feature-text">
                    <h3>Fiinway Cashbacks & Offers</h3>
                    <p>Earn instant rewards and cashbacks on every single order.</p>
                </div>
            </div>
        </div>

        <!-- Progress Tracker -->
        <div class="progress-box">
            <div class="progress-header">
                <span>Kitchen Setup & Partner Network</span>
                <span class="badge-status">85% Complete</span>
            </div>
            <div class="progress-bar-bg">
                <div class="progress-bar-fill"></div>
            </div>
        </div>
    </div>

    <!-- Bottom Footer -->
    <div class="footer-note">
        <div class="partner-link-box">
            Are you a restaurant or cafe owner?
            <a href="/food?view=portal&tab=dashboard">Partner With Us &rarr;</a>
        </div>
        <p class="copyright">&copy; {{ date('Y') }} Fiinway Technologies. All rights reserved.</p>
    </div>

</body>
</html>

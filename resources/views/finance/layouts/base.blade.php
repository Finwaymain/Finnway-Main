<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f1b2d">
    <title>@yield('title', 'Fiinway Finance')</title>
    <style>
        :root {
            --navy:   #0f1b2d;
            --navy2:  #152338;
            --slate:  #1e2f45;
            --blue:   #1a5fa8;
            --blue2:  #1976d2;
            --accent: #f5a623;
            --green:  #27ae60;
            --red:    #c0392b;
            --amber:  #e67e22;
            --white:  #ffffff;
            --gray1:  #f4f6f9;
            --gray2:  #d8dee6;
            --gray3:  #8a96a3;
            --text:   #1a2636;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: var(--gray1); color: var(--text); font-size: 14px; line-height: 1.5; }
        body { display: flex; flex-direction: column; min-height: 100vh; }

        /* Header */
        .fw-header {
            background: var(--navy);
            padding: env(safe-area-inset-top, 0px) 16px 0;
            min-height: calc(64px + env(safe-area-inset-top, 0px));
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .fw-header-brand { display: flex; align-items: center; gap: 10px; }
        .fw-header-logo { color: var(--white); font-size: 19px; font-weight: 800; letter-spacing: 0.5px; }
        .fw-header-logo span { color: var(--accent); }
        .fw-header-sub { color: #94a3b8; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.8px; margin-top: 1px; }

        /* Progress bar */
        .fw-progress { background: var(--navy2); padding: 10px 18px; border-bottom: 1px solid var(--slate); }
        .fw-progress-label { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .fw-progress-label span { color: var(--gray2); font-size: 12px; font-weight: 600; }
        .fw-progress-track { height: 5px; background: var(--slate); border-radius: 4px; overflow: hidden; }
        .fw-progress-fill { height: 100%; background: linear-gradient(90deg, #1a5fa8, #1976d2); border-radius: 4px; transition: width 0.3s ease; }

        /* Back link */
        .fw-back { display: inline-flex; align-items: center; gap: 6px; color: #475569; font-size: 13px; font-weight: 600; text-decoration: none; padding: 14px 18px 4px; transition: color 0.2s; }
        .fw-back svg { width: 16px; height: 16px; }
        .fw-back:hover { color: var(--navy); }

        /* Main content area */
        .fw-main { flex: 1; padding: 18px 16px; max-width: 480px; margin: 0 auto; width: 100%; }

        /* Cards */
        .fw-card {
            background: var(--white);
            border-radius: 14px;
            padding: 22px 20px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05);
        }
        .fw-card-title { font-size: 16px; font-weight: 700; color: var(--navy); margin-bottom: 14px; }

        /* Sections */
        .fw-section-title { font-size: 21px; font-weight: 800; color: var(--navy); margin-bottom: 6px; letter-spacing: -0.3px; }
        .fw-section-sub { font-size: 13px; color: #64748b; margin-bottom: 20px; line-height: 1.5; }

        /* Form elements */
        .fw-label { display: block; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .fw-input {
            width: 100%;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 13px 15px;
            font-size: 15px;
            font-weight: 500;
            color: var(--text);
            background: var(--white);
            outline: none;
            transition: all 0.2s ease;
        }
        .fw-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(26, 95, 168, 0.12); }
        .fw-input-group { margin-bottom: 16px; }
        select.fw-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1 1l5 5 5-5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 38px; }

        /* Buttons */
        .fw-btn {
            display: block;
            width: 100%;
            padding: 15px;
            border-radius: 11px;
            border: none;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.3px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .fw-btn:active { transform: scale(0.985); opacity: 0.92; }
        .fw-btn-primary { background: var(--blue); color: var(--white); box-shadow: 0 4px 14px rgba(26, 95, 168, 0.25); }
        .fw-btn-accent { background: var(--accent); color: var(--navy); box-shadow: 0 4px 14px rgba(245, 166, 35, 0.25); }
        .fw-btn-outline { background: transparent; color: var(--blue); border: 1.5px solid var(--blue); }
        .fw-btn-green { background: var(--green); color: var(--white); box-shadow: 0 4px 14px rgba(39, 174, 96, 0.25); }
        .fw-btn-sm { padding: 9px 16px; font-size: 13px; width: auto; display: inline-block; }

        /* Status badges */
        .fw-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .fw-badge-blue { background: #eff6ff; color: var(--blue); border: 1px solid #bfdbfe; }
        .fw-badge-green { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .fw-badge-amber { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .fw-badge-red { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .fw-badge-navy { background: var(--slate); color: var(--gray2); }

        /* Info rows */
        .fw-info-row { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid #f1f5f9; }
        .fw-info-row:last-child { border-bottom: none; }
        .fw-info-label { font-size: 13px; color: #64748b; font-weight: 500; }
        .fw-info-value { font-size: 14px; font-weight: 700; color: var(--navy); }

        /* Alert boxes */
        .fw-alert { border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; font-size: 13px; line-height: 1.5; }
        .fw-alert-info { background: #eff6ff; border-left: 4px solid var(--blue2); color: #1e40af; }
        .fw-alert-warn { background: #fffbeb; border-left: 4px solid var(--amber); color: #92400e; }
        .fw-alert-success { background: #ecfdf5; border-left: 4px solid var(--green); color: #065f46; }
        .fw-alert-error { background: #fef2f2; border-left: 4px solid var(--red); color: #991b1b; }

        /* Upload area */
        .fw-upload-box { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s; }
        .fw-upload-box:hover { border-color: var(--blue); background: #f0f7ff; }
        .fw-upload-box input[type=file] { display: none; }
        .fw-upload-box .fw-upload-icon { font-size: 30px; color: #64748b; margin-bottom: 6px; }
        .fw-upload-box p { font-size: 13px; color: #64748b; }
        .fw-upload-box strong { font-size: 14px; color: var(--navy); display: block; margin-bottom: 4px; }
        .fw-upload-done { border-color: var(--green); background: #ecfdf5; }
        .fw-upload-done p { color: var(--green); }

        /* Doc list */
        .fw-doc-item { display: flex; align-items: center; justify-content: space-between; padding: 14px; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 10px; background: var(--white); box-shadow: 0 2px 6px rgba(0,0,0,0.02); }
        .fw-doc-item-info { display: flex; align-items: center; gap: 12px; }
        .fw-doc-icon { width: 38px; height: 38px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 17px; }
        .fw-doc-name { font-size: 14px; font-weight: 700; color: var(--navy); }
        .fw-doc-status { font-size: 11px; color: #64748b; }

        /* Partner card */
        .fw-partner-card { border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 14px; background: var(--white); box-shadow: 0 2px 8px rgba(0,0,0,0.03); transition: all 0.2s ease; }
        .fw-partner-card.locked { opacity: 0.45; pointer-events: none; }
        .fw-partner-card.selected { border-color: var(--blue2); background: #f0f7ff; }
        .fw-partner-header { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .fw-partner-logo { width: 44px; height: 44px; border-radius: 8px; background: var(--slate); display: flex; align-items: center; justify-content: center; font-size: 11px; color: var(--white); font-weight: 700; }
        .fw-partner-name { font-size: 15px; font-weight: 700; color: var(--navy); }
        .fw-partner-type { font-size: 11px; color: var(--gray3); }
        .fw-partner-row { display: flex; justify-content: space-between; font-size: 12px; color: var(--gray3); margin-top: 6px; }
        .fw-partner-row span:last-child { font-weight: 600; color: var(--navy); }

        /* Tenure chips */
        .fw-tenure-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .fw-tenure-chip { border: 1.5px solid var(--gray2); border-radius: 8px; padding: 10px; text-align: center; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--navy); background: var(--white); }
        .fw-tenure-chip.active, .fw-tenure-chip:checked { border-color: var(--blue2); background: #ddeeff; color: var(--blue); }

        /* Amount display */
        .fw-amount-big { text-align: center; padding: 20px 0; }
        .fw-amount-big .amount { font-size: 36px; font-weight: 800; color: var(--navy); }
        .fw-amount-big .label { font-size: 12px; color: var(--gray3); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }

        /* Checklist */
        .fw-check-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 0; border-bottom: 1px solid var(--gray1); }
        .fw-check-item:last-child { border-bottom: none; }
        .fw-check-icon { width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; margin-top: 1px; }
        .fw-check-done { background: var(--green); color: var(--white); }
        .fw-check-pending { background: var(--gray2); color: var(--gray3); }
        .fw-check-text { font-size: 13px; color: var(--navy); }
        .fw-check-sub { font-size: 11px; color: var(--gray3); }

        /* Wallet stat box */
        .fw-wallet-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .fw-stat-box { background: var(--navy2); border-radius: 8px; padding: 12px; text-align: center; }
        .fw-stat-box .val { font-size: 18px; font-weight: 700; color: var(--white); }
        .fw-stat-box .lbl { font-size: 11px; color: var(--gray3); margin-top: 2px; }

        /* Consent checkbox */
        .fw-consent { display: flex; align-items: flex-start; gap: 10px; padding: 12px; background: var(--gray1); border-radius: 8px; margin-bottom: 14px; }
        .fw-consent input { width: 16px; height: 16px; flex-shrink: 0; margin-top: 2px; accent-color: var(--blue2); }
        .fw-consent label { font-size: 12px; color: var(--gray3); line-height: 1.5; }

        /* Footer area */
        .fw-footer-pad { height: 105px; }
        .fw-sticky-bottom {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            border-top: 1px solid #e2e8f0;
            padding: 16px 20px calc(16px + env(safe-area-inset-bottom, 0px));
            max-width: 480px;
            margin: 0 auto;
            z-index: 50;
            box-shadow: 0 -4px 20px rgba(15, 23, 42, 0.06);
        }

        /* Timer */
        .fw-timer { text-align: center; padding: 20px; }
        .fw-timer .count { font-size: 44px; font-weight: 800; color: var(--blue2); }
        .fw-timer .unit { font-size: 13px; color: var(--gray3); }

        /* Step track */
        .fw-steps { display: flex; align-items: center; margin-bottom: 20px; }
        .fw-step { flex: 1; text-align: center; }
        .fw-step-dot { width: 24px; height: 24px; border-radius: 50%; margin: 0 auto 4px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
        .fw-step-done .fw-step-dot { background: var(--blue); color: var(--white); }
        .fw-step-active .fw-step-dot { background: var(--blue2); color: var(--white); box-shadow: 0 0 0 3px rgba(25,118,210,0.2); }
        .fw-step-pending .fw-step-dot { background: var(--gray2); color: var(--gray3); }
        .fw-step-label { font-size: 9px; color: var(--gray3); }
        .fw-step-line { flex: 0.5; height: 2px; background: var(--gray2); }
        .fw-step-line.done { background: var(--blue); }

        /* QR area */
        .fw-qr-box { border: 2px solid var(--blue); border-radius: 12px; padding: 24px; text-align: center; background: var(--white); }
        .fw-qr-img { width: 160px; height: 160px; background: var(--gray2); border-radius: 8px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: var(--gray3); font-size: 12px; }

        /* Responsive: above 480px = centered card */
        @media (min-width: 480px) { .fw-main { padding: 22px 20px; } }
    </style>
    @stack('head')
</head>
<body>
    <div class="fw-header">
        <div class="fw-header-brand">
            @yield('header-left')
            <div>
                <div class="fw-header-logo">Fiin<span>way</span></div>
                <div class="fw-header-sub">@yield('header-sub', 'Finance Portal')</div>
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            @yield('header-right')
        </div>
    </div>

    @hasSection('progress')
    <div class="fw-progress">
        <div class="fw-progress-label">
            <span>@yield('progress-label', 'Step 1')</span>
            <span>@yield('progress-pct', '0')%</span>
        </div>
        <div class="fw-progress-track">
            <div class="fw-progress-fill" style="width: @yield('progress-pct', '0')%"></div>
        </div>
    </div>
    @endif

    @yield('back')

    <div class="fw-main">
        @yield('content')
        <div class="fw-footer-pad"></div>
    </div>

    @hasSection('sticky-bottom')
    <div class="fw-sticky-bottom">
        @yield('sticky-bottom')
    </div>
    @endif

    @stack('scripts')
</body>
</html>

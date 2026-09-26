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
        .fw-header { background: var(--navy); padding: 0 16px; height: 52px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .fw-header-logo { color: var(--white); font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }
        .fw-header-logo span { color: var(--accent); }
        .fw-header-sub { color: var(--gray3); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Progress bar */
        .fw-progress { background: var(--navy2); padding: 8px 16px; }
        .fw-progress-label { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
        .fw-progress-label span { color: var(--gray2); font-size: 11px; }
        .fw-progress-track { height: 4px; background: var(--slate); border-radius: 2px; overflow: hidden; }
        .fw-progress-fill { height: 100%; background: var(--blue2); border-radius: 2px; transition: width 0.3s ease; }

        /* Back link */
        .fw-back { display: inline-flex; align-items: center; gap: 6px; color: var(--gray3); font-size: 13px; text-decoration: none; padding: 12px 16px 4px; }
        .fw-back svg { width: 16px; height: 16px; }
        .fw-back:hover { color: var(--white); }

        /* Main content area */
        .fw-main { flex: 1; padding: 16px; max-width: 480px; margin: 0 auto; width: 100%; }

        /* Cards */
        .fw-card { background: var(--white); border-radius: 10px; padding: 18px; margin-bottom: 14px; border: 1px solid var(--gray2); }
        .fw-card-title { font-size: 15px; font-weight: 600; color: var(--navy); margin-bottom: 14px; }

        /* Sections */
        .fw-section-title { font-size: 20px; font-weight: 700; color: var(--navy); margin-bottom: 4px; }
        .fw-section-sub { font-size: 13px; color: var(--gray3); margin-bottom: 20px; }

        /* Form elements */
        .fw-label { display: block; font-size: 12px; font-weight: 600; color: var(--gray3); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; }
        .fw-input { width: 100%; border: 1.5px solid var(--gray2); border-radius: 7px; padding: 11px 12px; font-size: 14px; color: var(--text); background: var(--white); outline: none; transition: border-color 0.2s; }
        .fw-input:focus { border-color: var(--blue2); }
        .fw-input-group { margin-bottom: 14px; }
        select.fw-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%238a96a3' d='M1 1l5 5 5-5'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }

        /* Buttons */
        .fw-btn { display: block; width: 100%; padding: 14px; border-radius: 8px; border: none; font-size: 15px; font-weight: 600; cursor: pointer; text-align: center; text-decoration: none; transition: opacity 0.2s; }
        .fw-btn:active { opacity: 0.85; }
        .fw-btn-primary { background: var(--blue); color: var(--white); }
        .fw-btn-accent { background: var(--accent); color: var(--navy); }
        .fw-btn-outline { background: transparent; color: var(--blue); border: 1.5px solid var(--blue); }
        .fw-btn-green { background: var(--green); color: var(--white); }
        .fw-btn-sm { padding: 9px 16px; font-size: 13px; width: auto; display: inline-block; }

        /* Status badges */
        .fw-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .fw-badge-blue { background: #ddeeff; color: var(--blue); }
        .fw-badge-green { background: #d4f0e0; color: var(--green); }
        .fw-badge-amber { background: #fdebd0; color: var(--amber); }
        .fw-badge-red { background: #fde8e8; color: var(--red); }
        .fw-badge-navy { background: var(--slate); color: var(--gray2); }

        /* Info rows */
        .fw-info-row { display: flex; justify-content: space-between; align-items: center; padding: 9px 0; border-bottom: 1px solid var(--gray1); }
        .fw-info-row:last-child { border-bottom: none; }
        .fw-info-label { font-size: 12px; color: var(--gray3); }
        .fw-info-value { font-size: 14px; font-weight: 600; color: var(--navy); }

        /* Alert boxes */
        .fw-alert { border-radius: 8px; padding: 12px 14px; margin-bottom: 14px; font-size: 13px; }
        .fw-alert-info { background: #ddeeff; border-left: 3px solid var(--blue2); color: var(--blue); }
        .fw-alert-warn { background: #fdebd0; border-left: 3px solid var(--amber); color: var(--amber); }
        .fw-alert-success { background: #d4f0e0; border-left: 3px solid var(--green); color: var(--green); }
        .fw-alert-error { background: #fde8e8; border-left: 3px solid var(--red); color: var(--red); }

        /* Upload area */
        .fw-upload-box { border: 2px dashed var(--gray2); border-radius: 8px; padding: 20px; text-align: center; background: var(--gray1); cursor: pointer; }
        .fw-upload-box input[type=file] { display: none; }
        .fw-upload-box .fw-upload-icon { font-size: 28px; color: var(--gray3); margin-bottom: 6px; }
        .fw-upload-box p { font-size: 13px; color: var(--gray3); }
        .fw-upload-box strong { font-size: 14px; color: var(--navy); display: block; margin-bottom: 4px; }
        .fw-upload-done { border-color: var(--green); background: #d4f0e0; }
        .fw-upload-done p { color: var(--green); }

        /* Doc list */
        .fw-doc-item { display: flex; align-items: center; justify-content: space-between; padding: 12px; border: 1px solid var(--gray2); border-radius: 8px; margin-bottom: 10px; background: var(--white); }
        .fw-doc-item-info { display: flex; align-items: center; gap: 10px; }
        .fw-doc-icon { width: 36px; height: 36px; border-radius: 6px; background: var(--slate); display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .fw-doc-name { font-size: 13px; font-weight: 600; color: var(--navy); }
        .fw-doc-status { font-size: 11px; color: var(--gray3); }

        /* Partner card */
        .fw-partner-card { border: 1.5px solid var(--gray2); border-radius: 10px; padding: 14px; margin-bottom: 12px; background: var(--white); }
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
        .fw-footer-pad { height: 80px; }
        .fw-sticky-bottom { position: fixed; bottom: 0; left: 0; right: 0; background: var(--white); border-top: 1px solid var(--gray2); padding: 12px 16px; max-width: 480px; margin: 0 auto; z-index: 50; }

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
        @media (min-width: 480px) { .fw-main { padding: 20px; } }
    </style>
    @stack('head')
</head>
<body>
    <div class="fw-header">
        <div>
            <div class="fw-header-logo">Fiin<span>way</span></div>
            <div class="fw-header-sub">@yield('header-sub', 'Finance Portal')</div>
        </div>
        @yield('header-right')
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

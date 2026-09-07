<div class="navbar-header d-flex align-items-center">
    <!-- Mobile Hamburger Toggle Button (Always visible on mobile/tablet < 992px) -->
    <a class="nav-toggler d-flex d-lg-none align-items-center justify-content-center text-white" href="javascript:void(0)" title="Toggle Navigation" style="width: 44px; height: 44px; font-size: 24px; color: #ffffff !important; text-decoration: none; cursor: pointer; border-radius: 8px; margin-left: 6px; flex-shrink: 0;">
        <i class="mdi mdi-menu"></i>
    </a>

    <!-- Brand Logo -->
    <a class="navbar-brand d-flex align-items-center justify-content-center flex-grow-1" href="<?php echo URL::to('/'); ?>" style="padding: 0; text-decoration: none; height: 64px;">
        <span class="brand-text-full d-flex align-items-center justify-content-center" style="font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: 2px; text-transform: uppercase; font-family: 'Plus Jakarta Sans', sans-serif;">
            <i class="mdi mdi-cube-outline text-primary mr-1" style="font-size: 24px; color: #818cf8 !important;"></i>
            <span class="brand-name">FIINWAY</span>
        </span>
    </a>
</div>
<div class="navbar-collapse d-flex align-items-center justify-content-between">
    
    <!-- Left Section: Sidebar Toggler & Global Search -->
    <ul class="navbar-nav mr-auto mt-md-0 d-flex align-items-center">
        <li class="nav-item d-none d-lg-block"> 
            <a class="nav-link sidebartoggler waves-effect waves-dark" href="javascript:void(0)" style="font-size: 22px; color: #334155 !important; padding: 0 10px;">
                <i class="mdi mdi-menu"></i>
            </a> 
        </li>
        <li class="nav-item ml-2 ml-md-3 d-none d-md-block header-search-nav-item">
            <div id="header-search-wrapper" class="position-relative" style="width: 380px; max-width: 100%;">
                <i class="mdi mdi-magnify" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none; z-index: 2;"></i>
                <input type="text" id="global-header-search" class="form-control" autocomplete="off" placeholder="Search pages, menus, settings... (Ctrl + K)" style="height: 38px; padding-left: 40px !important; padding-right: 75px !important; border-radius: 20px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B; font-size: 13px; width: 100%; transition: all 0.2s ease;">
                <div class="header-search-actions d-flex align-items-center" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); z-index: 3;">
                    <span id="header-search-clear" style="display: none; cursor: pointer; color: #94a3b8; font-size: 16px; margin-right: 6px; padding: 2px;" title="Clear search"><i class="mdi mdi-close-circle"></i></span>
                    <kbd class="d-none d-lg-inline-block" style="background: #e2e8f0; color: #64748b; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; border: 1px solid #cbd5e1; box-shadow: none; font-family: inherit;">Ctrl K</kbd>
                </div>

                <!-- Suggestions Dropdown Menu -->
                <div id="header-search-results" class="header-search-dropdown shadow-lg d-none">
                    <!-- Dynamic suggestions will be rendered here -->
                </div>
            </div>
        </li>
    </ul>

    <style>
        .header-search-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            width: 420px;
            max-width: calc(100vw - 40px);
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 16px 36px -4px rgba(15, 23, 42, 0.16), 0 6px 14px -2px rgba(15, 23, 42, 0.08);
            z-index: 99999;
            max-height: 420px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .header-search-dropdown::-webkit-scrollbar {
            width: 6px;
        }
        .header-search-dropdown::-webkit-scrollbar-track {
            background: #f8fafc;
            border-radius: 6px;
        }
        .header-search-dropdown::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 6px;
        }
        .header-search-dropdown::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .search-result-item {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.15s ease;
            cursor: pointer;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #ffffff;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        .search-result-item:hover,
        .search-result-item.active {
            background-color: #f8fafc !important;
        }
        .search-result-item:hover .search-result-icon,
        .search-result-item.active .search-result-icon {
            background-color: #4f46e5 !important;
            color: #ffffff !important;
        }
        .search-result-item:hover .search-result-icon i,
        .search-result-item.active .search-result-icon i {
            color: #ffffff !important;
        }
        .search-result-item:hover .search-result-title,
        .search-result-item.active .search-result-title {
            color: #4f46e5 !important;
        }
        .search-result-item:hover .search-result-jump,
        .search-result-item.active .search-result-jump {
            color: #4f46e5 !important;
            transform: translateX(2px);
        }
        .search-result-jump {
            transition: transform 0.15s ease;
        }
        #global-header-search:focus {
            background: #ffffff !important;
            border-color: #4f46e5 !important;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12) !important;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('global-header-search');
        var searchWrapper = document.getElementById('header-search-wrapper');
        var searchResults = document.getElementById('header-search-results');
        var searchClear = document.getElementById('header-search-clear');
        if (!searchInput || !searchResults) return;

        var catalog = [];
        var activeIndex = -1;
        var currentItems = [];

        var synonyms = {
            'ride': ['all rides', 'new bookings', 'confirmed rides', 'ongoing rides', 'completed rides', 'cancelled', 'taxi', 'cab', 'trip', 'transport'],
            'cab': ['all rides', 'new bookings', 'confirmed rides', 'ongoing rides', 'completed rides', 'taxi', 'ride'],
            'taxi': ['all rides', 'new bookings', 'confirmed rides', 'ongoing rides', 'completed rides', 'cab', 'ride'],
            'car': ['vehicle types & rates', 'car models', 'brands', 'all rides'],
            'bike': ['vehicle types & rates', 'all rides'],
            'auto': ['vehicle types & rates', 'all rides'],
            'user': ['all users', 'consumers', 'business users', 'user activity log', 'sub-admin staffs'],
            'customer': ['consumers', 'all users', 'user management'],
            'driver': ['business users', 'driver transactions', 'drivers payouts', 'partner kits', 'kit orders'],
            'partner': ['business users', 'partner kits', 'kit orders'],
            'kyc': ['kyc verification', 'required documents', 'identity', 'approval', 'documents'],
            'verify': ['kyc verification', 'claims verification queue'],
            'wallet': ['user transactions', 'driver transactions', 'wallet growth engine', 'payout requests', 'drivers payouts'],
            'payout': ['payout requests', 'drivers payouts', 'wallet & financials', 'withdraw'],
            'money': ['earning management', 'wallet & financials', 'payout requests'],
            'earn': ['earning management', 'refer & earn engine'],
            'earning': ['earning management', 'refer & earn engine'],
            'service': ['service requests', 'home services catalog'],
            'home service': ['home services catalog', 'service requests'],
            'parcel': ['parcel categories', 'parcel deliveries', 'logistics live map', 'courier', 'delivery'],
            'delivery': ['parcel deliveries', 'parcel categories'],
            'zone': ['operating zones', 'geofence', 'area', 'map', 'city'],
            'geofence': ['operating zones'],
            'rate': ['vehicle types & rates'],
            'pricing': ['vehicle types & rates', 'subscription plans', 'consumer plans'],
            'tax': ['tax configuration', 'gst', 'vat', 'platform fee'],
            'gst': ['tax configuration'],
            'fee': ['tax configuration', 'commission & business models'],
            'commission': ['commission & business models', 'marketplace commission'],
            'coupon': ['discount coupons', 'promo', 'offer', 'voucher'],
            'discount': ['discount coupons'],
            'promo': ['discount coupons', 'multi-channel campaigns'],
            'banner': ['app banners', 'marketing & campaigns'],
            'notification': ['push notifications', 'pending requests & notifications'],
            'push': ['push notifications'],
            'campaign': ['multi-channel campaigns'],
            'report': ['user reports', 'driver reports', 'travel reports', 'analytics'],
            'analytics': ['user reports', 'driver reports', 'travel reports'],
            'chat': ['support live chat', 'customer care contact'],
            'support': ['support & customer care', 'support live chat', 'quick questions', 'customer care contact', 'complaints & tickets', 'sos alerts'],
            'complaint': ['complaints & tickets'],
            'ticket': ['complaints & tickets'],
            'sos': ['sos alerts', 'emergency'],
            'cms': ['cms pages', 'terms & conditions', 'privacy policy', 'onboarding screens'],
            'onboarding': ['onboarding screens'],
            'setting': ['general settings', 'system settings', 'dynamic api keys', 'app version control', 'tax configuration', 'payment gateways', 'countries', 'languages', 'currencies'],
            'api': ['dynamic api keys'],
            'log': ['audit logs', 'user activity log'],
            'audit': ['audit logs'],
            'medical': ['claims verification queue', 'manage card plans', 'active medical cards', 'medical cashback'],
            'claim': ['claims verification queue'],
            'card': ['manage card plans', 'active medical cards'],
            'kit': ['partner kits', 'kits & products', 'kit orders & tracking'],
            'market': ['marketplace orders', 'marketplace commission'],
            'store': ['marketplace orders', 'marketplace commission'],
            'shop': ['marketplace orders'],
            'subadmin': ['sub-admin staffs'],
            'staff': ['sub-admin staffs', 'dispatcher staff'],
            'profile': ['my profile'],
            'admin': ['sub-admin staffs', 'my profile', 'general settings'],
            'backup': ['database backup & restore'],
            'database': ['database backup & restore'],
            'version': ['app version control'],
            'update': ['app version control']
        };

        function escapeHtml(str) {
            return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function escapeRegExp(str) {
            return (str || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        function highlight(text, q) {
            if (!q) return escapeHtml(text);
            var reg = new RegExp('(' + escapeRegExp(q) + ')', 'gi');
            return escapeHtml(text).replace(reg, '<mark style="background: #fef08a; padding: 0 2px; border-radius: 2px; color: #0f172a; font-weight: 700;">$1</mark>');
        }

        function buildCatalog() {
            var items = [];
            var seenUrls = {};

            // 1. Scrape from #sidebarnav
            var sidebarNav = document.getElementById('sidebarnav');
            if (sidebarNav) {
                var topLis = sidebarNav.querySelectorAll(':scope > li');
                topLis.forEach(function(li) {
                    var parentA = li.querySelector(':scope > a');
                    if (!parentA) return;
                    var parentTitleEl = parentA.querySelector('.hide-menu');
                    var parentTitle = parentTitleEl ? parentTitleEl.textContent.trim() : parentA.textContent.trim();
                    var parentIconEl = parentA.querySelector('i');
                    var parentIcon = parentIconEl ? parentIconEl.className : 'mdi mdi-circle-outline';
                    var parentHref = parentA.getAttribute('href');

                    var subUl = li.querySelector(':scope > ul');
                    if (subUl) {
                        var subAs = subUl.querySelectorAll('li a');
                        subAs.forEach(function(subA) {
                            var subTitle = subA.textContent.trim();
                            var subHref = subA.getAttribute('href');
                            var subIconEl = subA.querySelector('i');
                            var subIcon = subIconEl ? subIconEl.className : parentIcon;

                            if (subHref && subHref !== '#' && !subHref.startsWith('javascript') && !seenUrls[subHref]) {
                                seenUrls[subHref] = true;
                                items.push({
                                    title: subTitle,
                                    section: parentTitle,
                                    url: subHref,
                                    icon: subIcon
                                });
                            }
                        });
                    } else if (parentHref && parentHref !== '#' && !parentHref.startsWith('javascript') && !seenUrls[parentHref]) {
                        if (parentTitle.toLowerCase() !== 'logout') {
                            seenUrls[parentHref] = true;
                            items.push({
                                title: parentTitle,
                                section: 'Navigation',
                                url: parentHref,
                                icon: parentIcon
                            });
                        }
                    }
                });
            }

            // 2. Extra header / profile items
            var extraPages = [
                { title: 'Dashboard', section: 'Overview', url: '{!! url("/dashboard") !!}', icon: 'mdi mdi-home' },
                { title: 'My Profile', section: 'Account', url: '{{ route("users.profile") }}', icon: 'ti-user' },
                { title: 'General Settings', section: 'System Settings', url: '{{ route("settings") }}', icon: 'ti-settings' },
                { title: 'Pending Requests & Notifications', section: 'Support', url: '{!! url("notification") !!}', icon: 'mdi mdi-bell-outline' },
                { title: 'Customer Care & Complaints', section: 'Support', url: '{!! url("complaints") !!}', icon: 'mdi mdi-message-text-outline' }
            ];

            extraPages.forEach(function(ep) {
                var exists = items.some(function(it) {
                    return it.url === ep.url || (it.url && ep.url && (it.url.endsWith(ep.url) || ep.url.endsWith(it.url)));
                });
                if (!exists && !seenUrls[ep.url]) {
                    seenUrls[ep.url] = true;
                    items.push(ep);
                }
            });

            return items;
        }

        catalog = buildCatalog();

        function search(q) {
            if (!catalog || catalog.length === 0) {
                catalog = buildCatalog();
            }
            q = (q || '').trim().toLowerCase();
            if (!q) {
                // Return top popular / quick link pages
                var defaultTitles = ['dashboard', 'all rides', 'consumers', 'business users', 'kyc verification', 'operating zones', 'general settings', 'tax configuration'];
                var popular = [];
                defaultTitles.forEach(function(dt) {
                    var found = catalog.find(function(c) { return c.title.toLowerCase().indexOf(dt) !== -1; });
                    if (found && !popular.includes(found)) popular.push(found);
                });
                return popular.slice(0, 8);
            }

            // Collect synonym expansions
            var expandedTerms = [q];
            for (var key in synonyms) {
                if (key.indexOf(q) !== -1 || q.indexOf(key) !== -1) {
                    synonyms[key].forEach(function(s) {
                        if (!expandedTerms.includes(s)) expandedTerms.push(s);
                    });
                }
            }

            var scored = [];
            catalog.forEach(function(item) {
                var title = item.title.toLowerCase();
                var section = item.section.toLowerCase();
                var score = 0;

                if (title === q) {
                    score = 200;
                } else if (title.startsWith(q)) {
                    score = 150;
                } else if (title.indexOf(q) !== -1) {
                    score = 100;
                } else if (section.indexOf(q) !== -1) {
                    score = 60;
                } else {
                    // Check expanded synonyms
                    for (var i = 0; i < expandedTerms.length; i++) {
                        var term = expandedTerms[i];
                        if (title.indexOf(term) !== -1) {
                            score = Math.max(score, 50);
                            break;
                        } else if (section.indexOf(term) !== -1) {
                            score = Math.max(score, 30);
                            break;
                        }
                    }
                }

                if (score > 0) {
                    scored.push({ item: item, score: score });
                }
            });

            scored.sort(function(a, b) {
                return b.score - a.score;
            });

            return scored.map(function(s) { return s.item; }).slice(0, 10);
        }

        function renderResults(q) {
            q = (q || '').trim();
            currentItems = search(q);
            activeIndex = -1;

            if (q.length > 0) {
                searchClear.style.display = 'inline-block';
            } else {
                searchClear.style.display = 'none';
            }

            if (currentItems.length === 0) {
                searchResults.innerHTML = 
                    '<div class="p-4 text-center">' +
                        '<i class="mdi mdi-file-search-outline" style="font-size: 32px; color: #94a3b8; display: block; margin-bottom: 6px;"></i>' +
                        '<div style="font-size: 13px; font-weight: 700; color: #1e293b;">No pages found</div>' +
                        '<div style="font-size: 11px; color: #64748b; margin-top: 3px;">Try searching for rides, users, kyc, settings, or tax...</div>' +
                    '</div>';
                searchResults.classList.remove('d-none');
                return;
            }

            var html = '';
            if (!q) {
                html += '<div class="px-3 pt-2 pb-1 text-uppercase" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px; color: #94a3b8;">Quick Access & Frequent Pages</div>';
            } else {
                html += '<div class="px-3 pt-2 pb-1 text-uppercase" style="font-size: 10px; font-weight: 700; letter-spacing: 0.5px; color: #94a3b8;">Matching Pages (' + currentItems.length + ')</div>';
            }

            currentItems.forEach(function(item, idx) {
                var highlightedTitle = highlight(item.title, q);
                var iconHtml = item.icon ? '<i class="' + item.icon + '" style="font-size: 16px;"></i>' : '<i class="mdi mdi-file-document-outline" style="font-size: 16px;"></i>';
                html += 
                    '<a href="' + item.url + '" class="search-result-item" data-index="' + idx + '">' +
                        '<div class="d-flex align-items-center" style="min-width: 0; gap: 12px;">' +
                            '<div class="search-result-icon d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; color: #475569; flex-shrink: 0;">' +
                                iconHtml +
                            '</div>' +
                            '<div style="min-width: 0;">' +
                                '<div class="search-result-title text-truncate" style="font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.3;">' +
                                    highlightedTitle +
                                '</div>' +
                                '<div class="search-result-crumb text-truncate" style="font-size: 11px; color: #64748b; margin-top: 1px;">' +
                                    escapeHtml(item.section) +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="d-flex align-items-center ml-2 flex-shrink-0">' +
                            '<span class="badge badge-light" style="font-size: 10px; color: #64748b; background: #f1f5f9; font-weight: 500; padding: 3px 6px; border-radius: 4px;">Open</span>' +
                            '<i class="mdi mdi-arrow-right ml-1 search-result-jump" style="font-size: 14px; color: #94a3b8;"></i>' +
                        '</div>' +
                    '</a>';
            });

            html += 
                '<div class="dropdown-footer px-3 py-2 d-flex align-items-center justify-content-between" style="background: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 11px; color: #64748b; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">' +
                    '<span><kbd style="font-size: 9px; padding: 1px 4px; background: #e2e8f0; color: #475569; border-radius: 3px;">↑</kbd> <kbd style="font-size: 9px; padding: 1px 4px; background: #e2e8f0; color: #475569; border-radius: 3px;">↓</kbd> navigate</span>' +
                    '<span><kbd style="font-size: 9px; padding: 1px 4px; background: #e2e8f0; color: #475569; border-radius: 3px;">↵</kbd> open</span>' +
                    '<span><kbd style="font-size: 9px; padding: 1px 4px; background: #e2e8f0; color: #475569; border-radius: 3px;">esc</kbd> close</span>' +
                '</div>';

            searchResults.innerHTML = html;
            searchResults.classList.remove('d-none');
        }

        function updateActiveItem() {
            var itemEls = searchResults.querySelectorAll('.search-result-item');
            itemEls.forEach(function(el, idx) {
                if (idx === activeIndex) {
                    el.classList.add('active');
                    el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    el.classList.remove('active');
                }
            });
        }

        function hideDropdown() {
            searchResults.classList.add('d-none');
            activeIndex = -1;
        }

        // Prevent mousedown inside results from blurring input prematurely
        searchResults.addEventListener('mousedown', function(e) {
            e.preventDefault();
        });

        // Click on suggestion
        searchResults.addEventListener('click', function(e) {
            var itemEl = e.target.closest('.search-result-item');
            if (itemEl) {
                var url = itemEl.getAttribute('href');
                if (url && url !== '#' && !url.startsWith('javascript')) {
                    window.location.href = url;
                }
            }
        });

        // Input events
        searchInput.addEventListener('focus', function() {
            if (catalog.length === 0) catalog = buildCatalog();
            renderResults(this.value);
        });

        searchInput.addEventListener('input', function() {
            renderResults(this.value);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (searchResults.classList.contains('d-none')) {
                if (e.key === 'ArrowDown') {
                    renderResults(this.value);
                    return;
                }
            }

            var itemEls = searchResults.querySelectorAll('.search-result-item');
            var total = itemEls.length;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (total === 0) return;
                activeIndex = (activeIndex + 1) % total;
                updateActiveItem();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (total === 0) return;
                activeIndex = (activeIndex - 1 + total) % total;
                updateActiveItem();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && activeIndex < currentItems.length) {
                    window.location.href = currentItems[activeIndex].url;
                } else if (currentItems.length > 0) {
                    window.location.href = currentItems[0].url;
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                hideDropdown();
                searchInput.blur();
            }
        });

        // Clear button
        searchClear.addEventListener('click', function(e) {
            e.stopPropagation();
            searchInput.value = '';
            searchInput.focus();
            renderResults('');
        });

        // Global Shortcut: Ctrl + K or Cmd + K
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
                renderResults(searchInput.value);
            }
        });

        // Click outside closes dropdown
        document.addEventListener('click', function(e) {
            if (!searchWrapper.contains(e.target)) {
                hideDropdown();
            }
        });
    });
    </script>


    <!-- Right Section: Notification, Chat & Admin Profile (Language Button Removed & Spacing Increased) -->
    <div class="d-flex align-items-center ml-auto">
        <ul class="navbar-nav my-lg-0 d-flex align-items-center" style="gap: 20px; margin: 0;">
            @php
                $pendingAdminRequestsCount = \App\Services\AdminNotificationService::getPendingRequestsCount();
                $complaintsCount = \App\Services\AdminNotificationService::getCounts()['complaints'] ?? 0;
            @endphp
            <!-- Notification Bell -->
            <li class="nav-item dropdown">
                <a class="nav-link text-muted waves-effect waves-dark position-relative" href="{!! url('notification') !!}" title="Pending Requests & Notifications" style="padding: 0; font-size: 20px; color: #64748B !important;">
                    <i class="mdi mdi-bell-outline"></i>
                    @if($pendingAdminRequestsCount > 0)
                        <span class="badge badge-danger position-absolute" style="top: -6px; right: -8px; font-size: 9px; min-width: 17px; height: 17px; padding: 0 4px; display: flex; align-items: center; justify-content: center; border-radius: 9px; font-weight: 700; border: 2px solid #FFFFFF; box-shadow: 0 2px 4px rgba(220,38,38,0.3);">{{ $pendingAdminRequestsCount > 99 ? '99+' : $pendingAdminRequestsCount }}</span>
                    @endif
                </a>
            </li>

            <!-- Chat / Support -->
            <li class="nav-item dropdown">
                <a class="nav-link text-muted waves-effect waves-dark position-relative" href="{!! url('complaints') !!}" title="Customer Care & Support" style="padding: 0; font-size: 20px; color: #64748B !important;">
                    <i class="mdi mdi-message-text-outline"></i>
                    @if($complaintsCount > 0)
                        <span class="badge badge-warning position-absolute" style="top: -6px; right: -8px; font-size: 9px; min-width: 17px; height: 17px; padding: 0 4px; display: flex; align-items: center; justify-content: center; border-radius: 9px; font-weight: 700; border: 2px solid #FFFFFF;">{{ $complaintsCount }}</span>
                    @endif
                </a>
            </li>

            <!-- User Profile Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark d-flex align-items-center" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding: 0; gap: 10px;">
                    <img src="{{ asset('/images/user.png') }}" alt="user" class="profile-pic" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid #E2E8F0;">
                    <div class="d-none d-lg-block text-left" style="line-height: 1.2;">
                        <div style="font-size: 13px; font-weight: 700; color: #0F172A;">Fiinway Admin</div>
                        <div style="font-size: 10px; color: #64748B; font-weight: 600;">Admin</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right scale-up" style="border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-radius: 8px;">
                    <ul class="dropdown-user" style="padding: 10px 0; margin: 0; list-style: none;">
                        <li>
                            <div class="dw-user-box" style="padding: 10px 20px; border-bottom: 1px solid #F1F5F9;">
                                <div class="u-text">
                                    <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #0F172A;">Super Admin</h4>
                                    <p class="text-muted" style="margin: 2px 0 0 0; font-size: 12px; color: #64748B;">admin@cabme.com</p>
                                </div>
                            </div>
                        </li>
                        <li role="separator" class="divider"></li>
                        <li><a href="{{ route('users.profile') }}" style="padding: 8px 20px; display: block; color: #334155; font-size: 13px; text-decoration: none;"><i class="ti-user mr-2"></i> My Profile</a></li>
                        <li><a href="{{ route('settings') }}" style="padding: 8px 20px; display: block; color: #334155; font-size: 13px; text-decoration: none;"><i class="ti-settings mr-2"></i> Settings</a></li>
                        <li role="separator" class="divider"></li>
                        <li>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="padding: 8px 20px; display: block; color: #EF4444; font-size: 13px; font-weight: 600; text-decoration: none;">
                                <i class="fa fa-power-off mr-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</div>

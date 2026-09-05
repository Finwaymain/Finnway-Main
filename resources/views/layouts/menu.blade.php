@php
    $authUser = Auth::user();
@endphp
<nav class="sidebar-nav">
    <!-- Mobile Sidebar Drawer Header -->
    <div class="mobile-sidebar-header d-flex d-lg-none align-items-center justify-content-between px-3 py-2 border-bottom" style="border-color: rgba(255,255,255,0.08) !important; margin-bottom: 8px;">
        <div class="d-flex align-items-center">
            <i class="mdi mdi-shield-account text-primary mr-2" style="font-size: 20px; color: #818cf8 !important;"></i>
            <span style="font-size: 14px; font-weight: 700; color: #ffffff; letter-spacing: 0.5px;">Navigation Menu</span>
        </div>
        <a href="javascript:void(0)" class="mobile-sidebar-close text-muted d-flex align-items-center justify-content-center" title="Close Menu" style="font-size: 20px; color: #94a3b8 !important; width: 32px; height: 32px; border-radius: 6px; background: rgba(255,255,255,0.05); text-decoration: none;">
            <i class="mdi mdi-close"></i>
        </a>
    </div>
    <ul id="sidebarnav">
        <!-- 1. Dashboard -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('dashboard') || $authUser->isSubAdmin()))
        <li>
            <a class="waves-effect waves-dark" href="{!! url('/dashboard') !!}">
                <i class="mdi mdi-home"></i>
                <span class="hide-menu">Dashboard</span>
            </a>
        </li> 
        @endif

        <!-- 1.5. Earning Management (Direct Single Page Link) -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('earnings')))
        <li>
            <a class="waves-effect waves-dark" href="{!! route('earnings.index') !!}">
                <i class="mdi mdi-cash-usd text-success"></i>
                <span class="hide-menu font-weight-bold">Earning Management</span>
            </a>
        </li>
        @endif

        <!-- 2. User Management -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('user_management') || $authUser->hasPermission('kyc_approval')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-account-multiple"></i>
                <span class="hide-menu">User Management</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                @if($authUser->isAdmin() || $authUser->hasPermission('user_management'))
                    <li><a href="{!! route('users.all') !!}">All Users</a></li>
                    <li><a href="{!! url('/users') !!}">Consumers</a></li>
                    <li><a href="{!! url('/drivers') !!}">Business Users</a></li>
                @endif

                @if($authUser->isAdmin() || $authUser->hasPermission('kyc_approval'))
                    <li><a href="{!! url('/users/kyc-verification') !!}">KYC Verification</a></li>
                @endif

                @if($authUser->isAdmin() || $authUser->hasPermission('user_management'))
                    <li><a href="{!! url('/users_shudule') !!}">User Activity Log</a></li>
                @endif

                @if($authUser->isAdmin())
                    <li><a href="{!! route('sub-admins.index') !!}">Sub-Admin Staffs</a></li>
                @endif
            </ul>
        </li>
        @endif

        <!-- 3. Business Categories -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('business_categories')))
        <li>
            <a class="waves-effect waves-dark" href="{!! url('/users_category') !!}">
                <i class="mdi mdi-briefcase"></i>
                <span class="hide-menu">Business Categories</span>
            </a>
        </li>
        @endif

        <!-- 3.5. Partner Welcome Kits -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('driver_management') || $authUser->hasPermission('system_settings')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-tshirt-crew text-info"></i>
                <span class="hide-menu font-weight-bold">Partner Kits</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! route('driver-kits.index') !!}"><i class="mdi mdi-package-variant-closed text-primary mr-1"></i> Kits & Products</a></li>
                <li><a href="{!! route('driver-kits.orders') !!}"><i class="mdi mdi-truck-delivery text-success mr-1"></i> Kit Orders & Tracking</a></li>
            </ul>
        </li>
        @endif

        <!-- 3.6. Marketplace Management -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('marketplace') || $authUser->isSubAdmin()))
        <li>
            <a class="has-arrow waves-effect waves-dark {{ request()->is('marketplace/admin*') ? 'active' : '' }}" href="#" aria-expanded="false">
                <i class="mdi mdi-shopping text-warning"></i>
                <span class="hide-menu font-weight-bold">Marketplace</span>
            </a>
            <ul aria-expanded="false" class="collapse {{ request()->is('marketplace/admin*') ? 'in' : '' }}">
                <li><a href="{!! route('admin.marketplace.orders.index') !!}"><i class="mdi mdi-clipboard-text mr-1 text-primary"></i> Marketplace Orders</a></li>
                <li><a href="{!! route('admin.marketplace.commission.index') !!}"><i class="mdi mdi-percent mr-1 text-success"></i> Marketplace Commission</a></li>
            </ul>
        </li>
        @endif

        <!-- 4. Premium Plans -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('premium_plans')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-star-circle"></i>
                <span class="hide-menu">Premium Plans</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! route('subscription-plans.index') !!}">Business Plans</a></li>
                <li><a href="{!! route('consumer-plans.index') !!}">Consumer Plans</a></li>
                <li><a href="{!! route('driver.subscriptionHistory') !!}">Subscription History</a></li>
            </ul>
        </li>
        @endif

        <!-- 4.5. Medical Cashback Module -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('medical_cashback') || $authUser->isSubAdmin()))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-medical-bag text-danger"></i>
                <span class="hide-menu font-weight-bold">Medical Cashback</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! route('admin.medical.index') !!}"><i class="mdi mdi-clipboard-check text-warning mr-1"></i> Claims Verification Queue</a></li>
                <li><a href="{!! route('admin.medical.plans.index') !!}"><i class="mdi mdi-settings text-primary mr-1"></i> Manage Card Plans</a></li>
                <li><a href="{!! route('admin.medical.cards') !!}"><i class="mdi mdi-credit-card text-success mr-1"></i> Active Medical Cards</a></li>
            </ul>
        </li>
        @endif

        <!-- 5. Wallet & Financials -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('wallet_financials')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-wallet"></i>
                <span class="hide-menu">Wallet & Financials</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('walletstransaction') !!}">User Transactions</a></li>
                <li><a href="{!! url('walletstransactions/driver') !!}">Driver Transactions</a></li>
                <li><a href="{!! route('wallet-growth.index') !!}">Wallet Growth Engine</a></li>
                <li><a href="{!! url('payoutRequest') !!}">Payout Requests</a></li>
                <li><a href="{!! url('driversPayouts') !!}">Drivers Payouts</a></li>
            </ul>
        </li>
        @endif

        <!-- 6. Referral Engine -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('referral_engine')))
        <li>
            <a class="waves-effect waves-dark" href="{!! route('referral.index') !!}">
                <i class="mdi mdi-share-variant"></i>
                <span class="hide-menu">Refer & Earn Engine</span>
            </a>
        </li>
        @endif

        <!-- 7. Services & Transport -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('services_transport')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-wrench"></i>
                <span class="hide-menu">Services & Transport</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('service-requests') !!}">Service Requests</a></li>
                <li><a href="{!! url('home-services') !!}">Home Services Catalog</a></li>
                <li><a href="{!! route('zone') !!}">Operating Zones</a></li>
                <li><a href="{!! url('vehicle/index') !!}">Vehicle Types & Rates</a></li>
                <li><a href="{!! url('brands') !!}">Vehicle Brands</a></li>
                <li><a href="{!! url('car_model') !!}">Car Models</a></li>
            </ul>
        </li>
        @endif

        <!-- 8. Rides & Logistics -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('rides_logistics')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-car"></i>
                <span class="hide-menu">Rides & Logistics</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('rides/all') !!}">All Rides</a></li>
                <li><a href="{!! url('rides/new') !!}">New Bookings</a></li>
                <li><a href="{!! url('rides/confirmed') !!}">Confirmed Rides</a></li>
                <li><a href="{!! url('rides/onRide') !!}">Ongoing Rides</a></li>
                <li><a href="{!! url('rides/completed') !!}">Completed Rides</a></li>
                <li><a href="{!! url('rides/rejected') !!}">Cancelled / Rejected</a></li>
                <li><a href="{!! url('parcel-category') !!}">Parcel Categories</a></li>
                <li><a href="{!! url('parcel/all') !!}">Parcel Deliveries</a></li>
                <li><a href="{!! url('parcel/map') !!}">Logistics Live Map</a></li>
            </ul>
        </li>
        @endif

        <!-- 9. Marketing & Campaigns -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('marketing_campaigns')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-bullhorn"></i>
                <span class="hide-menu">Marketing & Campaigns</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! route('campaigns.index') !!}">Multi-Channel Campaigns</a></li>
                <li><a href="{!! url('coupons') !!}">Discount Coupons</a></li>
                <li><a href="{!! url('banners') !!}">App Banners</a></li>
                <li><a href="{!! url('notification') !!}">Push Notifications</a></li>
            </ul>
        </li>
        @endif

        <!-- 10. Reports & Analytics (Default Access for Sub-Admin) -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('report_download')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-chart-bar"></i>
                <span class="hide-menu">Reports & Analytics</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('reports/userreport') !!}">User Reports</a></li>
                <li><a href="{!! url('reports/driverreport') !!}">Driver Reports</a></li>
                <li><a href="{!! url('reports/travelreport') !!}">Travel Reports</a></li>
            </ul>
        </li>
        @endif

        <!-- 11. Support & CMS (Default Access for Sub-Admin: Reply Customer MSG) -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('reply_customer_msg')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-headphones"></i>
                <span class="hide-menu">Support & Customer Care</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! route('support.chat.index') !!}"><i class="mdi mdi-forum"></i> Support Live Chat</a></li>
                <li><a href="{!! route('support.questions.index') !!}"><i class="mdi mdi-help-circle-outline"></i> Quick Questions</a></li>
                <li><a href="{!! route('customer-care.index') !!}"><i class="mdi mdi-phone-in-talk"></i> Customer Care Contact</a></li>
                <li><a href="{!! url('complaints') !!}"><i class="mdi mdi-message-text-outline"></i> Complaints & Tickets</a></li>
                <li><a href="{!! url('sos') !!}">SOS Alerts</a></li>
                @if($authUser->isAdmin() || $authUser->hasPermission('system_settings'))
                    <li><a href="{!! url('cms') !!}">CMS Pages</a></li>
                    <li><a href="{!! url('on-boarding') !!}">Onboarding Screens</a></li>
                    <li><a href="{!! url('dispatcher-users') !!}">Dispatcher Staff</a></li>
                @endif
            </ul>
        </li>
        @endif

        <!-- 12. System Settings -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('system_settings')))
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-settings"></i>
                <span class="hide-menu">System Settings</span>
            </a>
            <ul aria-expanded="false" class="collapse" style="max-height: 250px; overflow-y: auto;">
                <li><a href="{!! route('app-version-control.index') !!}"><i class="mdi mdi-cellphone-arrow-down"></i> App Version Control</a></li>
                <li><a href="{!! url('administration_tools/settings') !!}">General Settings</a></li>
                <li><a href="{!! route('api-keys.index') !!}">Dynamic API Keys</a></li>
                <li><a href="{!! url('administration_tools/tax') !!}">Tax Configuration</a></li>
                <li><a href="{!! url('administration_tools/commission') !!}">Commission & Business Models</a></li>
                <li><a href="{!! url('settings/payment/stripe') !!}">Payment Gateways</a></li>
                <li><a href="{!! url('administration_tools/driver_document') !!}">Required Documents</a></li>
                <li><a href="{!! url('administration_tools/country') !!}">Countries</a></li>
                <li><a href="{!! url('language') !!}">Languages</a></li>
                <li><a href="{!! url('administration_tools/currency') !!}">Currencies</a></li>
                <li><a href="{!! url('administration_tools/email_template') !!}">Email Templates</a></li>
                <li><a href="{!! route('database-backup.index') !!}"><i class="mdi mdi-database"></i> Database Backup & Restore</a></li>
                <li><a href="{!! url('administration_tools/terms_condition') !!}">Terms & Conditions</a></li>
                <li><a href="{!! url('administration_tools/privacy_policy') !!}">Privacy Policy</a></li>
            </ul>
        </li>
        @endif

        <!-- 13. Audit Logs -->
        @if($authUser && ($authUser->isAdmin() || $authUser->hasPermission('audit_logs')))
        <li>
            <a class="waves-effect waves-dark" href="{!! url('logs') !!}">
                <i class="mdi mdi-receipt"></i>
                <span class="hide-menu">Audit Logs</span>
            </a>
        </li>
        @endif

        <!-- 14. Logout -->
        <li>
            <a class="waves-effect waves-dark" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-power-off text-danger"></i>
                <span class="hide-menu text-danger">Logout</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</nav>
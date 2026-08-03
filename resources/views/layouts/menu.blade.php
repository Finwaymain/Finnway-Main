<nav class="sidebar-nav">
    <ul id="sidebarnav">
        <!-- 1. Dashboard -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('/dashboard') !!}">
                <i class="mdi mdi-home"></i>
                <span class="hide-menu">Dashboard</span>
            </a>
        </li> 

        <!-- 2. User Management -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-account-multiple"></i>
                <span class="hide-menu">User Management</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! route('users.all') !!}">All Users</a></li>
                <li><a href="{!! url('/users') !!}">Consumers</a></li>
                <li><a href="{!! url('/drivers') !!}">Business Users</a></li>
                <li><a href="{!! url('/users/kyc-verification') !!}">KYC Verification</a></li>
                <li><a href="{!! url('/users_shudule') !!}">User Activity Log</a></li>
            </ul>
        </li>

        <!-- 3. Business Categories -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('/users_category') !!}">
                <i class="mdi mdi-briefcase"></i>
                <span class="hide-menu">Business Categories</span>
            </a>
        </li>

        <!-- 4. Premium Plans -->
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

        <!-- 5. Wallet & Financials -->
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

        <!-- 6. Referral Engine -->
        <li>
            <a class="waves-effect waves-dark" href="{!! route('referral.index') !!}">
                <i class="mdi mdi-share-variant"></i>
                <span class="hide-menu">Refer & Earn Engine</span>
            </a>
        </li>

        <!-- 7. Services & Transport -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-wrench"></i>
                <span class="hide-menu">Services & Transport</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('service-requests') !!}">Service Requests</a></li>
                <li><a href="{!! url('home-services') !!}">Home Services Catalog</a></li>
                <li><a href="{!! url('vehicle/index') !!}">Vehicle Types</a></li>
                <li><a href="{!! url('brands') !!}">Vehicle Brands</a></li>
                <li><a href="{!! url('car_model') !!}">Car Models</a></li>
            </ul>
        </li>

        <!-- 8. Rides & Logistics -->
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

        <!-- 9. Marketing & Campaigns -->
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

        <!-- 10. Reports & Analytics -->
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

        <!-- 11. Support & CMS -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-headphones"></i>
                <span class="hide-menu">Support & CMS</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('complaints') !!}">Complaints & Tickets</a></li>
                <li><a href="{!! url('sos') !!}">SOS Alerts</a></li>
                <li><a href="{!! url('cms') !!}">CMS Pages</a></li>
                <li><a href="{!! url('on-boarding') !!}">Onboarding Screens</a></li>
                <li><a href="{!! url('dispatcher-users') !!}">Dispatcher Staff</a></li>
            </ul>
        </li>

        <!-- 12. System Settings -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-settings"></i>
                <span class="hide-menu">System Settings</span>
            </a>
            <ul aria-expanded="false" class="collapse" style="max-height: 250px; overflow-y: auto;">
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
                <li><a href="{!! url('administration_tools/terms_condition') !!}">Terms & Conditions</a></li>
                <li><a href="{!! url('administration_tools/privacy_policy') !!}">Privacy Policy</a></li>
            </ul>
        </li>

        <!-- 13. Audit Logs -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('logs') !!}">
                <i class="mdi mdi-receipt"></i>
                <span class="hide-menu">Audit Logs</span>
            </a>
        </li>

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
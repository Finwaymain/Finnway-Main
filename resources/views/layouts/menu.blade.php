<nav class="sidebar-nav">
    <ul id="sidebarnav">
        <!-- Dashboard -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('/dashboard') !!}">
                <i class="mdi mdi-home"></i>
                <span class="hide-menu">Dashboard</span>
            </a>
        </li> 

        <!-- User Management -->
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
                <li><a href="{!! url('/users_shudule') !!}">User Activity</a></li>
                <li><a href="#">QR Management</a></li>
            </ul>
        </li>

        <!-- Business Management -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('/users_category') !!}">
                <i class="mdi mdi-briefcase"></i>
                <span class="hide-menu">Business Management</span>
            </a>
        </li>

        <!-- Premium Plans -->
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

        <!-- Wallet & Transactions -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-wallet"></i>
                <span class="hide-menu">Wallet & Transactions</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('walletstransaction') !!}">User Transactions</a></li>
                <li><a href="{!! url('walletstransactions/driver') !!}">Driver Transactions</a></li>
                <li><a href="{!! url('payoutRequest') !!}">Payout Requests</a></li>
                <li><a href="{!! url('driversPayouts') !!}">Drivers Payouts</a></li>
            </ul>
        </li>

        <!-- Cashback Management -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('/users_shudule') !!}">
                <i class="mdi mdi-cash-multiple"></i>
                <span class="hide-menu">Cashback Management</span>
            </a>
        </li>

        <!-- Referral & Earnings -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('/users_shudule') !!}">
                <i class="mdi mdi-share-variant"></i>
                <span class="hide-menu">Referral & Earnings</span>
            </a>
        </li>

        <!-- Services Management -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('service-requests') !!}">
                <i class="mdi mdi-wrench"></i>
                <span class="hide-menu">Services Management</span>
            </a>
        </li>

        <!-- Transport Management -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-car"></i>
                <span class="hide-menu">Transport Management</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('vehicle/index') !!}">Vehicle Types</a></li>
                <li><a href="{!! url('brands') !!}">Brands</a></li>
                <li><a href="{!! url('car_model') !!}">Car Models</a></li>
            </ul>
        </li>

        <!-- Home Services -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('home-services') !!}">
                <i class="mdi mdi-home-outline"></i>
                <span class="hide-menu">Home Services</span>
            </a>
        </li>

        <!-- Marketplace -->
        <li>
            <a class="waves-effect waves-dark" href="#">
                <i class="mdi mdi-shopping"></i>
                <span class="hide-menu">Marketplace</span>
            </a>
        </li>

        <!-- Healthcare (Cards) -->
        <li>
            <a class="waves-effect waves-dark" href="#">
                <i class="mdi mdi-heart-pulse"></i>
                <span class="hide-menu">Healthcare (Cards)</span>
            </a>
        </li>

        <!-- Finance & Loans -->
        <li>
            <a class="waves-effect waves-dark" href="#">
                <i class="mdi mdi-bank"></i>
                <span class="hide-menu">Finance & Loans</span>
            </a>
        </li>

        <!-- Subscriptions -->
        <li>
            <a class="waves-effect waves-dark" href="#">
                <i class="mdi mdi-rss"></i>
                <span class="hide-menu">Subscriptions</span>
            </a>
        </li>

        <!-- Order & Booking -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-calendar-clock"></i>
                <span class="hide-menu">Order & Booking</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('rides/all') !!}">All Rides</a></li>
                <li><a href="{!! url('rides/new') !!}">New Rides</a></li>
                <li><a href="{!! url('rides/confirmed') !!}">Confirmed Rides</a></li>
                <li><a href="{!! url('rides/onRide') !!}">On Ride</a></li>
                <li><a href="{!! url('rides/completed') !!}">Completed</a></li>
                <li><a href="{!! url('rides/rejected') !!}">Cancelled & Rejected</a></li>
            </ul>
        </li>

        <!-- Delivery & Logistics -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-package"></i>
                <span class="hide-menu">Delivery & Logistics</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('parcel/map') !!}">Map View</a></li>
                <li><a href="{!! url('parcel-category') !!}">Parcel Categories</a></li>
                <li><a href="{!! url('parcel/all') !!}">Parcel Orders</a></li>
            </ul>
        </li>

        <!-- Marketing & Campaigns -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-bullhorn"></i>
                <span class="hide-menu">Marketing & Campaigns</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('coupons') !!}">Coupons</a></li>
                <li><a href="{!! url('banners') !!}">Banners</a></li>
            </ul>
        </li>

        <!-- Notifications -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('notification') !!}">
                <i class="mdi mdi-bell-ring"></i>
                <span class="hide-menu">Notifications</span>
            </a>
        </li>

        <!-- Reports & Analytics -->
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

        <!-- Employee Management -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-worker"></i>
                <span class="hide-menu">Employee Management</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('dispatcher-users') !!}">Dispatcher Users</a></li>
            </ul>
        </li>

        <!-- Support & Complaints -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="fa fa-list-alt"></i>
                <span class="hide-menu">Support & Complaints</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('complaints') !!}">Complaints</a></li>
                <li><a href="{!! url('sos') !!}">SOS</a></li>
            </ul>
        </li>

        <!-- CMS & Settings -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-monitor-multiple"></i>
                <span class="hide-menu">CMS & Settings</span>
            </a>
            <ul aria-expanded="false" class="collapse">
                <li><a href="{!! url('cms') !!}">CMS Pages</a></li>
                <li><a href="{!! url('on-boarding') !!}">On Boarding</a></li>
            </ul>
        </li>

        <!-- System Settings -->
        <li>
            <a class="has-arrow waves-effect waves-dark" href="#" aria-expanded="false">
                <i class="mdi mdi-settings"></i>
                <span class="hide-menu">System Settings</span>
            </a>
            <ul aria-expanded="false" class="collapse" style="max-height: 250px; overflow-y: auto;">
                <li><a href="{!! url('administration_tools/settings') !!}">Settings</a></li>
                <li><a href="{!! url('administration_tools/tax') !!}">Tax</a></li>
                <li><a href="{!! url('administration_tools/commission') !!}">Business Models</a></li>
                <li><a href="{!! url('settings/payment/stripe') !!}">Payment Methods</a></li>
                <li><a href="{!! url('administration_tools/driver_document') !!}">Driver Documents</a></li>
                <li><a href="{!! url('administration_tools/country') !!}">Countries</a></li>
                <li><a href="{!! url('language') !!}">Languages</a></li>
                <li><a href="{!! url('administration_tools/currency') !!}">Currencies</a></li>
                <li><a href="{!! url('administration_tools/email_template') !!}">Email Templates</a></li>
                <li><a href="{!! url('administration_tools/homepageTemplate') !!}">Homepage Templates</a></li>
                <li><a href="{!! url('administration_tools/terms_condition') !!}">Terms & Conditions</a></li>
                <li><a href="{!! url('administration_tools/privacy_policy') !!}">Privacy Policy</a></li>
            </ul>
        </li>

        <!-- Audit Logs -->
        <li>
            <a class="waves-effect waves-dark" href="{!! url('logs') !!}">
                <i class="mdi mdi-receipt"></i>
                <span class="hide-menu">Audit Logs</span>
            </a>
        </li>

        <!-- Logout -->
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
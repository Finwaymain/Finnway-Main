<div class="navbar-header d-flex align-items-center justify-content-center">
    <a class="navbar-brand d-flex align-items-center justify-content-center" href="<?php echo URL::to('/'); ?>" style="padding: 0; text-decoration: none; height: 64px; width: 100%;">
        <span class="brand-text-full d-flex align-items-center justify-content-center" style="font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: 2px; text-transform: uppercase; font-family: 'Plus Jakarta Sans', sans-serif;">
            <i class="mdi mdi-cube-outline text-primary mr-1" style="font-size: 24px; color: #818cf8 !important;"></i>
            <span class="brand-name">FIINWAY</span>
        </span>
    </a>
</div>
<div class="navbar-collapse d-flex align-items-center justify-content-between">
    
    <!-- Left Section: Sidebar Toggler & Global Search -->
    <ul class="navbar-nav mr-auto mt-md-0 d-flex align-items-center">
        <li class="nav-item"> 
            <a class="nav-link sidebartoggler waves-effect waves-dark" href="javascript:void(0)" style="font-size: 22px; color: #334155 !important; padding: 0 10px;">
                <i class="mdi mdi-menu"></i>
            </a> 
        </li>
        <li class="nav-item ml-3 d-none d-md-block">
            <div class="position-relative" style="width: 360px;">
                <i class="mdi mdi-magnify" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 18px; pointer-events: none; z-index: 2;"></i>
                <input type="text" id="global-header-search" class="form-control" placeholder="Search by User, Mobile, Order ID, Booking ID..." style="height: 38px; padding-left: 42px !important; padding-right: 15px; border-radius: 20px; border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B; font-size: 13px;">
            </div>
        </li>
    </ul>

    <!-- Right Section: Notification, Chat & Admin Profile (Language Button Removed & Spacing Increased) -->
    <div class="d-flex align-items-center">
        <ul class="navbar-nav my-lg-0 d-flex align-items-center" style="gap: 24px; margin: 0;">
            <!-- Notification Bell -->
            <li class="nav-item dropdown">
                <a class="nav-link text-muted waves-effect waves-dark position-relative" href="{!! url('notification') !!}" style="padding: 0; font-size: 20px; color: #64748B !important;">
                    <i class="mdi mdi-bell-outline"></i>
                    <span class="badge badge-danger position-absolute" style="top: -4px; right: -4px; font-size: 8px; width: 14px; height: 14px; display: flex; align-items: center; justify-content: center; border-radius: 50%; padding: 0;">5</span>
                </a>
            </li>

            <!-- Chat / Support -->
            <li class="nav-item dropdown">
                <a class="nav-link text-muted waves-effect waves-dark" href="{!! url('complaints') !!}" style="padding: 0; font-size: 20px; color: #64748B !important;">
                    <i class="mdi mdi-message-text-outline"></i>
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

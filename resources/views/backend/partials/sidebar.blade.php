@php
    $setting = \App\Models\SystemSetting::first();
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="" class="app-brand-link">
            <a href="#">
                @if ($setting && $setting->logo)
                    <img src="{{ asset($setting->logo) }}" style="height: 95px;width: 176px;" alt="Logo">
                @else
                    <img src="{{ asset('system/logo/demo.png') }}" style="height: 95px;width: 176px;"
                        alt="Default Logo">
                @endif
            </a>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">

        <li class="menu-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <a class="menu-link" href="{{route('dashboard')}}">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        {{-- ..................................................... --}}

        <!-- Trips -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Trips</span></li>
        <!-- Layouts -->

          <li class="menu-header small text-uppercase"><span class="menu-header-text">Booking</span></li>
          <!-- CMS -->
          <li class="menu-item">
              <a href="javascript:void(0);" class="menu-link menu-toggle">
                  <i class="menu-icon tf-icons bx bx-cog"></i>
                  <div data-i18n="Layouts">Booking</div>
              </a>

              <ul class="menu-sub">
                  <li class="menu-item">
                      <a class="menu-link" href="">All Booking</a>
                  </li>
                  <li class="menu-item">
                    <a class="menu-link" href="">All Payment</a>
                </li>
              </ul>
          </li>



        {{-- ..................................................... --}}

        <li class="menu-header small text-uppercase"><span class="menu-header-text">CMS</span></li>
        <!-- CMS -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Layouts">CMS</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item">
                    <a class="menu-link" href="{{route('cms.index')}}">Landing Page</a>
                </li>
            </ul>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a class="menu-link" href="{{route('features.index')}}">Features</a>
                </li>
            </ul>

            {{-- Review --}}
            <ul class="menu-sub">
                <li class="menu-item">
                    <a class="menu-link" href="{{route('review.index')}}">Reviews</a>
                </li>
            </ul>

            {{-- Brand Logo --}}
            <ul class="menu-sub">
                <li class="menu-item">
                    <a class="menu-link" href="{{route('brandlogo.index')}}">Brand Logos</a>
                </li>
            </ul>

            {{-- Plan Packages --}}
            <ul class="menu-sub">
                <li class="menu-item">
                    <a class="menu-link" href="{{route('planpackage.index')}}">Plan Packages</a>
                </li>
            </ul>
        </li>


        {{-- ..................................................... --}}

        <!-- Blogs -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Blogs</span></li>
        <!-- Layouts -->
        <li
            class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bxl-blogger"></i>
                <div data-i18n="Layouts">Blog</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item"><a class="menu-link"
                        href=""> Categories</a></li>
                <li class="menu-item"><a class="menu-link"
                        href=""> Tags</a></li>
                <li class="menu-item"><a class="menu-link"
                        href="">Blogs</a></li>
            </ul>
        </li>


        <!-- FAQ-->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">FAQ</span></li>

        <li class="menu-item">
            <a href="" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-badge-check'></i>
                <div data-i18n="Layouts">FAQ</div>
            </a>
        </li>

        <!-- User-->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">User</span></li>

        <li class="menu-item">
            <a href="" class="menu-link">
                <i class='menu-icon tf-icons bx bxs-user'></i>
                <div data-i18n="Layouts">User</div>
            </a>
        </li>

        {{-- ..................................................... --}}



        <!-- Settings -->
        <li class="menu-header small text-uppercase"><span class="menu-header-text">Settings</span></li>
        <!-- Layouts -->
        <li
            class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Layouts">Settings</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item"><a
                        class="menu-link" href="{{route('system.setting')}}">System Settings</a></li>

                <li class="menu-item"><a
                        class="menu-link" href="{{route('system.mail.index')}}">Mail Setting</a></li>
                {{-- <li class="menu-item {{ Request::routeIs('admin.social-light-page*') ? 'active' : '' }}"><a class="menu-link"
                        href="{{ route('admin.social-light-page') }}">Social Light</a></li> --}}

                {{-- <li class="menu-item {{ Request::routeIs('admin.dynamic_page.*') ? 'active' : '' }}"><a
                        class="menu-link" href="{{ route('admin.dynamic_page.index') }}">Add Dynamic Page</a></li> --}}

                <li class="menu-item"><a class="menu-link"
                        href="{{route('stripe.index')}}">Stripe</a></li>
                <li class="menu-item"><a class="menu-link"
                            href="{{route('paypal.index')}}">Paypal</a></li>
            </ul>
        </li>

        {{-- ..................................................... --}}

        {{-- prifile seatting --}}
        <li class="menu-item">
            <a class="menu-link" href="{{route('profilesetting')}}">
                <i class="menu-icon tf-icons bx bxs-user-account"></i>
                <div data-i18n="Support">Profile Setting</div>
            </a>
        </li>


    </ul>
</aside>

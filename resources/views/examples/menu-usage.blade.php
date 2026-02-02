{{-- Example Blade template showing new menu system usage --}}

{{-- Basic menu rendering --}}
@menu('sidebar')

{{-- Conditional menu rendering --}}
@menuHas('admin_navigation')
    <div class="admin-menu-wrapper">
        @menu('admin_navigation')
    </div>
@endMenuHas

@menuMissing('user_menu')
    <div class="no-menu-message">
        <p>No user menu available</p>
    </div>
@endMenuMissing

{{-- Example with conditional logic --}}
@if(auth()->check())
    @menuHas('user_navigation')
        <nav class="user-nav">
            @menu('user_navigation')
        </nav>
    @endMenuHas
@endif

{{-- Multiple menus in sidebar --}}
<aside class="sidebar">
    @menu('main_navigation')
    
    @menuHas('admin_menu')
        <hr>
        <h3>Admin</h3>
        @menu('admin_menu')
    @endMenuHas
    
    @menuHas('footer_menu')
        <hr>
        <footer>
            @menu('footer_menu')
        </footer>
    @endMenuHas
</aside>

{{-- Advanced usage with caching --}}
@if(isset($menuCacheKey))
    @php
        $menuHtml = Cache::remember($menuCacheKey, 3600, function() {
            return app(\App\Support\MenuService::class)->render('dynamic_menu');
        });
    @endphp
    {!! $menuHtml !!}
@else
    @menu('dynamic_menu')
@endif

{{-- Conditional rendering based on user role --}}
@if(auth()->user()?->isAdmin())
    @menu('admin_navigation')
@else
    @menu('user_navigation')
@endif
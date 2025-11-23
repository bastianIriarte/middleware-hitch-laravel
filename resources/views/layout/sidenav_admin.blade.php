<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-menu">
        <a href="{{ route('dashboard') }}" class="menu-item @if ($sidenav == 'dashboard') active @endif">
            <i class="fa fa-tachometer"></i>
            <span>Dashboard</span>
        </a>
        <!-- Dropdown fijo de Recursos -->
        <div class="" style="display: block !important;">
            <a class="menu-item text-decoration-none d-flex justify-content-between align-items-center @if (Str::startsWith($sidenav, 'resource_')) active @endif"
                data-bs-toggle="collapse" href="#resourcesCollapse" role="button"
                aria-expanded="{{ Str::startsWith($sidenav, 'resource_') ? 'true' : 'false' }}"
                aria-controls="resourcesCollapse">
                <div>
                    <i class="fa fa-folder"></i>
                    <span>Recursos</span>
                </div>
                <i class="fa fa-caret-down"></i>
            </a>
            @if (!empty($sidenavResources))
                <div class="collapse @if (Str::startsWith($sidenav, 'resource_')) show @endif ml-3" id="resourcesCollapse">
                    @foreach ($sidenavResources as $item)
                        <a href="{{ route('integrations', ['slug' => $item->slug]) }}"
                            class="menu-item @if ($sidenav == 'resource_' . $item->slug) active @endif">
                            <i class="fa fa-circle-o" style="font-size:10px !important;"></i>
                            <span>{{ $item->name }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
        <a href="{{ route('users') }}" class="menu-item @if ($sidenav == 'users') active @endif">
            <i class="fa fa-users"></i>
            <span>Usuarios</span>
        </a>

        <a href="{{ route('api-connections') }}" class="menu-item @if ($sidenav == 'api_connections') active @endif">
            <i class="fa fa-plug"></i>
            <span>Conexiones</span>
        </a>

        <a href="{{ route('file-management.index') }}" class="menu-item @if ($sidenav == 'file_management') active @endif">
            <i class="fa fa-files-o"></i>
            <span>Gestión de Archivos</span>
        </a>

        <a href="{{ route('watts-extraction.index') }}" class="menu-item @if ($sidenav == 'watts_extraction') active @endif">
            <i class="fa fa-download"></i>
            <span>Extracciones Watts</span>
        </a>
    </div>
</div>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

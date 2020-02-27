<nav class="sidebar-nav">
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="fa fa-fw fa-home"></i> Dashboard
            </a>
        </li>

        <li class="nav-title">MANAJEMEN PRODUK</li>
        <li class="nav-item nav-dropdown">
            <a class="nav-link nav-dropdown-toggle" href="#">
                <i class="nav-icon icon-settings"></i> All Product
            </a>
            <ul class="nav-dropdown-items">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('product.index') }}">
                        <i class="fa fa-fw fa-server"></i> Produk
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('category.index') }}">
                        <i class="fa fa-fw fa-database"></i> Kategori
                    </a>
                </li>
            </ul>
        </li>
        <li class="nav-title">MANAJEMEN ORDER</li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('orders.index') }}">
                <i class="fa fa-fw fa-database"></i> Pesanan
            </a>
        </li>

        <li class="nav-item nav-dropdown">
            <a class="nav-link nav-dropdown-toggle" href="#">
                <i class="nav-icon icon-settings"></i> Pengaturan
            </a>
            <ul class="nav-dropdown-items">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fa fa-fw fa-store"></i> Toko
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>

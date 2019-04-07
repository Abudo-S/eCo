<?php
    $name = Route::currentRouteName();
    switch ($name) {
        case 'welcome':
            $home = true;
            break;
        case 'products.index':
            $shop = true;
            break;
        case 'cart.index':
            $cart = true;
            break;
        default:
            // code
            break;
    }
?>

<div class="mainmenu-area">
    <div class="container">
        <div class="row">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div> 
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav">
                    <li class="<?php if(isset($home)) echo 'active' ?>"><a href="{{ url('/') }}">Home</a></li>
                    <li class="<?php if(isset($shop)) echo 'active' ?>"><a href="{{ url('/products') }}">Shop</a></li>
                    <!-- <li class="<?php if(isset($cart)) echo 'active' ?>"><a href="{{ url('/cart') }}">Cart</a></li> -->
                    @if(Auth::guard('admin')->check())
                        @if (Auth::guard('admin')->user()->role == 'admin')
                            <li><a href="{{url('reports')}}">REPORTS</a></li>
                        @else
                            <li><a href="#">MY PRODUCTS</a></li>
                        @endif
                        @else
                            <li><a href="#">Checkout</a></li>
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Contact</a></li>
                    @endif    
                </ul>
                {!! Form::open(['action' => 'ProductsController@search','method'=>'POST','style'=>'padding-top:15px;margin-right=-15px; float:right;']) !!}
                        {{Form::text('text', '', ['class' => 'form-control', 'placeholder' => 'Search for products...'])}}
                        {{Form::submit('Search',['style'=>'display:none'])}}
                    {!! Form::close() !!}
                    
            </div>             
        </div>
    </div>    
</div> <!-- End mainmenu area -->
{{-- Dynamic Navigation based on user role --}}
@if(request()->routeIs('admin.*'))
    {{-- Admin pages don't need nav here - handled in admin layout --}}
@else
    {{-- Client Navigation --}}
    @include('partials.client-nav')
@endif
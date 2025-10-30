
{{-- Dynamic Navigation based on user role --}}
@if(request()->routeIs('admin.*'))
    {{-- Admin pages don't need nav here - handled in admin layout --}}
@else
    {{-- Client Navigation --}}
    @include('partials.client-nav')
@endif
 <nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    </div>
</nav> 

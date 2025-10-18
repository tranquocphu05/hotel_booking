@extends('layouts.client')

@section('client_content')
<!-- BREADCRUMB -->
<div class="bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-semibold text-center text-gray-800 mb-2">Contact Us</h2>
        <p class="text-center text-gray-500 text-sm">
            <a href="{{ url('/') }}" class="hover:text-yellow-600">Home</a> /
            <span class="text-gray-800">Contact</span>
        </p>
    </div>
</div>

<!-- CONTACT SECTION -->
<section class="max-w-7xl mx-auto px-4 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

        <!-- Contact Info -->
        <div>
            <h3 class="text-2xl font-semibold text-gray-800 mb-4">Contact Info</h3>
            <p class="text-gray-600 mb-6">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                labore et dolore magna aliqua.
            </p>

            <table class="text-gray-700 text-sm w-full">
                <tbody>
                    <tr class="border-b border-gray-200">
                        <td class="font-semibold py-2 w-24">Address:</td>
                        <td>856 Cordia Extension Apt. 356, Lake, US</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="font-semibold py-2">Phone:</td>
                        <td>(12) 345 67890</td>
                    </tr>
                    <tr class="border-b border-gray-200">
                        <td class="font-semibold py-2">Email:</td>
                        <td>info.colorlib@gmail.com</td>
                    </tr>
                    <tr>
                        <td class="font-semibold py-2">Fax:</td>
                        <td>+(12) 345 67890</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Contact Form -->
        <div>
            <form action="#" method="POST" class="bg-white p-8 shadow-md rounded-lg space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <input type="text" name="name" placeholder="Your Name"
                        class="border border-gray-300 rounded px-4 py-3 w-full focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                    <input type="email" name="email" placeholder="Your Email"
                        class="border border-gray-300 rounded px-4 py-3 w-full focus:ring-2 focus:ring-yellow-500 focus:outline-none">
                </div>
                <textarea name="message" rows="5" placeholder="Your Message"
                    class="border border-gray-300 rounded px-4 py-3 w-full focus:ring-2 focus:ring-yellow-500 focus:outline-none"></textarea>
                <button type="submit"
                    class="bg-yellow-600 text-white font-semibold px-6 py-3 rounded hover:bg-yellow-700 transition">
                    Submit Now
                </button>
            </form>
        </div>

    </div>

    <!-- Google Map -->
    <div class="mt-16">
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.0606825994123!2d-72.8735845851828!3d40.760690042573295!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89e85b24c9274c91%3A0xf310d41b791bcb71!2sWilliam%20Floyd%20Pkwy%2C%20Mastic%20Beach%2C%20NY%2C%20USA!5e0!3m2!1sen!2sbd!4v1578582744646!5m2!1sen!2sbd"
            class="w-full h-[450px] rounded-lg border-0" allowfullscreen="" loading="lazy"></iframe>
    </div>
</section>
@endsection

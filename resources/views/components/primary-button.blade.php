<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 
    /* ĐÃ XÓA/GIỮ NGUYÊN - Loại bỏ bg-gray-800, hover:bg-gray-700, v.v. */
    border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest 
    focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
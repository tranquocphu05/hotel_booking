import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // Giữ nguyên cấu hình font sans (Figtree)
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                
                // THÊM CẤU HÌNH FONT SERIF (Lý do lỗi dấu)
                // Đặt 'Times New Roman' lên đầu để ưu tiên font hỗ trợ tiếng Việt tốt nhất
                serif: [
                    'Cambria', 
                    '"Times New Roman"', 
                    'Times', 
                    'Georgia', 
                    ...defaultTheme.fontFamily.serif
                ],
            },
        },
    },

    plugins: [forms],
};
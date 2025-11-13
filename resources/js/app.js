import './bootstrap';
import './lazyload';
// import './sw-register'; // Tạm disable Service Worker

import Alpine from 'alpinejs';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

document.addEventListener('DOMContentLoaded', () => {
    const el = document.querySelector('#serviceSelect');
    if (el) {
        new TomSelect(el, {
            create: false,
            sortField: { field: "text", direction: "asc" },
            placeholder: "Chọn dịch vụ...",
            allowEmptyOption: true,
        });
    }
});


window.Alpine = Alpine;

Alpine.start();

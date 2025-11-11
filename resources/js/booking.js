/**
 * Booking System JavaScript Module
 * Handles room selection, voucher application, and price calculations
 */

class BookingManager {
    constructor() {
        // DOM element references
        this.giaMotDem = parseFloat(document.querySelector('[data-gia-mot-dem]')?.getAttribute('data-gia-mot-dem') || 0);
        this.loaiPhongId = document.querySelector('[data-loai-phong-id]')?.getAttribute('data-loai-phong-id') || 0;

        // Config injected from Blade
        this.bookingConfig = window.bookingConfig || {};
        this.routes = this.bookingConfig.routes || {};
        this.csrfToken = this.bookingConfig.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        this.defaultRoomCount = this.bookingConfig.defaultRoomCount || 0;

        // Voucher elements
        this.openVoucherLink = document.getElementById('openVoucherLink');
        this.openVoucherInlineBtn = document.getElementById('openVoucherInline');
        this.voucherActionText = document.getElementById('voucherActionText');
        this.voucherDisplayDiv = document.getElementById('voucherDisplay');
        this.voucherCodeInput = document.getElementById('voucherCode');
        this.discountValueInput = document.getElementById('discountValue');

        // Date elements
        this.checkinInput = document.getElementById('ngay_nhan_input');
        this.checkoutInput = document.getElementById('ngay_tra_input');

        // Price display elements
        this.soDemLuuTruElement = document.getElementById('so-dem-luu-tru');
        this.totalBeforeDiscountDiv = document.getElementById('totalBeforeDiscount');
        this.totalAfterDiscountDiv = document.getElementById('totalAfterDiscount');
        this.finalBookingPriceInput = document.getElementById('finalBookingPrice');

        // Room summary elements
        this.selectedRoomsSummaryChip = document.getElementById('selectedRoomsSummary');
        this.summaryRoomCount = document.getElementById('summaryRoomCount');

        // Form element
        this.finalBookingForm = document.getElementById('finalBookingForm');

        // State variables
        this.popupElement = null;
        this.currentDiscountPercent = parseFloat(this.discountValueInput?.value || 0);
        this.alertTimeout = null;
        this.isCompletingBooking = false;
        this.roomIndex = 1; // Start from 1 since first room is index 0
        this.roomTypeOptionsHtml = '';

        // Initialize
        this.init();
    }

    init() {
        // Restore voucher state
        this.restoreVoucherState();

        // Set up event listeners
        this.setupEventListeners();

        // Initialize calculations
        this.tinhTongTien();

        // Update room availability and prices after a short delay
        setTimeout(() => {
            if (this.checkinInput && this.checkoutInput && this.checkinInput.value && this.checkoutInput.value) {
                this.updateAvailableCount();
            }

            // Update prices for all rooms
            const defaultRoomSelect = document.querySelector('.room-type-select');
            if (defaultRoomSelect && defaultRoomSelect.value) {
                this.updateRoomPrice(defaultRoomSelect);
            }

            // Update subtotals for all rooms
            document.querySelectorAll('.room-item').forEach(roomItem => {
                const hiddenInput = roomItem.querySelector('.room-type-select[type="hidden"]');
                if (hiddenInput) {
                    this.updateRoomPrice(hiddenInput);
                }
            });
        }, 100);
    }

    // === UTILITY FUNCTIONS ===
    formatCurrency(number) {
        return Math.round(number).toLocaleString('vi-VN', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }) + ' VNĐ';
    }

    getDatesAndDays() {
        const checkinValue = this.checkinInput?.value;
        const checkoutValue = this.checkoutInput?.value;

        const checkinDate = new Date(checkinValue);
        const checkoutDate = new Date(checkoutValue);

        let soDem;

        if (checkinDate instanceof Date && !isNaN(checkinDate) &&
            checkoutDate instanceof Date && !isNaN(checkoutDate) &&
            checkoutDate > checkinDate) {

            const diffTime = Math.abs(checkoutDate - checkinDate);
            soDem = Math.round(diffTime / (1000 * 60 * 60 * 24));
        } else {
            soDem = 1;
        }

        return {
            checkinValue,
            checkoutValue,
            soDem
        };
    }

    getDiscountPercentFromCard(cardElement) {
        const discountElement = cardElement.querySelector('.font-semibold.text-gray-800.text-base');
        if (!discountElement) return 0;

        const text = discountElement.textContent || '';
        const match = text.match(/Giảm\s*(\d+)\s*%/i);

        if (match && match[1]) {
            return parseFloat(match[1].trim());
        }
        return 0;
    }

    // === VOUCHER MANAGEMENT ===
    saveVoucherState() {
        try {
            sessionStorage.setItem('appliedVoucherCode', this.voucherCodeInput.value);
            sessionStorage.setItem('appliedDiscountPercent', this.currentDiscountPercent.toString());
            sessionStorage.setItem('appliedVoucherRoomId', this.loaiPhongId);
        } catch (e) {
            console.warn('Không thể lưu trạng thái voucher vào sessionStorage:', e);
        }
    }

    clearSavedVoucherState() {
        try {
            sessionStorage.removeItem('appliedVoucherCode');
            sessionStorage.removeItem('appliedDiscountPercent');
            sessionStorage.removeItem('appliedVoucherRoomId');
        } catch (e) {
            console.warn('Không thể xóa trạng thái voucher khỏi sessionStorage:', e);
        }
    }

    restoreVoucherState() {
        try {
            const savedCode = sessionStorage.getItem('appliedVoucherCode');
            const savedPercent = parseFloat(sessionStorage.getItem('appliedDiscountPercent')) || 0;
            const savedRoomId = sessionStorage.getItem('appliedVoucherRoomId');

            if (savedCode && savedPercent > 0 && savedRoomId === this.loaiPhongId) {
                this.currentDiscountPercent = savedPercent;
                this.voucherCodeInput.value = savedCode;
                this.discountValueInput.value = savedPercent.toString();
                return true;
            }
        } catch (e) {
            console.warn('Không thể khôi phục trạng thái voucher từ sessionStorage:', e);
        }
        return false;
    }

    clearVoucher() {
        this.currentDiscountPercent = 0;
        this.voucherCodeInput.value = '';
        this.discountValueInput.value = '0';
        this.clearSavedVoucherState();
        this.tinhTongTien();

        const popup = this.popupElement;
        if (popup) {
            const searchInput = popup.querySelector('#popupVoucherCodeInput');
            if (searchInput) searchInput.value = '';
            this.displayAlert(popup, '');
        }
    }

    applyVoucher(code, percent, popup) {
        const numericPercent = parseFloat(percent) || 0;
        if (!code || numericPercent === 0) return;

        this.currentDiscountPercent = numericPercent;
        this.voucherCodeInput.value = code;
        this.discountValueInput.value = this.currentDiscountPercent;

        this.saveVoucherState();
        this.tinhTongTien();

        if (popup) {
            const closeBtn = popup.querySelector('#closeVoucherPopup');
            if (closeBtn) {
                closeBtn.dispatchEvent(new Event('click'));
            }
        }
    }

    displayAlert(popup, message, isError = false) {
        const alertContainer = popup.querySelector('#voucherAlertMessage');
        const pTag = alertContainer ? alertContainer.querySelector('p') : null;
        if (!alertContainer || !pTag) return;

        if (this.alertTimeout) {
            clearTimeout(this.alertTimeout);
            this.alertTimeout = null;
        }

        if (!message) {
            alertContainer.classList.add('hidden');
            pTag.textContent = '';
            return;
        }

        pTag.innerHTML = message;
        alertContainer.classList.remove('hidden');

        alertContainer.className = 'mt-2';
        pTag.className = isError ?
            'text-sm py-2 px-3 rounded-lg bg-red-100 text-red-700 font-medium' :
            'text-sm py-2 px-3 rounded-lg bg-sky-100 text-sky-800 font-medium';

        this.alertTimeout = setTimeout(() => {
            alertContainer.classList.add('hidden');
            this.alertTimeout = null;
        }, 5000);
    }

    // === PRICE CALCULATIONS ===
    tinhTongTien() {
        // Tính tổng giá từ tất cả các loại phòng được chọn
        const { soDem } = this.getDatesAndDays();
        let totalBeforeDiscountAmount = 0;

        // Tìm tất cả hidden input với class room-type-select
        document.querySelectorAll('.room-type-select').forEach(selectElement => {
            let price = 0;
            let quantity = 1;

            // Nếu là hidden input
            if (selectElement.type === 'hidden') {
                price = parseFloat(selectElement.getAttribute('data-price')) || 0;
                // Tìm quantity input tương ứng
                const roomId = selectElement.name.match(/rooms\[(\d+)\]/);
                if (roomId) {
                    const quantityInput = document.querySelector(`input[name="rooms[${roomId[1]}][so_luong]"]`);
                    if (quantityInput) {
                        quantity = parseInt(quantityInput.value) || 1;
                    }
                }
            }
            // Nếu là select element
            else if (selectElement.tagName === 'SELECT' && selectElement.value) {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                // Tìm quantity trong cùng container
                const roomItem = selectElement.closest('.room-item');
                if (roomItem) {
                    const quantityInput = roomItem.querySelector('.room-quantity');
                    if (quantityInput) {
                        quantity = parseInt(quantityInput.value) || 1;
                    }
                }
            }

            if (price > 0) {
                totalBeforeDiscountAmount += price * quantity * soDem;
            }
        });

        const discountPercent = this.currentDiscountPercent;

        let discountAmount = 0;
        let totalAfterDiscount = totalBeforeDiscountAmount;

        if (discountPercent > 0) {
            discountAmount = totalBeforeDiscountAmount * (discountPercent / 100);
            totalAfterDiscount = Math.max(0, totalBeforeDiscountAmount - discountAmount);
        }

        // Cập nhật giao diện chính
        this.soDemLuuTruElement.textContent = `Số đêm: ${soDem} đêm`;

        // Tìm element hiển thị discount amount
        let discountAmountDisplay = document.getElementById('discountAmountDisplay');
        if (!discountAmountDisplay) {
            discountAmountDisplay = document.createElement('div');
            discountAmountDisplay.id = 'discountAmountDisplay';
            discountAmountDisplay.className = 'text-sm text-green-600 mb-1 hidden';
            this.totalAfterDiscountDiv.parentNode.insertBefore(discountAmountDisplay, this.totalAfterDiscountDiv);
        }

        if (discountPercent > 0) {
            const currentCode = this.voucherCodeInput.value || 'VOUCHER';

            // Hiển thị giá gốc
            this.totalBeforeDiscountDiv.innerHTML =
                `<span class="text-gray-600">Giá gốc:</span> <span class="line-through text-gray-500">${this.formatCurrency(totalBeforeDiscountAmount)}</span>`;
            this.totalBeforeDiscountDiv.classList.remove('hidden');

            // Hiển thị số tiền giảm
            discountAmountDisplay.innerHTML =
                `<span class="text-green-600">Giảm giá:</span> <span class="font-semibold text-green-600">-${this.formatCurrency(discountAmount)}</span>`;
            discountAmountDisplay.classList.remove('hidden');

            // Cập nhật tổng tiền sau giảm giá
            this.totalAfterDiscountDiv.innerHTML = `Tổng: ${this.formatCurrency(totalAfterDiscount)}`;
            this.totalAfterDiscountDiv.classList.add('text-xl', 'font-bold', 'text-red-600');

            // Cập nhật giao diện cho LINK TEXT
            this.voucherActionText.textContent = `Đã áp dụng mã: ${currentCode}`;

            this.openVoucherLink.classList.remove('text-green-600', 'hover:text-green-800');
            this.openVoucherLink.classList.add('text-indigo-600', 'hover:text-indigo-800');

            // Phần hiển thị chi tiết voucher
            this.voucherDisplayDiv.innerHTML = `
                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mt-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-green-800">Mã ${currentCode}</p>
                                <p class="text-xs text-green-600">Giảm ${discountPercent}%</p>
                            </div>
                        </div>
                        <button id="voucherClearLink" type="button" class="text-xs text-red-600 hover:text-red-700 font-semibold transition hover:underline">
            Hủy
        </button>
                    </div>
                </div>
        `;
            this.voucherDisplayDiv.classList.remove('hidden');

            const clearLink = this.voucherDisplayDiv.querySelector('#voucherClearLink');
            if (clearLink) {
                const newClearLink = clearLink.cloneNode(true);
                clearLink.parentNode.replaceChild(newClearLink, clearLink);
                newClearLink.addEventListener('click', () => {
                    this.clearVoucher();
                });
            }

        } else {
            // Không có voucher
            this.totalBeforeDiscountDiv.classList.add('hidden');
            discountAmountDisplay.classList.add('hidden');

            // Cập nhật tổng tiền
            this.totalAfterDiscountDiv.innerHTML = `Tổng: ${this.formatCurrency(totalAfterDiscount)}`;
            this.totalAfterDiscountDiv.classList.add('text-xl', 'font-bold', 'text-red-600');

            // Reset link text
            this.voucherActionText.textContent = 'Chọn hoặc nhập mã giảm giá';

            this.openVoucherLink.classList.remove('text-green-600', 'hover:text-green-800');
            this.openVoucherLink.classList.add('text-indigo-600', 'hover:text-indigo-800');

            this.voucherDisplayDiv.classList.add('hidden');
        }

        this.finalBookingPriceInput.value = Math.round(totalAfterDiscount);
        this.discountValueInput.value = this.currentDiscountPercent;

        return totalBeforeDiscountAmount;
    }

    // === ROOM MANAGEMENT ===
    updateRoomAvailability(roomIndex, loaiPhongId, checkin, checkout) {
        if (!checkin || !checkout) {
            return;
        }

        // Validate dates
        if (new Date(checkout) <= new Date(checkin)) {
            return;
        }

        if (!this.routes.availableCount) {
            console.error('Booking route for availability is missing.');
            return;
        }

        // Format date for display
        const formatDate = (dateStr) => {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        };

        fetch(this.routes.availableCount, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({
                loai_phong_id: loaiPhongId,
                checkin: checkin,
                checkout: checkout
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const availableCount = Math.max(0, data.availableCount);

                // Update availability display
                const availabilityEl = document.getElementById(`room_availability_${roomIndex}`);
                if (availabilityEl) {
                    availabilityEl.innerHTML = `
                        <i class="fas fa-bed text-blue-500"></i> Còn ${availableCount} phòng trống
                        <span class="text-blue-500 text-xs">(từ ${formatDate(checkin)} đến ${formatDate(checkout)})</span>
                    `;
                }

                // Update data-max and max quantity
                const quantityInput = document.getElementById(`room_quantity_${roomIndex}`);
                if (quantityInput) {
                    quantityInput.setAttribute('data-max', availableCount);
                    const maxQuantityEl = document.getElementById(`max_quantity_${roomIndex}`);
                    if (maxQuantityEl) {
                        maxQuantityEl.textContent = `${availableCount}`;
                    }
                    // Reset quantity if it exceeds max
                    const currentQuantity = parseInt(quantityInput.value) || 1;
                    if (currentQuantity > availableCount && availableCount > 0) {
                        quantityInput.value = availableCount;
                        this.updateRoomQuantity(roomIndex);
                    }
                }
            } else {
                console.error('Error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error updating available count:', error);
        });
    }

    updateAvailableCount() {
        const checkin = this.checkinInput?.value;
        const checkout = this.checkoutInput?.value;

        if (!checkin || !checkout) {
            // Show default message for all rooms
            document.querySelectorAll('.room-item').forEach(roomItem => {
                const roomIndex = roomItem.getAttribute('data-room-index');
                const availabilityEl = document.getElementById(`room_availability_${roomIndex}`);
                if (availabilityEl) {
                    // Get default room count from data attribute or select
                    const select = roomItem.querySelector('.room-type-select');
                    let defaultCount = 0;
                    if (select && select.tagName === 'SELECT' && select.value) {
                        const selectedOption = select.options[select.selectedIndex];
                        defaultCount = parseInt(selectedOption.getAttribute('data-so-luong-trong')) || 0;
                    } else if (roomIndex === '0') {
                        defaultCount = this.defaultRoomCount || 0;
                    }
                    availabilityEl.innerHTML = `<i class="fas fa-bed text-blue-500"></i> Còn ${defaultCount} phòng (vui lòng chọn ngày để xem số phòng trống)`;
                }
            });
            return;
        }

        // Validate dates
        if (new Date(checkout) <= new Date(checkin)) {
            return;
        }

        // Update for all selected rooms
        document.querySelectorAll('.room-item').forEach(roomItem => {
            const roomIndex = roomItem.getAttribute('data-room-index');
            const select = roomItem.querySelector('.room-type-select');

            if (select && select.value) {
                let loaiPhongId = null;

                if (select.tagName === 'SELECT') {
                    loaiPhongId = select.value;
                } else if (select.type === 'hidden') {
                    loaiPhongId = select.value;
                }

                if (loaiPhongId) {
                    this.updateRoomAvailability(roomIndex, loaiPhongId, checkin, checkout);
                }
            }
        });
    }

    updateSummaryDate() {
        const checkinValue = this.checkinInput?.value;
        const checkoutValue = this.checkoutInput?.value;

        if (checkinValue && checkoutValue) {
            const checkinDate = new Date(checkinValue);
            const checkoutDate = new Date(checkoutValue);

            if (checkinDate instanceof Date && !isNaN(checkinDate) &&
                checkoutDate instanceof Date && !isNaN(checkoutDate) &&
                checkoutDate > checkinDate) {

                const diffTime = Math.abs(checkoutDate - checkinDate);
                const soDem = Math.round(diffTime / (1000 * 60 * 60 * 24));

                // Format dates
                const formatDate = (date) => {
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}/${month}/${year}`;
                };

                const summaryDateText = `${formatDate(checkinDate)} - ${formatDate(checkoutDate)} (${soDem} đêm)`;

                // Update summary element
                const summaryDateElement = document.querySelector('.summary-date');
                if (summaryDateElement) {
                    summaryDateElement.textContent = summaryDateText;
                }
            }
        }
    }

    // === EVENT LISTENERS SETUP ===
    setupEventListeners() {
        // Voucher events
        if (this.openVoucherInlineBtn && this.openVoucherLink) {
            this.openVoucherInlineBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openVoucherLink.click();
            });
        }

        if (this.openVoucherLink) {
            this.openVoucherLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.openVoucherPopup();
            });
        }

        // Date change events
        if (this.checkinInput) {
            this.checkinInput.addEventListener('change', () => {
                this.clearVoucher();
                this.tinhTongTien();
                this.updateAvailableCount();
                this.updateSummaryDate();
                // Update prices for all rooms
                document.querySelectorAll('.room-type-select').forEach(select => {
                    if (select.value) {
                        this.updateRoomPrice(select);
                    }
                });
            });
        }

        if (this.checkoutInput) {
            this.checkoutInput.addEventListener('change', () => {
                this.clearVoucher();
                this.tinhTongTien();
                this.updateAvailableCount();
                this.updateSummaryDate();
                // Update prices for all rooms
                document.querySelectorAll('.room-type-select').forEach(select => {
                    if (select.value) {
                        this.updateRoomPrice(select);
                    }
                });
            });
        }

        // Form submit event
        if (this.finalBookingForm) {
            this.finalBookingForm.addEventListener('submit', () => {
                this.isCompletingBooking = true;
            });
        }

        // Before unload event
        window.addEventListener('beforeunload', () => {
            if (!this.isCompletingBooking) {
                this.clearSavedVoucherState();
            }
        });

        // Scroll to rooms button
        const scrollToRoomsBtn = document.getElementById('scrollToRoomsBtn');
        const roomSelectionGrid = document.getElementById('roomSelectionGrid');
        if (scrollToRoomsBtn && roomSelectionGrid) {
            scrollToRoomsBtn.addEventListener('click', () => {
                roomSelectionGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }

        // Room selection buttons
        document.querySelectorAll('.choose-room-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.handleRoomSelection(btn);
            });
        });

        // Add room button
        const addRoomBtn = document.getElementById('addRoomBtn');
        if (addRoomBtn) {
            addRoomBtn.addEventListener('click', () => {
                const newIndex = this.addRoomCard();
                const select = document.getElementById(`room_type_select_${newIndex}`);
                if (select) {
                    select.focus();
                }
            });
        }
    }

    // === VOUCHER POPUP ===
    openVoucherPopup() {
        const { soDem } = this.getDatesAndDays();
        const currentTotal = this.giaMotDem * soDem;
        const fetchUrl = `/client/voucher?current_total=${Math.round(currentTotal)}&loai_phong_id=${this.loaiPhongId}`;

        fetch(fetchUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                if (!this.popupElement) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    this.popupElement = tempDiv.firstChild;
                    document.body.appendChild(this.popupElement);
                } else {
                    const innerContent = this.popupElement.querySelector('.custom-voucher-inner');
                    if (innerContent) {
                        const newDoc = new DOMParser().parseFromString(html, 'text/html');
                        const newVoucherList = newDoc.querySelector('.custom-scrollbar');
                        const oldVoucherList = innerContent.querySelector('.custom-scrollbar');
                        if (oldVoucherList && newVoucherList) {
                            oldVoucherList.parentNode.replaceChild(newVoucherList, oldVoucherList);
                        }
                    }
                }

                this.setupPopupEvents(this.popupElement);

                this.popupElement.classList.remove('hidden');

                if (this.currentDiscountPercent > 0) {
                    this.displayAlert(this.popupElement,
                        `Mã ${this.voucherCodeInput.value} đang được áp dụng.`, false);
                } else {
                    this.displayAlert(this.popupElement, '');
                }
            })
            .catch(err => {
                console.error('Lỗi khi tải voucher:', err);
                if (!this.popupElement) {
                    alert('Không thể tải danh sách voucher. Vui lòng kiểm tra kết nối mạng hoặc server.');
                } else {
                    const loadingMessage = this.popupElement.querySelector('#voucherAlertMessage p');
                    if (loadingMessage) {
                        loadingMessage.textContent = 'Không thể tải danh sách voucher. Vui lòng thử lại sau. (Lỗi server/route)';
                        this.displayAlert(this.popupElement, loadingMessage.textContent, true);
                    }
                }
            });
    }

    setupPopupEvents(popup) {
        const searchInput = popup.querySelector('#popupVoucherCodeInput');
        if (searchInput) {
            searchInput.value = '';
        }

        const closeBtn = popup.querySelector('#closeVoucherPopup');
        if (!closeBtn.hasEventListener) {
            const closePopup = () => {
                const popupContent = popup.querySelector('.custom-voucher-inner');
                if (popupContent) {
                    popupContent.classList.remove('animate-fadeIn');
                    this.displayAlert(popup, '');
                }
                setTimeout(() => {
                    popup.classList.add('hidden');
                }, 300);
            };

            closeBtn.addEventListener('click', closePopup);
            popup.addEventListener('click', function(e) {
                if (e.target === popup) closePopup();
            });
            closeBtn.hasEventListener = true;
        }

        const voucherListContainer = popup.querySelector('.custom-scrollbar');
        if (voucherListContainer && !voucherListContainer.hasEventListener) {
            voucherListContainer.addEventListener('click', (e) => {
                const applyBtn = e.target.closest('.apply-voucher-btn');

                if (applyBtn) {
                    if (this.currentDiscountPercent > 0) {
                        const targetCard = applyBtn.closest('.custom-voucher-card');
                        const newCode = targetCard.dataset.voucherCode;
                        const currentCode = this.voucherCodeInput.value;

                        if (newCode === currentCode) {
                            this.displayAlert(popup, `Mã ${newCode} đã được áp dụng rồi.`, false);
                            return;
                        }

                        this.displayAlert(popup,
                            `Bạn chỉ có thể áp dụng 1 mã voucher duy nhất. Vui lòng nhấn "Hủy" mã ${this.voucherCodeInput.value} ở bên dưới hoặc ngoài trang thanh toán trước.`,
                            true);
                        return;
                    }

                    const targetCard = applyBtn.closest('.custom-voucher-card');
                    if (!targetCard) return;

                    const isValid = targetCard.dataset.isValid === 'true';
                    const code = targetCard.dataset.voucherCode;
                    const percent = this.getDiscountPercentFromCard(targetCard);

                    if (isValid) {
                        this.applyVoucher(code, percent, popup);
                    } else {
                        this.displayAlert(popup,
                            'Voucher này chưa đủ điều kiện (giá trị đơn hàng tối thiểu hoặc không áp dụng cho loại phòng này).',
                            true);
                    }
                }
            });
            voucherListContainer.hasEventListener = true;
        }

        const searchBtn = popup.querySelector('#searchVoucherBtn');

        if (searchBtn && searchInput) {
            // Reset event listener
            const newSearchBtn = searchBtn.cloneNode(true);
            searchBtn.parentNode.replaceChild(newSearchBtn, searchBtn);

            const handleSearch = () => {
                const searchCode = searchInput.value.toUpperCase().trim();
                this.displayAlert(popup, '');

                if (!searchCode) {
                    this.displayAlert(popup, 'Vui lòng nhập mã voucher.', true);
                    return;
                }

                // Check if already applied
                if (this.currentDiscountPercent > 0) {
                    const currentAppliedCode = this.voucherCodeInput.value;

                    // If same code, cancel it
                    if (searchCode === currentAppliedCode) {
                        this.clearVoucher();
                        this.displayAlert(popup, `Mã ${searchCode} đã được hủy thành công.`, false);
                        return;
                    }

                    this.displayAlert(popup,
                        `Bạn chỉ có thể áp dụng 1 mã voucher duy nhất. Vui lòng nhấn "Hủy" mã ${currentAppliedCode} trước khi áp dụng mã mới.`,
                        true);
                    return;
                }

                const card = popup.querySelector(`[data-voucher-code="${searchCode}"]`);

                if (card) {
                    const isValid = card.dataset.isValid === 'true';
                    const percent = this.getDiscountPercentFromCard(card);

                    if (isValid) {
                        this.applyVoucher(searchCode, percent, popup);
                    } else {
                        this.displayAlert(popup,
                            `Mã "${searchCode}" không hợp lệ cho đơn hàng này (không đủ giá trị tối thiểu hoặc không áp dụng cho loại phòng này).`,
                            true);
                    }
                } else {
                    this.displayAlert(popup, `Mã "${searchCode}" không tồn tại.`, true);
                }
            };

            newSearchBtn.addEventListener('click', handleSearch);
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleSearch();
                }
            });
        }
    }

    // === ROOM SELECTION ===
    handleRoomSelection(btn) {
        const roomId = btn.dataset.roomId;
        const selectedRoomCard = btn.closest('.room-card');

        // Set active card
        this.setActiveRoomCard(selectedRoomCard);

        const indexToUse = this.addRoomCard();
        const select = document.getElementById(`room_type_select_${indexToUse}`);
        if (select) {
            select.value = roomId;
            this.handleRoomTypeChange(indexToUse);
            select.dispatchEvent(new Event('change'));
        }

        const targetCard = document.getElementById(`room_item_${indexToUse}`);
        if (targetCard) {
            targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Update hero section
        this.updateHeroSection(selectedRoomCard);
    }

    setActiveRoomCard(targetCard) {
        document.querySelectorAll('.room-card').forEach(card => card.classList.remove('room-card--active'));
        if (targetCard) {
            targetCard.classList.add('room-card--active');
        }
    }

    updateHeroSection(selectedRoomCard) {
        if (!selectedRoomCard) return;

        // Get info from data attributes
        const roomName = selectedRoomCard.dataset.roomName || '';
        const roomImage = selectedRoomCard.dataset.roomImage || '';
        const roomPrice = parseFloat(selectedRoomCard.dataset.roomPrice) || 0;

        // Update hero section
        const heroTitle = document.getElementById('heroPrimaryTitle');
        const heroImage = document.getElementById('heroPrimaryImage');
        const heroPrice = document.getElementById('heroPriceValue');

        if (heroTitle && roomName) {
            heroTitle.textContent = roomName;
            heroTitle.setAttribute('data-default-title', roomName);
        }

        if (heroImage && roomImage) {
            heroImage.src = roomImage;
            heroImage.setAttribute('data-default-image', roomImage);
        }

        if (heroPrice && roomPrice > 0) {
            heroPrice.textContent = Math.round(roomPrice).toLocaleString('vi-VN') + ' VND';
            heroPrice.setAttribute('data-default-price', roomPrice);
        }
    }

    addRoomCard() {
        const newHtml = this.buildRoomCardHtml(this.roomIndex);
        const roomsContainer = document.getElementById('roomsContainer');
        roomsContainer.insertAdjacentHTML('beforeend', newHtml);

        const currentIndex = this.roomIndex;
        this.roomIndex++;

        const newCard = document.getElementById(`room_item_${currentIndex}`);
        if (newCard) {
            newCard.classList.add('selected-room-card--pulse');
            setTimeout(() => newCard.classList.remove('selected-room-card--pulse'), 1000);
        }

        return currentIndex;
    }

    getRoomTypeOptionsHtml() {
        if (!this.roomTypeOptionsHtml) {
            const template = document.getElementById('roomTypeOptionsTemplate');
            this.roomTypeOptionsHtml = template ? template.innerHTML.trim() : '';
        }
        return this.roomTypeOptionsHtml;
    }

    buildRoomCardHtml(index) {
        return `
            <div class="room-item selected-room-card" data-room-index="${index}" id="room_item_${index}">
                <div class="selected-room-card__inner">
                    <div class="selected-room-card__header">
                        <h4>Loại phòng ${index + 1}</h4>
                        <button type="button" class="selected-room-card__remove" onclick="window.bookingManager.removeRoom(${index})">
                            <i class="fas fa-times"></i> Xóa
                        </button>
                    </div>
                    <div class="selected-room-card__body">
                        <div class="selected-room-card__placeholder">
                            <p class="selected-room-card__label">Chọn loại phòng *</p>
                            <div class="selected-room-card__control">
                                <select name="rooms[${index}][loai_phong_id]"
                                        id="room_type_select_${index}"
                                        class="room-type-select w-full border-2 border-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="window.bookingManager.handleRoomTypeChange(${index})">
                                    ${this.getRoomTypeOptionsHtml()}
                                </select>
                            </div>
                        </div>

                        <div class="selected-room-card__details hidden selected-room-details" id="room_details_${index}">
                            <div class="selected-room-card__media" id="room_image_${index}">
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                                </div>
                            </div>
                            <div class="selected-room-card__info">
                                <h5 id="room_name_${index}"></h5>
                                <div class="selected-room-card__price" id="room_price_${index}"></div>
                                <p class="text-sm text-gray-600" id="room_availability_${index}">
                                    <i class="fas fa-bed text-blue-500"></i> Đang tải...
                                </p>
                            </div>
                        </div>

                        <div class="quantity-section hidden">
                            <label class="block text-sm font-medium mb-2">Số lượng phòng *</label>
                            <div class="flex items-center gap-3">
                                <button type="button"
                                    class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                    onclick="window.bookingManager.decreaseRoomQuantity(${index})">
                                    −
                                </button>
                                <input type="text"
                                    name="rooms[${index}][so_luong]"
                                    id="room_quantity_${index}"
                                    value="1"
                                    data-max="0"
                                    class="room-quantity w-20 text-center border-2 border-gray-300 rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-semibold"
                                    onchange="window.bookingManager.updateRoomQuantity(${index})">
                                <button type="button"
                                    class="w-10 h-10 flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition-colors font-bold text-lg"
                                    onclick="window.bookingManager.increaseRoomQuantity(${index})">
                                    +
                                </button>
                                <span class="text-sm text-gray-600 ml-2">
                                    / <span id="max_quantity_${index}">0</span> phòng
                                </span>
                            </div>
                            <p class="text-xs text-red-600 mt-1 hidden" id="quantity_error_${index}">
                                Số lượng không được vượt quá 0 phòng
                            </p>
                        </div>

                        <div class="mt-3 text-sm text-gray-700 hidden" id="subtotal_section_${index}">
                            <span class="room-subtotal font-medium">Giá: <span id="room_subtotal_${index}">0</span></span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    removeRoom(index) {
        const roomItem = document.querySelector(`.room-item[data-room-index="${index}"]`);
        if (roomItem) {
            roomItem.remove();
            this.updateTotalPrice();
            this.tinhTongTien();
        }
    }

    handleRoomTypeChange(index) {
        const select = document.getElementById(`room_type_select_${index}`);
        if (!select || !select.value) return;

        const selectedOption = select.options[select.selectedIndex];
        const loaiPhongId = selectedOption.value;
        const tenLoai = selectedOption.getAttribute('data-ten-loai');
        const anh = selectedOption.getAttribute('data-anh');
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const basePrice = parseFloat(selectedOption.getAttribute('data-base-price')) || 0;
        const giaKhuyenMai = parseFloat(selectedOption.getAttribute('data-gia-khuyen-mai')) || 0;
        const giaCoBan = parseFloat(selectedOption.getAttribute('data-gia-co-ban')) || 0;

        // Update display info
        document.getElementById(`room_name_${index}`).textContent = tenLoai;

        // Update price
        const priceDiv = document.getElementById(`room_price_${index}`);
        const formatNumber = (num) => Math.round(num).toLocaleString('vi-VN');
        if (giaKhuyenMai > 0) {
            priceDiv.innerHTML = `
                <span class="text-red-600 font-bold text-lg">${formatNumber(giaKhuyenMai)}</span>
                <span class="text-gray-500 line-through text-sm">${formatNumber(giaCoBan)}</span>
                <span class="text-gray-600 text-sm">VNĐ/đêm</span>
            `;
        } else {
            priceDiv.innerHTML = `
                <span class="text-blue-600 font-bold text-lg">${formatNumber(giaCoBan)}</span>
                <span class="text-gray-600 text-sm">VNĐ/đêm</span>
            `;
        }

        // Update image
        const imageDiv = document.getElementById(`room_image_${index}`);
        if (anh && anh.trim() !== '') {
            const imagePath = anh.startsWith('/') ? anh : '/' + anh;
            imageDiv.innerHTML = `<img src="${imagePath}" alt="${tenLoai}" class="w-full h-full object-cover">`;
        } else {
            imageDiv.innerHTML = `<div class="w-full h-full bg-gray-200 flex items-center justify-center"><i class="fas fa-image text-gray-400 text-4xl"></i></div>`;
        }

        // Show room details
        document.getElementById(`room_details_${index}`).classList.remove('hidden');
        const placeholderBlock = select.closest('.selected-room-card__body')?.querySelector('.selected-room-card__placeholder');
        if (placeholderBlock) {
            placeholderBlock.classList.add('hidden');
        }

        // Show quantity section
        const quantityInput = document.getElementById(`room_quantity_${index}`);
        if (quantityInput) {
            quantityInput.closest('.quantity-section').classList.remove('hidden');
            document.getElementById(`subtotal_section_${index}`).classList.remove('hidden');
        }

        // Get room availability
        const checkin = this.checkinInput?.value;
        const checkout = this.checkoutInput?.value;
        if (checkin && checkout) {
            this.updateRoomAvailability(index, loaiPhongId, checkin, checkout);
        } else {
            // Use default room count
            const soLuongTrong = parseInt(selectedOption.getAttribute('data-so-luong-trong')) || 0;
            const availabilityEl = document.getElementById(`room_availability_${index}`);
            if (availabilityEl) {
                availabilityEl.innerHTML = `
                    <i class="fas fa-bed text-blue-500"></i> Còn ${soLuongTrong} phòng (vui lòng chọn ngày để xem số phòng trống)
                `;
            }
            if (quantityInput) {
                quantityInput.setAttribute('data-max', soLuongTrong);
                const maxQuantityEl = document.getElementById(`max_quantity_${index}`);
                if (maxQuantityEl) {
                    maxQuantityEl.textContent = `${soLuongTrong}`;
                }
                const quantityErrorEl = document.getElementById(`quantity_error_${index}`);
                if (quantityErrorEl) {
                    quantityErrorEl.textContent = `Số lượng không được vượt quá ${soLuongTrong} phòng`;
                }
            }
        }

        // Recalculate prices
        this.updateRoomPrice(select);
        this.updateRoomQuantity(index);
        this.updateTotalPrice();
        this.tinhTongTien();
    }

    updateRoomPrice(selectElement) {
        // Handle both select and hidden input
        let price = 0;
        let roomItem = null;
        let selectedOption = null;

        if (selectElement.tagName === 'SELECT') {
            selectedOption = selectElement.options[selectElement.selectedIndex];
            if (!selectedOption.value) {
                // Hide display if no room selected
                const roomItem = selectElement.closest('.room-item');
                const roomIndex = roomItem.getAttribute('data-room-index');
                document.querySelector(`#room_name_${roomIndex}`).closest('.selected-room-details').classList.add('hidden');
                document.querySelector(`#room_quantity_${roomIndex}`).closest('.quantity-section').classList.add('hidden');
                document.getElementById(`subtotal_section_${roomIndex}`).classList.add('hidden');
                return;
            }
            price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            roomItem = selectElement.closest('.room-item');

            // Show selected room info
            const roomIndex = roomItem.getAttribute('data-room-index');
            const tenLoai = selectedOption.getAttribute('data-ten-loai');
            const anh = selectedOption.getAttribute('data-anh');
            const soLuongTrong = parseInt(selectedOption.getAttribute('data-so-luong-trong')) || 0;
            const giaKhuyenMai = parseFloat(selectedOption.getAttribute('data-gia-khuyen-mai')) || 0;
            const giaCoBan = parseFloat(selectedOption.getAttribute('data-gia-co-ban')) || 0;

            // Update room name
            document.getElementById(`room_name_${roomIndex}`).textContent = tenLoai;

            // Update price
            const priceDiv = document.getElementById(`room_price_${roomIndex}`);
            const formatNumber = (num) => Math.round(num).toLocaleString('vi-VN');

            if (giaKhuyenMai > 0) {
                priceDiv.innerHTML = `
                    <span class="text-red-600 font-bold text-lg">${formatNumber(giaKhuyenMai)}</span>
                    <span class="text-gray-500 line-through text-sm">${formatNumber(giaCoBan)}</span>
                    <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                `;
            } else {
                priceDiv.innerHTML = `
                    <span class="text-blue-600 font-bold text-lg">${formatNumber(giaCoBan)}</span>
                    <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                `;
            }

            // Update availability (only if not already updated from API)
            const availabilityEl = document.getElementById(`room_availability_${roomIndex}`);
            const checkin = this.checkinInput?.value;
            const checkout = this.checkoutInput?.value;
            if (availabilityEl && (!checkin || !checkout || !availabilityEl.innerHTML.includes('(từ'))) {
                availabilityEl.innerHTML = `
                    <i class="fas fa-bed text-blue-500"></i> Còn ${soLuongTrong} phòng trống
                `;
            }

            // Update image
            const imageDiv = document.getElementById(`room_image_${roomIndex}`);
            if (anh && anh.trim() !== '') {
                const imagePath = anh.startsWith('/') ? anh : '/' + anh;
                imageDiv.innerHTML = `<img src="${imagePath}" alt="${tenLoai}" class="w-20 h-20 object-cover rounded-lg ml-3">`;
            } else {
                imageDiv.innerHTML = `<div class="w-full h-full bg-gray-200 flex items-center justify-center"><i class="fas fa-image text-gray-400 text-4xl"></i></div>`;
            }

            // Show room details
            document.getElementById(`room_details_${roomIndex}`).classList.remove('hidden');

            // Update quantity section
            const quantityInput = document.getElementById(`room_quantity_${roomIndex}`);
            if (quantityInput) {
                quantityInput.setAttribute('data-max', soLuongTrong);
                quantityInput.setAttribute('oninput', `validateRoomQuantity(this, ${roomIndex})`);
                const maxQuantityEl = document.getElementById(`max_quantity_${roomIndex}`);
                if (maxQuantityEl) {
                    maxQuantityEl.textContent = `${soLuongTrong}`;
                }
                const quantityErrorEl = document.getElementById(`quantity_error_${roomIndex}`);
                if (quantityErrorEl) {
                    quantityErrorEl.textContent = `Số lượng không được vượt quá ${soLuongTrong} phòng`;
                }
                quantityInput.closest('.quantity-section').classList.remove('hidden');
                const subtotalSection = document.getElementById(`subtotal_section_${roomIndex}`);
                if (subtotalSection) {
                    subtotalSection.classList.remove('hidden');
                }
            }

            // Update onclick handlers
            const decreaseBtn = quantityInput.closest('.quantity-section').querySelector('button[onclick*="decreaseRoomQuantity"]');
            const increaseBtn = quantityInput.closest('.quantity-section').querySelector('button[onclick*="increaseRoomQuantity"]');
            decreaseBtn.setAttribute('onclick', `decreaseRoomQuantity(${roomIndex})`);
            increaseBtn.setAttribute('onclick', `increaseRoomQuantity(${roomIndex})`);

        } else if (selectElement.tagName === 'INPUT' && selectElement.type === 'hidden') {
            price = parseFloat(selectElement.getAttribute('data-price')) || 0;
            roomItem = selectElement.closest('.room-item');
        }

        if (!roomItem) return;

        const quantityInput = roomItem.querySelector('.room-quantity');
        const roomIndex = roomItem.getAttribute('data-room-index');
        const subtotalSpan = document.getElementById('room_subtotal_' + roomIndex);

        if (quantityInput && subtotalSpan) {
            const quantity = parseInt(quantityInput.value) || 1;
            const { soDem } = this.getDatesAndDays();
            const subtotal = price * quantity * soDem;
            const formattedSubtotal = Math.round(subtotal).toLocaleString('vi-VN');
            subtotalSpan.textContent = formattedSubtotal + ' VNĐ';
        }

        this.updateTotalPrice();
    };

    // Room quantity functions
    increaseRoomQuantity(roomIndex) {
        const quantityInput = document.getElementById('room_quantity_' + roomIndex);
        if (!quantityInput) return;

        const maxQuantity = parseInt(quantityInput.getAttribute('data-max')) || 0;
        const currentValue = parseInt(quantityInput.value) || 1;

        if (maxQuantity > 0 && currentValue < maxQuantity) {
            quantityInput.value = currentValue + 1;
            quantityInput.dispatchEvent(new Event('change'));
            this.updateRoomQuantity(roomIndex);
        } else if (maxQuantity === 0) {
            quantityInput.value = currentValue + 1;
            this.updateRoomQuantity(roomIndex);
        }
    }

    decreaseRoomQuantity(roomIndex) {
        const quantityInput = document.getElementById('room_quantity_' + roomIndex);
        if (!quantityInput) return;

        const currentValue = parseInt(quantityInput.value) || 1;
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
            quantityInput.dispatchEvent(new Event('change'));
            this.updateRoomQuantity(roomIndex);
        }
    }

    updateRoomQuantity(roomIndex) {
        const quantityInput = document.getElementById('room_quantity_' + roomIndex);
        if (!quantityInput) return;

        // Validate and limit quantity
        const maxQuantity = parseInt(quantityInput.getAttribute('data-max')) || 0;
        let currentQuantity = parseInt(quantityInput.value) || 1;

        if (maxQuantity > 0 && currentQuantity > maxQuantity) {
            quantityInput.value = maxQuantity;
            currentQuantity = maxQuantity;
        }

        if (currentQuantity < 1) {
            quantityInput.value = 1;
            currentQuantity = 1;
        }

        const roomItem = quantityInput.closest('.room-item');
        const select = roomItem.querySelector('.room-type-select');

        if (!select || !select.value) {
            const subtotalSpan = document.getElementById('room_subtotal_' + roomIndex);
            if (subtotalSpan && subtotalSpan.textContent && subtotalSpan.textContent !== '0') {
                const currentPriceText = subtotalSpan.textContent.replace(/[^\d]/g, '');
                const currentPrice = parseInt(currentPriceText) || 0;
                if (currentPrice > 0) {
                    const { soDem } = this.getDatesAndDays();
                    const pricePerNight = currentPrice / (parseInt(quantityInput.value) || 1) / soDem;
                    const newSubtotal = pricePerNight * currentQuantity * soDem;
                    const formattedSubtotal = Math.round(newSubtotal).toLocaleString('vi-VN');
                    subtotalSpan.textContent = formattedSubtotal + ' VNĐ';
                }
            }
            return;
        }

        // Get price from select or hidden input
        let price = 0;
        if (select.tagName === 'SELECT') {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption) {
                price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            }
        } else if (select.type === 'hidden') {
            price = parseFloat(select.getAttribute('data-price')) || 0;
        }

        // Update price display
        if (price > 0) {
            const subtotalSpan = document.getElementById('room_subtotal_' + roomIndex);
            if (subtotalSpan) {
                const { soDem } = this.getDatesAndDays();
                const subtotal = price * currentQuantity * soDem;
                const formattedSubtotal = Math.round(subtotal).toLocaleString('vi-VN');
                subtotalSpan.textContent = formattedSubtotal + ' VNĐ';
            }
        }

        // Update summary
        const summaryEl = document.getElementById(`room_${roomIndex}_summary_quantity`);
        if (summaryEl) {
            summaryEl.textContent = `x${currentQuantity}`;
        }

        // Update totals
        this.updateTotalPrice();
        this.tinhTongTien();
    }

    updateTotalPrice() {
        const { soDem } = this.getDatesAndDays();
        let totalBeforeDiscount = 0;
        const roomsSummary = [];

        // Calculate total from all selected rooms
        document.querySelectorAll('.room-item').forEach(roomItem => {
            const select = roomItem.querySelector('.room-type-select');
            const quantityInput = roomItem.querySelector('.room-quantity');

            if (select && select.value && quantityInput) {
                let price = 0;
                let roomName = '';
                let roomImage = '';

                if (select.tagName === 'SELECT') {
                    const selectedOption = select.options[select.selectedIndex];
                    price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                    roomName = selectedOption.getAttribute('data-ten-loai') || '';
                    roomImage = selectedOption.getAttribute('data-anh') || '';
                } else if (select.tagName === 'INPUT' && select.type === 'hidden') {
                    price = parseFloat(select.getAttribute('data-price')) || 0;
                    roomName = select.getAttribute('data-room-type-name') || '';
                    roomImage = select.getAttribute('data-room-image') || '';
                }

                const quantity = parseInt(quantityInput.value) || 1;
                const roomIndex = roomItem.getAttribute('data-room-index');
                totalBeforeDiscount += price * quantity * soDem;

                if (roomName) {
                    roomsSummary.push({
                        name: roomName,
                        quantity: quantity,
                        price: price,
                        roomIndex: roomIndex,
                        image: roomImage
                    });
                }
            }
        });

        // Update room summary list
        const roomsSummaryList = document.getElementById('roomsSummaryList');
        if (roomsSummaryList) {
            if (roomsSummary.length > 0) {
                roomsSummaryList.classList.remove('summary-room-list--empty');
                const totalRoomsSelected = roomsSummary.reduce((sum, room) => sum + room.quantity, 0);
                roomsSummaryList.innerHTML = roomsSummary.map(room =>
                    `<div class="summary-room-line">
                        <div>
                            <p class="font-medium">${room.name}</p>
                            <small>${this.formatCurrency(room.price)} / đêm</small>
                        </div>
                        <div class="summary-room-actions">
                            <span>x${room.quantity}</span>
                            <button type="button" class="summary-room-remove" aria-label="Xóa ${room.name}" onclick="window.bookingManager.removeRoom(${room.roomIndex})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>`
                ).join('');

                if (this.selectedRoomsSummaryChip) {
                    this.selectedRoomsSummaryChip.classList.remove('hidden');
                }
                if (this.summaryRoomCount) {
                    this.summaryRoomCount.textContent = totalRoomsSelected;
                }
            } else {
                roomsSummaryList.classList.add('summary-room-list--empty');
                roomsSummaryList.innerHTML = '<p class="summary-room-empty">Chưa có phòng nào</p>';
                if (this.selectedRoomsSummaryChip) {
                    this.selectedRoomsSummaryChip.classList.add('hidden');
                }
                if (this.summaryRoomCount) {
                    this.summaryRoomCount.textContent = 0;
                }
            }
        }

        // Apply voucher discount
        const discountPercent = this.currentDiscountPercent;
        let discountAmount = 0;
        let totalAfterDiscount = totalBeforeDiscount;

        if (discountPercent > 0) {
            discountAmount = totalBeforeDiscount * (discountPercent / 100);
            totalAfterDiscount = Math.max(0, totalBeforeDiscount - discountAmount);
        }

        // Update display
        this.soDemLuuTruElement.textContent = `Số đêm: ${soDem} đêm`;
        this.totalAfterDiscountDiv.innerHTML = `Tổng: ${this.formatCurrency(totalAfterDiscount)}`;
        this.totalAfterDiscountDiv.classList.add('text-xl', 'font-bold', 'text-red-600');

        if (discountPercent > 0) {
            this.totalBeforeDiscountDiv.innerHTML =
                `Giá gốc: <span class="line-through text-gray-500">${this.formatCurrency(totalBeforeDiscount)}</span>`;
            this.totalBeforeDiscountDiv.classList.remove('hidden');
        } else {
            this.totalBeforeDiscountDiv.classList.add('hidden');
        }

        this.finalBookingPriceInput.value = Math.round(totalAfterDiscount);
    }

    // Utility function for hotel details
    showHotelDetails() {
        alert('Tính năng chi tiết khách sạn sẽ được phát triển trong tương lai!');
    }
}

// Global functions for backward compatibility
window.updateRoomPrice = function(selectElement) {
    if (window.bookingManager) {
        window.bookingManager.updateRoomPrice(selectElement);
    }
};

window.increaseRoomQuantity = function(roomIndex) {
    if (window.bookingManager) {
        window.bookingManager.increaseRoomQuantity(roomIndex);
    }
};

window.decreaseRoomQuantity = function(roomIndex) {
    if (window.bookingManager) {
        window.bookingManager.decreaseRoomQuantity(roomIndex);
    }
};

window.updateRoomQuantity = function(roomIndex) {
    if (window.bookingManager) {
        window.bookingManager.updateRoomQuantity(roomIndex);
    }
};

window.updateTotalPrice = function() {
    if (window.bookingManager) {
        window.bookingManager.updateTotalPrice();
    }
};

window.removeRoom = function(index) {
    if (window.bookingManager) {
        window.bookingManager.removeRoom(index);
    }
};

window.handleRoomTypeChange = function(index) {
    if (window.bookingManager) {
        window.bookingManager.handleRoomTypeChange(index);
    }
};

window.showHotelDetails = function() {
    if (window.bookingManager) {
        window.bookingManager.showHotelDetails();
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.bookingManager = new BookingManager();
});

document.addEventListener('DOMContentLoaded', function() {
    const modalOverlay = document.getElementById('roomDetailsModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    // Đảm bảo DOM element chứa tất cả các nút phòng nghỉ là đúng
    const roomSelectionGrid = document.getElementById('roomSelectionGrid'); 

    // DOM elements in modal
    const modalRoomName = document.getElementById('modalRoomName');
    const modalRoomSize = document.getElementById('modalRoomSize');
    const modalRoomImage = document.getElementById('modalRoomImage');
    const modalRoomDescription = document.getElementById('modalRoomDescription');
    // KHÔNG cần modalRoomAmenities vì bạn dùng dữ liệu mẫu tĩnh trong HTML

    // Mở modal
    // Dùng Event Delegation trên container chung để bắt sự kiện click nút chi tiết
    roomSelectionGrid.addEventListener('click', function(e) {
        // Tìm element gần nhất có class 'view-room-details'
        const detailButton = e.target.closest('.view-room-details'); 
        
        if (detailButton) {
            e.preventDefault(); // Ngăn chặn hành vi mặc định (nếu là thẻ <a>)
            const roomData = detailButton.dataset;
            
            // 1. Điền dữ liệu cơ bản
            modalRoomName.textContent = roomData.roomName;
            // Dùng ternary operator để xử lý nếu data-room-size không tồn tại
            modalRoomSize.textContent = roomData.roomSize || ''; 
            modalRoomImage.src = roomData.roomImage;
            modalRoomDescription.textContent = roomData.roomDescription;
            
            // 2. BỎ QUA LOGIC ĐIỀN TIỆN ÍCH ĐỘNG (vì đã dùng HTML tĩnh)
            
            // 3. Hiển thị modal
            modalOverlay.classList.add('visible');
        }
    });

    // Đóng modal
    closeModalBtn.addEventListener('click', closeRoomDetailsModal);
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            closeRoomDetailsModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.classList.contains('visible')) {
            closeRoomDetailsModal();
        }
    });

    function closeRoomDetailsModal() {
        modalOverlay.classList.remove('visible');
    }
});

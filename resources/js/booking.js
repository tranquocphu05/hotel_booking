/**
 * Booking System JavaScript Module
 * Handles room selection, voucher application, and price calculations
 */

class BookingManager {
    constructor() {
        // DOM element references
        this.giaMotDem = parseFloat(
            document
                .querySelector("[data-gia-mot-dem]")
                ?.getAttribute("data-gia-mot-dem") || 0
        );
        this.loaiPhongId =
            document
                .querySelector("[data-loai-phong-id]")
                ?.getAttribute("data-loai-phong-id") || 0;

        // Config injected from Blade
        this.bookingConfig = window.bookingConfig || {};
        this.routes = this.bookingConfig.routes || {};
        this.csrfToken =
            this.bookingConfig.csrfToken ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") ||
            "";
        this.defaultRoomCount = this.bookingConfig.defaultRoomCount || 0;

        // Voucher elements
        this.openVoucherLink = document.getElementById("openVoucherLink");
        this.openVoucherInlineBtn =
            document.getElementById("openVoucherInline");
        this.voucherActionText = document.getElementById("voucherActionText");
        this.voucherDisplayDiv = document.getElementById("voucherDisplay");
        this.voucherCodeInput = document.getElementById("voucherCode");
        this.discountValueInput = document.getElementById("discountValue");

        // Date elements
        this.checkinInput = document.getElementById("ngay_nhan_input");
        this.checkoutInput = document.getElementById("ngay_tra_input");

        // Price display elements
        this.soDemLuuTruElement = document.getElementById("so-dem-luu-tru");
        this.totalBeforeDiscountDiv = document.getElementById(
            "totalBeforeDiscount"
        );
        this.totalAfterDiscountDiv =
            document.getElementById("totalAfterDiscount");
        this.pricingMultiplierInfoDiv = document.getElementById(
            "pricingMultiplierInfo"
        );
        this.finalBookingPriceInput =
            document.getElementById("finalBookingPrice");

        // Room summary elements
        this.selectedRoomsSummaryChip = document.getElementById(
            "selectedRoomsSummary"
        );
        this.summaryRoomCount = document.getElementById("summaryRoomCount");

        // Form element
        this.finalBookingForm = document.getElementById("finalBookingForm");

        // State variables
        this.popupElement = null;
        this.currentDiscountPercent = parseFloat(
            this.discountValueInput?.value || 0
        );
        this.alertTimeout = null;
        this.isCompletingBooking = false;
        this.roomIndex = 1; // Start from 1 since first room is index 0
        this.roomTypeOptionsHtml = "";
        // Capacity and surcharge config
        this.maxAdultsPerRoom = 2; // fixed capacity per room
        // Các biến percent giữ lại để không phá vỡ code cũ, nhưng logic mới dùng giá cố định
        this.extraFeePercent = 0.2; // legacy: không còn được dùng trong tính toán
        this.childFeePercent = 0.5; // legacy
        this.infantFeePercent = 0.05; // legacy

        // Initialize
        this.init();
    }

    init() {
        // Restore voucher state
        this.restoreVoucherState();

        // Set up event listeners
        this.setupEventListeners();

        // Initialize room card quantities FIRST (this will render guest rows)
        this.initializeRoomCardQuantities();

        // Then initialize calculations AFTER quantities are set up
        this.tinhTongTien();

        // Update room availability and prices after a short delay
        setTimeout(() => {
            if (
                this.checkinInput &&
                this.checkoutInput &&
                this.checkinInput.value &&
                this.checkoutInput.value
            ) {
                this.updateAvailableCount();
                this.updateRoomCardAvailabilities();
            }

            // Update prices for all rooms
            const defaultRoomSelect =
                document.querySelector(".room-type-select");
            if (defaultRoomSelect && defaultRoomSelect.value) {
                this.updateRoomPrice(defaultRoomSelect);
            }

            // Update subtotals for all rooms
            document.querySelectorAll(".room-item").forEach((roomItem) => {
                const hiddenInput = roomItem.querySelector(
                    '.room-type-select[type="hidden"]'
                );
                if (hiddenInput) {
                    this.updateRoomPrice(hiddenInput);
                }
            });
        }, 100);
    }

    // === ROOM CARD QUANTITY & ADULTS MANAGEMENT ===
    initializeRoomCardQuantities() {
        // Initialize quantity buttons based on current values (don't reset to 0)
        let hasPreselectedRoom = false;

        document
            .querySelectorAll(".room-card-quantity, .room-card-quantity-modern")
            .forEach((input) => {
                // Keep the value from HTML (may be 1 if pre-selected from room list)
                const currentValue = parseInt(input.value) || 0;
                const roomId = input.dataset.roomId;

                // Update button states
                this.updateQuantityButtons(input);

                // If quantity > 0, update the card to show selected state and render guest rows
                if (currentValue > 0 && roomId) {
                    hasPreselectedRoom = true;
                    const roomCard = input.closest(".room-card");
                    if (roomCard) {
                        roomCard.classList.add("room-card--active");
                    }
                    // Render guest rows for this room type
                    this.renderGuestRows(roomId);
                }
            });
        // Initialize adults selectors to 2
        document.querySelectorAll(".room-card-adults").forEach((sel) => {
            if (!sel.value) sel.value = "2";
        });

        // Initialize hidden inputs
        this.updateRoomCardHiddenInputs();

        // If there's a preselected room, recalculate totals
        if (hasPreselectedRoom) {
            this.tinhTongTien();
        }
    }

    // Render per-room guest selector rows for a room type card
    // Now includes separate selectors for Adults (Người lớn), Children (Trẻ em 6-12), and Infants (Em bé 0-5)
    renderGuestRows(roomId) {
        const container = document.getElementById(
            `room_card_guest_rows_${roomId}`
        );
        const quantityInput = document.getElementById(
            `room_card_quantity_${roomId}`
        );
        const defaultAdultsSel = document.getElementById(
            `room_card_adults_${roomId}`
        );
        if (!container || !quantityInput) return;

        const qty = parseInt(quantityInput.value) || 0;

        // Preserve old values if possible
        const previousValues = {};
        container.querySelectorAll("[data-guest-row] select").forEach((sel) => {
            previousValues[sel.id] = sel.value;
        });

        if (qty <= 0) {
            container.innerHTML = "";
            container.classList.add("hidden");
            return;
        }

        // Get max capacity from data attributes (from database)
        const maxAdults = parseInt(quantityInput.dataset.maxAdults) || 2;
        const maxChildren = parseInt(quantityInput.dataset.maxChildren) || 2;
        const maxInfants = parseInt(quantityInput.dataset.maxInfants) || 2;

        // Lấy giá trị mặc định từ bookingConfig (từ trang chi tiết phòng) hoặc từ maxAdults
        const configAdults = this.bookingConfig.initialAdults;
        const configChildren = this.bookingConfig.initialChildren;
        const configInfants = this.bookingConfig.initialInfants;
        
        // Sử dụng giá trị từ config nếu có, không vượt quá max
        const defaultAdults = Math.min(
            parseInt(defaultAdultsSel?.value) || (configAdults !== undefined ? configAdults : maxAdults),
            maxAdults
        );
        const defaultChildren = Math.min(configChildren || 0, maxChildren);
        const defaultInfants = Math.min(configInfants || 0, maxInfants);

        // Get surcharge rates from room type data
        const childFee = parseFloat(quantityInput.dataset.childFee) || 0;
        const infantFee = parseFloat(quantityInput.dataset.infantFee) || 0;

        // Generate options for adults (0 to maxAdults)
        const adultsOptions = Array.from({ length: maxAdults + 1 }, (_, i) => 
            `<option value="${i}">${i}</option>`
        ).join('');

        // Generate options for children (0 to maxChildren)
        const childrenOptions = Array.from({ length: maxChildren + 1 }, (_, i) => 
            `<option value="${i}">${i}</option>`
        ).join('');

        // Generate options for infants (0 to maxInfants)
        const infantsOptions = Array.from({ length: maxInfants + 1 }, (_, i) => 
            `<option value="${i}">${i}</option>`
        ).join('');

        const rows = [];
        for (let i = 1; i <= qty; i++) {
            const adultsRowId = `room_card_guest_row_adults_${roomId}_${i}`;
            const childrenRowId = `room_card_guest_row_children_${roomId}_${i}`;
            const infantsRowId = `room_card_guest_row_infants_${roomId}_${i}`;

            const adultsValue =
                previousValues[adultsRowId] || String(defaultAdults);
            const childrenValue = previousValues[childrenRowId] || String(defaultChildren);
            const infantsValue = previousValues[infantsRowId] || String(defaultInfants);

            rows.push(`
                <div class="border-b border-dashed border-gray-300 py-2" data-guest-row>
                    <div class="text-sm text-gray-700 mb-2 font-bold">Chọn số người phòng ${i}</div>
                    <div class="flex gap-3">
                        <!-- Adults (Người lớn) -->
                        <div class="flex-1">
                            <div class="text-sm text-gray-700 mb-1">Người lớn</div>
                            <select id="${adultsRowId}"
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white appearance-none cursor-pointer"
                                    data-room-id="${roomId}"
                                    data-guest-type="adults"
                                    onchange="window.bookingManager.onGuestRowChange('${roomId}')">
                                ${adultsOptions}
                            </select>
                        </div>

                        <!-- Children (Trẻ em 6-11 tuổi) -->
                        <div class="flex-1">
                            <div class="text-sm text-gray-700 mb-1">Trẻ em 6-11</div>
                            <select id="${childrenRowId}"
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white appearance-none cursor-pointer"
                                    data-room-id="${roomId}"
                                    data-guest-type="children"
                                    onchange="window.bookingManager.onGuestRowChange('${roomId}')">
                                ${childrenOptions}
                            </select>
                        </div>

                        <!-- Infants (Em bé 0-5 tuổi) -->
                        <div class="flex-1">
                            <div class="text-sm text-gray-700 mb-1">Em bé 0-5</div>
                            <select id="${infantsRowId}"
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white appearance-none cursor-pointer"
                                    data-room-id="${roomId}"
                                    data-guest-type="infants"
                                    onchange="window.bookingManager.onGuestRowChange('${roomId}')">
                                ${infantsOptions}
                            </select>
                        </div>
                    </div>
                </div>
            `);
        }

        container.innerHTML = rows.join("");
        container.classList.remove("hidden");

        // Apply preserved/default values
        for (let i = 1; i <= qty; i++) {
            const adultsRowId = `room_card_guest_row_adults_${roomId}_${i}`;
            const childrenRowId = `room_card_guest_row_children_${roomId}_${i}`;
            const infantsRowId = `room_card_guest_row_infants_${roomId}_${i}`;

            const adultsSel = document.getElementById(adultsRowId);
            const childrenSel = document.getElementById(childrenRowId);
            const infantsSel = document.getElementById(infantsRowId);

            if (adultsSel) {
                adultsSel.value =
                    previousValues[adultsRowId] || String(defaultAdults);
            }
            if (childrenSel) {
                childrenSel.value = previousValues[childrenRowId] || String(defaultChildren);
            }
            if (infantsSel) {
                infantsSel.value = previousValues[infantsRowId] || String(defaultInfants);
            }
        }

        // Recalculate after render
        this.tinhTongTien();
        this.updateRoomCardHiddenInputs();
    }

    onGuestRowChange(roomId) {
        this.tinhTongTien();
        this.updateRoomCardHiddenInputs();
    }

    updateQuantityButtons(quantityInput) {
        const roomId = quantityInput.dataset.roomId;
        const currentValue = parseInt(quantityInput.value) || 0;
        const maxQuantity = parseInt(quantityInput.dataset.maxQuantity) || 0;

        const decreaseBtn = document.querySelector(
            `button[onclick="decreaseRoomCardQuantity('${roomId}')"]`
        );
        const increaseBtn = document.querySelector(
            `button[onclick="increaseRoomCardQuantity('${roomId}')"]`
        );

        if (decreaseBtn) {
            decreaseBtn.disabled = currentValue <= 0;
        }

        if (increaseBtn) {
            increaseBtn.disabled = currentValue >= maxQuantity;
        }
    }

    updateRoomCardAvailabilities() {
        const checkin = this.checkinInput?.value;
        const checkout = this.checkoutInput?.value;

        if (!checkin || !checkout) {
            // Show default availability and ensure dropdown options are correct
            document
                .querySelectorAll(
                    ".room-card-quantity, .room-card-quantity-modern"
                )
                .forEach((el) => {
                    const roomId = el.dataset.roomId;
                    const maxQuantity = parseInt(el.dataset.maxQuantity) || 0;
                    const availabilityEl = document.getElementById(
                        `availability_${roomId}`
                    );
                    if (availabilityEl) {
                        availabilityEl.textContent = `Còn ${maxQuantity} phòng`;
                        availabilityEl.classList.remove("unavailable");
                    }

                    // If it's a select, rebuild options 0..maxQuantity and clamp current value
                    if (el.tagName === "SELECT") {
                        const current = parseInt(el.value) || 0;
                        const newMax = Math.max(0, maxQuantity);
                        const optionsHtml = Array.from(
                            { length: newMax + 1 },
                            (_, i) => `<option value="${i}">${i} Phòng</option>`
                        ).join("");
                        el.innerHTML = optionsHtml;
                        el.value = Math.min(current, newMax).toString();
                    }
                });
            return;
        }

        // Check availability for each room type
        document
            .querySelectorAll(".room-card-quantity, .room-card-quantity-modern")
            .forEach((input) => {
                const roomId = input.dataset.roomId;
                this.checkRoomCardAvailability(roomId, checkin, checkout);
            });
    }

    checkRoomCardAvailability(roomId, checkin, checkout) {
        if (!this.routes.availableCount) return;

        // Format dates for display
        const formatDate = (dateStr) => {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, "0");
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        };

        fetch(this.routes.availableCount, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": this.csrfToken,
            },
            body: JSON.stringify({
                loai_phong_id: roomId,
                checkin: checkin,
                checkout: checkout,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const availableCount = Math.max(0, data.availableCount);
                    const quantityInput = document.getElementById(
                        `room_card_quantity_${roomId}`
                    );
                    const availabilityInfo = document.getElementById(
                        `availability_info_${roomId}`
                    );
                    const availabilityText = document.getElementById(
                        `availability_${roomId}`
                    );
                    const dateRangeInfo = document.getElementById(
                        `date_range_${roomId}`
                    );

                    if (quantityInput) {
                        quantityInput.dataset.maxQuantity = availableCount;

                        if (quantityInput.tagName === "SELECT") {
                            const current = parseInt(quantityInput.value) || 0;
                            const newMax = Math.max(0, availableCount);
                            const optionsHtml = Array.from(
                                { length: newMax + 1 },
                                (_, i) =>
                                    `<option value="${i}">${i} Phòng</option>`
                            ).join("");
                            quantityInput.innerHTML = optionsHtml;
                            const clamped = Math.min(current, newMax);
                            quantityInput.value = clamped.toString();
                            this.updateRoomCardQuantity(roomId);
                        } else {
                            // For non-select inputs, set HTML max and clamp
                            quantityInput.setAttribute("max", availableCount);
                            const currentQuantity =
                                parseInt(quantityInput.value) || 0;
                            if (currentQuantity > availableCount) {
                                quantityInput.value = availableCount;
                                this.updateRoomCardQuantity(roomId);
                            }
                            this.updateQuantityButtons(quantityInput);
                        }
                    }

                    if (availabilityText && dateRangeInfo) {
                        const statusDiv = availabilityText.closest(
                            ".availability-status"
                        );

                        if (availableCount > 0) {
                            availabilityText.textContent = `Còn ${availableCount} phòng`;
                            statusDiv.classList.remove("unavailable");
                            statusDiv.classList.add("available");

                            // Update icon
                            const icon = statusDiv.querySelector("i");
                            if (icon) {
                                icon.className = "fas fa-bed text-green-500";
                            }

                            // Update date range info
                            dateRangeInfo.innerHTML = `
                            <small class="text-green-600">
                                <i class="fas fa-calendar-check"></i>
                                Từ ${formatDate(checkin)} đến ${formatDate(
                                checkout
                            )}
                            </small>
                        `;
                        } else {
                            availabilityText.textContent = "Hết phòng";
                            statusDiv.classList.remove("available");
                            statusDiv.classList.add("unavailable");

                            // Update icon
                            const icon = statusDiv.querySelector("i");
                            if (icon) {
                                icon.className =
                                    "fas fa-times-circle text-red-500";
                            }

                            // Update date range info
                            dateRangeInfo.innerHTML = `
                            <small class="text-red-600">
                                <i class="fas fa-calendar-times"></i>
                                Không có phòng trống trong khoảng thời gian này
                            </small>
                        `;
                        }
                    }
                }
            })
            .catch((error) => {
                console.error("Error checking room availability:", error);
            });
    }
    formatCurrency(number) {
        return (
            Math.round(number).toLocaleString("vi-VN", {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }) + " VNĐ"
        );
    }

    getDatesAndDays() {
        const checkinValue = this.checkinInput?.value;
        const checkoutValue = this.checkoutInput?.value;

        const checkinDate = new Date(checkinValue);
        const checkoutDate = new Date(checkoutValue);

        let soDem;

        if (
            checkinDate instanceof Date &&
            !isNaN(checkinDate) &&
            checkoutDate instanceof Date &&
            !isNaN(checkoutDate) &&
            checkoutDate > checkinDate
        ) {
            const diffTime = Math.abs(checkoutDate - checkinDate);
            soDem = Math.round(diffTime / (1000 * 60 * 60 * 24));
        } else {
            soDem = 1;
        }

        return {
            checkinValue,
            checkoutValue,
            soDem,
        };
    }

    // Helpers to mirror server-side holiday/weekend logic (fixed solar holidays)
    isHoliday(date) {
        const year = date.getFullYear();
        const pad = (n) => (n < 10 ? "0" + n : "" + n);
        const key = `${year}-${pad(date.getMonth() + 1)}-${pad(
            date.getDate()
        )}`;
        const holidays = [
            `${year}-01-01`,
            `${year}-04-30`,
            `${year}-05-01`,
            `${year}-09-02`,
        ];
        return holidays.includes(key);
    }

    getMultiplierForDate(date) {
        if (this.isHoliday(date)) {
            return 1.25;
        }
        const day = date.getDay(); // 0 = Sun, 6 = Sat
        if (day === 0 || day === 6) {
            return 1.15;
        }
        return 1.0;
    }

    getDiscountPercentFromCard(cardElement) {
        const discountElement = cardElement.querySelector(
            ".font-semibold.text-gray-800.text-base"
        );
        if (!discountElement) return 0;

        const text = discountElement.textContent || "";
        const match = text.match(/Giảm\s*(\d+)\s*%/i);

        if (match && match[1]) {
            return parseFloat(match[1].trim());
        }
        return 0;
    }

    // === VOUCHER MANAGEMENT ===
    saveVoucherState() {
        try {
            sessionStorage.setItem(
                "appliedVoucherCode",
                this.voucherCodeInput.value
            );
            sessionStorage.setItem(
                "appliedDiscountPercent",
                this.currentDiscountPercent.toString()
            );
            sessionStorage.setItem("appliedVoucherRoomId", this.loaiPhongId);
        } catch (e) {
            console.warn(
                "Không thể lưu trạng thái voucher vào sessionStorage:",
                e
            );
        }
    }

    clearSavedVoucherState() {
        try {
            sessionStorage.removeItem("appliedVoucherCode");
            sessionStorage.removeItem("appliedDiscountPercent");
            sessionStorage.removeItem("appliedVoucherRoomId");
        } catch (e) {
            console.warn(
                "Không thể xóa trạng thái voucher khỏi sessionStorage:",
                e
            );
        }
    }

    restoreVoucherState() {
        try {
            const savedCode = sessionStorage.getItem("appliedVoucherCode");
            const savedPercent =
                parseFloat(sessionStorage.getItem("appliedDiscountPercent")) ||
                0;
            const savedRoomId = sessionStorage.getItem("appliedVoucherRoomId");

            if (
                savedCode &&
                savedPercent > 0 &&
                savedRoomId === this.loaiPhongId
            ) {
                this.currentDiscountPercent = savedPercent;
                this.voucherCodeInput.value = savedCode;
                this.discountValueInput.value = savedPercent.toString();
                return true;
            }
        } catch (e) {
            console.warn(
                "Không thể khôi phục trạng thái voucher từ sessionStorage:",
                e
            );
        }
        return false;
    }

    clearVoucher() {
        this.currentDiscountPercent = 0;
        this.voucherCodeInput.value = "";
        this.discountValueInput.value = "0";
        this.clearSavedVoucherState();
        this.tinhTongTien();

        const popup = this.popupElement;
        if (popup) {
            const searchInput = popup.querySelector("#popupVoucherCodeInput");
            if (searchInput) searchInput.value = "";
            this.displayAlert(popup, "");
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
            const closeBtn = popup.querySelector("#closeVoucherPopup");
            if (closeBtn) {
                closeBtn.dispatchEvent(new Event("click"));
            }
        }
    }

    displayAlert(popup, message, isError = false) {
        const alertContainer = popup.querySelector("#voucherAlertMessage");
        const pTag = alertContainer ? alertContainer.querySelector("p") : null;
        if (!alertContainer || !pTag) return;

        if (this.alertTimeout) {
            clearTimeout(this.alertTimeout);
            this.alertTimeout = null;
        }

        if (!message) {
            alertContainer.classList.add("hidden");
            pTag.textContent = "";
            return;
        }

        pTag.innerHTML = message;
        alertContainer.classList.remove("hidden");

        alertContainer.className = "mt-2";
        pTag.className = isError
            ? "text-sm py-2 px-3 rounded-lg bg-red-100 text-red-700 font-medium"
            : "text-sm py-2 px-3 rounded-lg bg-sky-100 text-sky-800 font-medium";

        this.alertTimeout = setTimeout(() => {
            alertContainer.classList.add("hidden");
            this.alertTimeout = null;
        }, 5000);
    }

    // === PRICE CALCULATIONS ===
    tinhTongTien() {
        // Tính tổng giá từ room cards với multiplier theo từng ngày + phụ phí khách vượt
        const { checkinValue, checkoutValue, soDem } = this.getDatesAndDays();
        let totalBeforeDiscountAmount = 0;
        let totalExtraFee = 0;
        let totalChildFee = 0;
        let totalInfantFee = 0;

        let weekdayNights = 0;
        let weekendNights = 0;
        let holidayNights = 0;

        const checkinDate = new Date(checkinValue);
        const checkoutDate = new Date(checkoutValue);

        // Tính từ room cards
        document
            .querySelectorAll(".room-card-quantity, .room-card-quantity-modern")
            .forEach((quantityInput) => {
                const quantity = parseInt(quantityInput.value) || 0;
                if (
                    quantity > 0 &&
                    !isNaN(checkinDate) &&
                    !isNaN(checkoutDate)
                ) {
                    const price =
                        parseFloat(quantityInput.dataset.roomPrice) || 0;
                    const roomId = quantityInput.dataset.roomId;
                    const adultsSel = document.getElementById(
                        `room_card_adults_${roomId}`
                    );
                    const defaultAdults =
                        parseInt(adultsSel?.value || this.maxAdultsPerRoom) ||
                        this.maxAdultsPerRoom;

                    // Sum adults từ các hàng guest, dùng để tính khách vượt
                    let sumAdults = 0;
                    let sumChildren = 0;
                    let sumInfants = 0;
                    const rowsContainer = document.getElementById(
                        `room_card_guest_rows_${roomId}`
                    );

                    if (rowsContainer) {
                        // Lấy tổng người lớn từ các selector
                        const adultSelects = rowsContainer.querySelectorAll(
                            '[data-guest-type="adults"]'
                        );
                        adultSelects.forEach((sel) => {
                            sumAdults += parseInt(sel.value) || 0;
                        });

                        // Lấy tổng trẻ em từ các selector
                        const childSelects = rowsContainer.querySelectorAll(
                            '[data-guest-type="children"]'
                        );
                        childSelects.forEach((sel) => {
                            sumChildren += parseInt(sel.value) || 0;
                        });

                        // Lấy tổng em bé từ các selector
                        const infantSelects = rowsContainer.querySelectorAll(
                            '[data-guest-type="infants"]'
                        );
                        infantSelects.forEach((sel) => {
                            sumInfants += parseInt(sel.value) || 0;
                        });
                    }

                    // Nếu không có dòng guest nào, mặc định số người lớn = số người chuẩn của phòng * số phòng
                    if (
                        sumAdults === 0 &&
                        !rowsContainer?.querySelector("[data-guest-row]")
                    ) {
                        sumAdults = defaultAdults * quantity;
                    }

                    const capacity = quantity * this.maxAdultsPerRoom;
                    const extraGuestsForType = Math.max(
                        0,
                        sumAdults - capacity
                    );

                    // Duyệt từng ngày để áp dụng multiplier cho GIÁ PHÒNG, nhưng phụ phí thêm người là cố định, không nhân multiplier
                    const current = new Date(checkinDate.getTime());
                    while (current < checkoutDate) {
                        const m = this.getMultiplierForDate(current);
                        if (this.isHoliday(current)) {
                            holidayNights++;
                        } else {
                            const d = current.getDay();
                            if (d === 0 || d === 6) weekendNights++;
                            else weekdayNights++;
                        }

                        // Tiền phòng cơ bản theo hệ số ngày
                        totalBeforeDiscountAmount += price * m * quantity;

                        // Phụ phí khách vượt: giá cố định 300,000 VND / người lớn / đêm, KHÔNG áp dụng multiplier
                        if (extraGuestsForType > 0) {
                            const basePerAdultPerNight = 300000;
                            totalExtraFee +=
                                extraGuestsForType * basePerAdultPerNight;
                        }

                        // Phụ phí trẻ em: giá cố định 150,000 VND / trẻ em / đêm, KHÔNG áp dụng multiplier
                        if (sumChildren > 0) {
                            const basePerChildPerNight = 150000;
                            totalChildFee += sumChildren * basePerChildPerNight;
                        }

                        // Phụ phí em bé: miễn phí hoàn toàn, không cộng thêm gì
                        // totalInfantFee luôn = 0 theo policy mới

                        current.setDate(current.getDate() + 1);
                    }
                }
            });

        // Cộng tất cả phụ phí vào tổng
        if (totalExtraFee > 0) {
            totalBeforeDiscountAmount += totalExtraFee;
        }
        if (totalChildFee > 0) {
            totalBeforeDiscountAmount += totalChildFee;
        }
        if (totalInfantFee > 0) {
            totalBeforeDiscountAmount += totalInfantFee;
        }

        const discountPercent = this.currentDiscountPercent;

        let discountAmount = 0;
        let totalAfterDiscount = totalBeforeDiscountAmount;

        if (discountPercent > 0) {
            discountAmount =
                totalBeforeDiscountAmount * (discountPercent / 100);
            totalAfterDiscount = Math.max(
                0,
                totalBeforeDiscountAmount - discountAmount
            );
        }

        // Cập nhật giao diện chính
        this.soDemLuuTruElement.textContent = `Số đêm: ${soDem} đêm`;

        // Hiển thị breakdown ngày thường/cuối tuần/lễ nếu có element
        if (this.pricingMultiplierInfoDiv) {
            if (weekdayNights + weekendNights + holidayNights > 0) {
                this.pricingMultiplierInfoDiv.innerHTML =
                    `Giá đã áp dụng: ` +
                    `${weekdayNights} đêm thường, ` +
                    `${weekendNights} đêm cuối tuần (+15%), ` +
                    `${holidayNights} đêm lễ (+25%).`;
                this.pricingMultiplierInfoDiv.classList.remove("hidden");
            } else {
                this.pricingMultiplierInfoDiv.classList.add("hidden");
            }
        }

        // Dòng hiển thị phụ phí khách vượt
        let extraFeeDisplay = document.getElementById("extraGuestFeeDisplay");
        if (!extraFeeDisplay) {
            extraFeeDisplay = document.createElement("div");
            extraFeeDisplay.id = "extraGuestFeeDisplay";
            extraFeeDisplay.className = "text-sm text-amber-700 mb-1 hidden";
            this.totalAfterDiscountDiv.parentNode.insertBefore(
                extraFeeDisplay,
                this.totalAfterDiscountDiv
            );
        }

        if (totalExtraFee > 0) {
            extraFeeDisplay.innerHTML = `Phụ phí thêm khách: <span class="font-semibold">+${this.formatCurrency(
                totalExtraFee
            )}</span>`;
            extraFeeDisplay.classList.remove("hidden");
        } else {
            extraFeeDisplay.classList.add("hidden");
        }
        // Remove any existing duplicate surcharge displays (they are shown in summary instead)
        const existingExtraFeeDisplay = document.getElementById(
            "extraGuestFeeDisplay"
        );
        const existingChildFeeDisplay =
            document.getElementById("childFeeDisplay");
        const existingInfantFeeDisplay =
            document.getElementById("infantFeeDisplay");
        if (existingExtraFeeDisplay) existingExtraFeeDisplay.remove();
        if (existingChildFeeDisplay) existingChildFeeDisplay.remove();
        if (existingInfantFeeDisplay) existingInfantFeeDisplay.remove();

        // Tìm element hiển thị discount amount
        let discountAmountDisplay = document.getElementById(
            "discountAmountDisplay"
        );
        if (!discountAmountDisplay) {
            discountAmountDisplay = document.createElement("div");
            discountAmountDisplay.id = "discountAmountDisplay";
            discountAmountDisplay.className =
                "text-sm text-green-600 mb-1 hidden";
            this.totalAfterDiscountDiv.parentNode.insertBefore(
                discountAmountDisplay,
                this.totalAfterDiscountDiv
            );
        }

        if (discountPercent > 0) {
            const currentCode = this.voucherCodeInput.value || "VOUCHER";

            // Hiển thị giá gốc
            this.totalBeforeDiscountDiv.innerHTML = `<span class="text-gray-600">Giá gốc:</span> <span class="line-through text-gray-500">${this.formatCurrency(
                totalBeforeDiscountAmount
            )}</span>`;
            this.totalBeforeDiscountDiv.classList.remove("hidden");

            // Hiển thị số tiền giảm
            discountAmountDisplay.innerHTML = `<span class="text-green-600">Giảm giá:</span> <span class="font-semibold text-green-600">-${this.formatCurrency(
                discountAmount
            )}</span>`;
            discountAmountDisplay.classList.remove("hidden");

            // Cập nhật tổng tiền sau giảm giá
            this.totalAfterDiscountDiv.innerHTML = `Tổng: ${this.formatCurrency(
                totalAfterDiscount
            )}`;
            this.totalAfterDiscountDiv.classList.add(
                "text-xl",
                "font-bold",
                "text-red-600"
            );

            // Cập nhật giao diện cho LINK TEXT
            this.voucherActionText.textContent = `Đã áp dụng mã: ${currentCode}`;

            this.openVoucherLink.classList.remove(
                "text-green-600",
                "hover:text-green-800"
            );
            this.openVoucherLink.classList.add(
                "text-indigo-600",
                "hover:text-indigo-800"
            );

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
            this.voucherDisplayDiv.classList.remove("hidden");

            const clearLink =
                this.voucherDisplayDiv.querySelector("#voucherClearLink");
            if (clearLink) {
                const newClearLink = clearLink.cloneNode(true);
                clearLink.parentNode.replaceChild(newClearLink, clearLink);
                newClearLink.addEventListener("click", () => {
                    this.clearVoucher();
                });
            }
        } else {
            // Không có voucher
            this.totalBeforeDiscountDiv.classList.add("hidden");
            discountAmountDisplay.classList.add("hidden");

            // Cập nhật tổng tiền
            this.totalAfterDiscountDiv.innerHTML = `Tổng: ${this.formatCurrency(
                totalAfterDiscount
            )}`;
            this.totalAfterDiscountDiv.classList.add(
                "text-xl",
                "font-bold",
                "text-red-600"
            );

            // Reset link text
            this.voucherActionText.textContent = "Chọn hoặc nhập mã giảm giá";

            this.openVoucherLink.classList.remove(
                "text-green-600",
                "hover:text-green-800"
            );
            this.openVoucherLink.classList.add(
                "text-indigo-600",
                "hover:text-indigo-800"
            );

            this.voucherDisplayDiv.classList.add("hidden");
        }

        this.finalBookingPriceInput.value = Math.round(totalAfterDiscount);
        this.discountValueInput.value = this.currentDiscountPercent;

        // Cập nhật summary
        this.updateRoomsSummary();

        return totalBeforeDiscountAmount;
    }

    updateRoomsSummary() {
        const { soDem } = this.getDatesAndDays();
        const roomsSummary = [];
        let totalRoomsSelected = 0;

        // Collect only from room cards (selected rooms section removed)
        document
            .querySelectorAll(".room-card-quantity, .room-card-quantity-modern")
            .forEach((quantityInput) => {
                const quantity = parseInt(quantityInput.value) || 0;
                if (quantity > 0) {
                    const roomName = quantityInput.dataset.roomName || "";
                    const price =
                        parseFloat(quantityInput.dataset.roomPrice) || 0;
                    const roomId = quantityInput.dataset.roomId;
                    // Legacy: child/infant fee rates theo % giá phòng (không còn dùng trong tính toán chính)
                    const childFeeRate = price * this.childFeePercent;
                    const infantFeeRate = price * this.infantFeePercent;
                    const adultsSel = document.getElementById(
                        `room_card_adults_${roomId}`
                    );
                    const defaultAdults =
                        parseInt(adultsSel?.value || this.maxAdultsPerRoom) ||
                        this.maxAdultsPerRoom;
                    totalRoomsSelected += quantity;

                    // Compute guests from per-room rows
                    const { soDem } = this.getDatesAndDays();
                    let sumAdults = 0;
                    let sumChildren = 0;
                    let sumInfants = 0;
                    const rowsContainer = document.getElementById(
                        `room_card_guest_rows_${roomId}`
                    );

                    if (rowsContainer) {
                        // Get adults from adult selectors
                        const adultSelects = rowsContainer.querySelectorAll(
                            '[data-guest-type="adults"]'
                        );
                        adultSelects.forEach((sel) => {
                            sumAdults += parseInt(sel.value) || 0;
                        });

                        // Get children from children selectors
                        const childSelects = rowsContainer.querySelectorAll(
                            '[data-guest-type="children"]'
                        );
                        childSelects.forEach((sel) => {
                            sumChildren += parseInt(sel.value) || 0;
                        });

                        // Get infants from infant selectors
                        const infantSelects = rowsContainer.querySelectorAll(
                            '[data-guest-type="infants"]'
                        );
                        infantSelects.forEach((sel) => {
                            sumInfants += parseInt(sel.value) || 0;
                        });
                    }

                    // If no guest rows, use default adults
                    if (
                        sumAdults === 0 &&
                        !rowsContainer?.querySelector("[data-guest-row]")
                    ) {
                        sumAdults = defaultAdults * quantity;
                    }

                    const capacity = quantity * this.maxAdultsPerRoom;
                    const extraGuestsForType = Math.max(
                        0,
                        sumAdults - capacity
                    );

                    // Tính phụ phí thêm người theo số đêm, KHÔNG nhân multiplier
                    const { checkinValue, checkoutValue } =
                        this.getDatesAndDays();
                    const checkinDate = new Date(checkinValue);
                    const checkoutDate = new Date(checkoutValue);

                    let extraFeeForType = 0;
                    let childFeeForType = 0;
                    let infantFeeForType = 0;

                    const oneDayMs = 24 * 60 * 60 * 1000;
                    const nights = Math.max(
                        1,
                        Math.round((checkoutDate - checkinDate) / oneDayMs)
                    );

                    // Phụ phí người lớn vượt: 300,000 VND / người / đêm (không nhân multiplier)
                    if (extraGuestsForType > 0) {
                        const basePerAdultPerNight = 300000;
                        extraFeeForType =
                            extraGuestsForType * basePerAdultPerNight * nights;
                    }

                    // Phụ phí trẻ em: 150,000 VND / người / đêm (không nhân multiplier)
                    if (sumChildren > 0) {
                        const basePerChildPerNight = 150000;
                        childFeeForType =
                            sumChildren * basePerChildPerNight * nights;
                    }

                    // Phụ phí em bé: miễn phí

                    roomsSummary.push({
                        name: roomName,
                        quantity: quantity,
                        price: price,
                        roomId: roomId,
                        adults: sumAdults,
                        children: sumChildren,
                        infants: sumInfants,
                        extraFee: extraFeeForType,
                        childFee: childFeeForType,
                        infantFee: infantFeeForType,
                        childFeeRate: childFeeRate,
                        infantFeeRate: infantFeeRate,
                        source: "card",
                    });
                }
            });

        // Update room summary list
        const roomsSummaryList = document.getElementById("roomsSummaryList");
        if (roomsSummaryList) {
            if (roomsSummary.length > 0) {
                roomsSummaryList.classList.remove("summary-room-list--empty");
                roomsSummaryList.innerHTML = roomsSummary
                    .map((room) => {
                        const removeAction = `onclick="window.bookingManager.clearRoomCardQuantity('${room.roomId}')"`;

                        // Build guest info display
                        let guestInfoHtml = "";
                        if (
                            room.adults > 0 ||
                            room.children > 0 ||
                            room.infants > 0
                        ) {
                            const guestParts = [];
                            if (room.adults > 0) {
                                guestParts.push(
                                    `<span class="text-blue-600"><i class="fas fa-user text-xs"></i> ${room.adults} người lớn</span>`
                                );
                            }
                            if (room.children > 0) {
                                guestParts.push(
                                    `<span class="text-green-600"><i class="fas fa-child text-xs"></i> ${room.children} trẻ em</span>`
                                );
                            }
                            if (room.infants > 0) {
                                guestParts.push(
                                    `<span class="text-pink-600"><i class="fas fa-baby text-xs"></i> ${room.infants} em bé</span>`
                                );
                            }
                            guestInfoHtml = `<div class="text-xs mt-1 flex flex-wrap gap-2">${guestParts.join(
                                ""
                            )}</div>`;
                        }

                        // Build surcharge display with breakdown
                        let surchargeHtml = "";
                        const surchargeItems = [];

                        if (room.extraFee && room.extraFee > 0) {
                            surchargeItems.push(
                                `<div class="flex items-center gap-1">
            <i class="fas fa-user-plus text-amber-500 text-xs"></i>
            <span>Thêm người lớn: +${this.formatCurrency(room.extraFee)}</span>
        </div>`
                            );
                        }

                        if (room.childFee && room.childFee > 0) {
                            surchargeItems.push(
                                `<div class="flex items-center gap-1">
            <i class="fas fa-child text-green-500 text-xs"></i>
            <span>Thêm trẻ em: +${this.formatCurrency(room.childFee)}</span>
        </div>`
                            );
                        }

                        if (room.infantFee && room.infantFee > 0) {
                            surchargeItems.push(
                                `<div class="flex items-center gap-1">
            <i class="fas fa-baby text-pink-500 text-xs"></i>
            <span>Thêm em bé: +${this.formatCurrency(room.infantFee)}</span>
        </div>`
                            );
                        }

                        if (surchargeItems.length > 0) {
                            surchargeHtml = `<div class="text-xs text-gray-700 mt-2 space-y-1 bg-orange-50 rounded-md p-2 border border-orange-100">${surchargeItems.join(
                                ""
                            )}</div>`;
                        }

                        return `<div class="summary-room-line border-b border-gray-100 pb-3 mb-3">
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-800">${
                                    room.name
                                }</p>
                                <div class="summary-room-actions flex items-center gap-2">
                                    <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full text-xs font-medium">x${
                                        room.quantity
                                    } phòng</span>
                                    <button type="button" class="summary-room-remove text-red-400 hover:text-red-600 transition-colors" aria-label="Xóa ${
                                        room.name
                                    }" ${removeAction}>
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-gray-500">${this.formatCurrency(
                                room.price
                            )} / đêm</small>
                            ${guestInfoHtml}
                            ${surchargeHtml}
                        </div>
                    </div>`;
                    })
                    .join("");

                if (this.selectedRoomsSummaryChip) {
                    this.selectedRoomsSummaryChip.classList.remove("hidden");
                }
                if (this.summaryRoomCount) {
                    this.summaryRoomCount.textContent = totalRoomsSelected;
                }
            } else {
                roomsSummaryList.classList.add("summary-room-list--empty");
                roomsSummaryList.innerHTML =
                    '<p class="summary-room-empty">Chưa có phòng nào</p>';
                if (this.selectedRoomsSummaryChip) {
                    this.selectedRoomsSummaryChip.classList.add("hidden");
                }
                if (this.summaryRoomCount) {
                    this.summaryRoomCount.textContent = 0;
                }
            }
        }
    }
    updateRoomAvailability(roomIndex, loaiPhongId, checkin, checkout) {
        if (!checkin || !checkout) {
            return;
        }

        // Validate dates
        if (new Date(checkout) <= new Date(checkin)) {
            return;
        }

        if (!this.routes.availableCount) {
            console.error("Booking route for availability is missing.");
            return;
        }

        // Format date for display
        const formatDate = (dateStr) => {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, "0");
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        };

        fetch(this.routes.availableCount, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": this.csrfToken,
            },
            body: JSON.stringify({
                loai_phong_id: loaiPhongId,
                checkin: checkin,
                checkout: checkout,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const availableCount = Math.max(0, data.availableCount);

                    // Update availability display
                    const availabilityEl = document.getElementById(
                        `room_availability_${roomIndex}`
                    );
                    if (availabilityEl) {
                        availabilityEl.innerHTML = `
                        <i class="fas fa-bed text-blue-500"></i> Còn ${availableCount} phòng trống
                        <span class="text-blue-500 text-xs">(từ ${formatDate(
                            checkin
                        )} đến ${formatDate(checkout)})</span>
                    `;
                    }

                    // Update data-max and max quantity
                    const quantityInput = document.getElementById(
                        `room_quantity_${roomIndex}`
                    );
                    if (quantityInput) {
                        quantityInput.setAttribute("data-max", availableCount);
                        const maxQuantityEl = document.getElementById(
                            `max_quantity_${roomIndex}`
                        );
                        if (maxQuantityEl) {
                            maxQuantityEl.textContent = `${availableCount}`;
                        }
                        // Reset quantity if it exceeds max
                        const currentQuantity =
                            parseInt(quantityInput.value) || 1;
                        if (
                            currentQuantity > availableCount &&
                            availableCount > 0
                        ) {
                            quantityInput.value = availableCount;
                            this.updateRoomQuantity(roomIndex);
                        }
                    }
                } else {
                    console.error("Error:", data.message);
                }
            })
            .catch((error) => {
                console.error("Error updating available count:", error);
            });
    }

    updateAvailableCount() {
        const checkin = this.checkinInput?.value;
        const checkout = this.checkoutInput?.value;

        if (!checkin || !checkout) {
            // Show default message for all rooms
            document.querySelectorAll(".room-item").forEach((roomItem) => {
                const roomIndex = roomItem.getAttribute("data-room-index");
                const availabilityEl = document.getElementById(
                    `room_availability_${roomIndex}`
                );
                if (availabilityEl) {
                    // Get default room count from data attribute or select
                    const select = roomItem.querySelector(".room-type-select");
                    let defaultCount = 0;
                    if (select && select.tagName === "SELECT" && select.value) {
                        const selectedOption =
                            select.options[select.selectedIndex];
                        defaultCount =
                            parseInt(
                                selectedOption.getAttribute(
                                    "data-so-luong-trong"
                                )
                            ) || 0;
                    } else if (roomIndex === "0") {
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
        document.querySelectorAll(".room-item").forEach((roomItem) => {
            const roomIndex = roomItem.getAttribute("data-room-index");
            const select = roomItem.querySelector(".room-type-select");

            if (select && select.value) {
                let loaiPhongId = null;

                if (select.tagName === "SELECT") {
                    loaiPhongId = select.value;
                } else if (select.type === "hidden") {
                    loaiPhongId = select.value;
                }

                if (loaiPhongId) {
                    this.updateRoomAvailability(
                        roomIndex,
                        loaiPhongId,
                        checkin,
                        checkout
                    );
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

            if (
                checkinDate instanceof Date &&
                !isNaN(checkinDate) &&
                checkoutDate instanceof Date &&
                !isNaN(checkoutDate) &&
                checkoutDate > checkinDate
            ) {
                const diffTime = Math.abs(checkoutDate - checkinDate);
                const soDem = Math.round(diffTime / (1000 * 60 * 60 * 24));

                // Format dates
                const formatDate = (date) => {
                    const day = String(date.getDate()).padStart(2, "0");
                    const month = String(date.getMonth() + 1).padStart(2, "0");
                    const year = date.getFullYear();
                    return `${day}/${month}/${year}`;
                };

                const summaryDateText = `${formatDate(
                    checkinDate
                )} - ${formatDate(checkoutDate)} (${soDem} đêm)`;

                // Update summary element
                const summaryDateElement =
                    document.querySelector(".summary-date");
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
            this.openVoucherInlineBtn.addEventListener("click", (e) => {
                e.preventDefault();
                this.openVoucherLink.click();
            });
        }

        if (this.openVoucherLink) {
            this.openVoucherLink.addEventListener("click", (e) => {
                e.preventDefault();
                this.openVoucherPopup();
            });
        }

        // Date change events
        if (this.checkinInput) {
            this.checkinInput.addEventListener("change", () => {
                this.clearVoucher();
                this.tinhTongTien();
                this.updateAvailableCount();
                this.updateRoomCardAvailabilities();
                this.updateSummaryDate();
                // Update prices for all rooms
                document
                    .querySelectorAll(".room-type-select")
                    .forEach((select) => {
                        if (select.value) {
                            this.updateRoomPrice(select);
                        }
                    });
            });
        }

        if (this.checkoutInput) {
            this.checkoutInput.addEventListener("change", () => {
                this.clearVoucher();
                this.tinhTongTien();
                this.updateAvailableCount();
                this.updateRoomCardAvailabilities();
                this.updateSummaryDate();
                // Update prices for all rooms
                document
                    .querySelectorAll(".room-type-select")
                    .forEach((select) => {
                        if (select.value) {
                            this.updateRoomPrice(select);
                        }
                    });
            });
        }

        // Form submit event
        if (this.finalBookingForm) {
            this.finalBookingForm.addEventListener("submit", (e) => {
                // Check if any rooms are selected
                const hasSelectedRooms = Array.from(
                    document.querySelectorAll(
                        ".room-card-quantity, .room-card-quantity-modern"
                    )
                ).some((input) => {
                    return parseInt(input.value) > 0;
                });

                if (!hasSelectedRooms) {
                    e.preventDefault();
                    alert(
                        "Vui lòng chọn ít nhất một loại phòng trước khi đặt phòng."
                    );
                    return false;
                }

                this.isCompletingBooking = true;
                // Update hidden inputs one more time before submit
                this.updateRoomCardHiddenInputs();
            });
        }

        // Before unload event
        window.addEventListener("beforeunload", () => {
            if (!this.isCompletingBooking) {
                this.clearSavedVoucherState();
            }
        });

        // Scroll to rooms button
        const scrollToRoomsBtn = document.getElementById("scrollToRoomsBtn");
        const roomSelectionGrid = document.getElementById("roomSelectionGrid");
        if (scrollToRoomsBtn && roomSelectionGrid) {
            scrollToRoomsBtn.addEventListener("click", () => {
                roomSelectionGrid.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                });
            });
        }

        // Room selection buttons
        document.querySelectorAll(".choose-room-btn").forEach((btn) => {
            btn.addEventListener("click", () => {
                this.handleRoomSelection(btn);
            });
        });

        // Add room button
        const addRoomBtn = document.getElementById("addRoomBtn");
        if (addRoomBtn) {
            addRoomBtn.addEventListener("click", () => {
                const newIndex = this.addRoomCard();
                const select = document.getElementById(
                    `room_type_select_${newIndex}`
                );
                if (select) {
                    select.focus();
                }
            });
        }
    }

    // === VOUCHER POPUP ===
    openVoucherPopup() {
        // Lấy lại tổng tiền hiện tại (trước khi áp dụng voucher mới)
        const currentTotal = this.tinhTongTien();

        // Thu thập danh sách các loại phòng đang được chọn từ room cards
        const selectedRoomTypeIds = [];
        document
            .querySelectorAll(".room-card-quantity, .room-card-quantity-modern")
            .forEach((input) => {
                const qty = parseInt(input.value) || 0;
                const roomId = input.dataset.roomId;
                if (qty > 0 && roomId) {
                    selectedRoomTypeIds.push(roomId);
                }
            });

        const roomTypeIdsParam = encodeURIComponent(
            selectedRoomTypeIds.join(",")
        );

        const fetchUrl = `/client/voucher?current_total=${Math.round(
            currentTotal
        )}&room_type_ids=${roomTypeIdsParam}`;

        fetch(fetchUrl)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then((html) => {
                if (!this.popupElement) {
                    const tempDiv = document.createElement("div");
                    tempDiv.innerHTML = html;
                    this.popupElement = tempDiv.firstChild;
                    document.body.appendChild(this.popupElement);
                } else {
                    const innerContent = this.popupElement.querySelector(
                        ".custom-voucher-inner"
                    );
                    if (innerContent) {
                        const newDoc = new DOMParser().parseFromString(
                            html,
                            "text/html"
                        );
                        const newVoucherList =
                            newDoc.querySelector(".custom-scrollbar");
                        const oldVoucherList =
                            innerContent.querySelector(".custom-scrollbar");
                        if (oldVoucherList && newVoucherList) {
                            oldVoucherList.parentNode.replaceChild(
                                newVoucherList,
                                oldVoucherList
                            );
                        }
                    }
                }

                this.setupPopupEvents(this.popupElement);

                this.popupElement.classList.remove("hidden");

                if (this.currentDiscountPercent > 0) {
                    this.displayAlert(
                        this.popupElement,
                        `Mã ${this.voucherCodeInput.value} đang được áp dụng.`,
                        false
                    );
                } else {
                    this.displayAlert(this.popupElement, "");
                }
            })
            .catch((err) => {
                console.error("Lỗi khi tải voucher:", err);
                if (!this.popupElement) {
                    alert(
                        "Không thể tải danh sách voucher. Vui lòng kiểm tra kết nối mạng hoặc server."
                    );
                } else {
                    const loadingMessage = this.popupElement.querySelector(
                        "#voucherAlertMessage p"
                    );
                    if (loadingMessage) {
                        loadingMessage.textContent =
                            "Không thể tải danh sách voucher. Vui lòng thử lại sau. (Lỗi server/route)";
                        this.displayAlert(
                            this.popupElement,
                            loadingMessage.textContent,
                            true
                        );
                    }
                }
            });
    }

    setupPopupEvents(popup) {
        const searchInput = popup.querySelector("#popupVoucherCodeInput");
        if (searchInput) {
            searchInput.value = "";
        }

        const closeBtn = popup.querySelector("#closeVoucherPopup");
        if (!closeBtn.hasEventListener) {
            const closePopup = () => {
                const popupContent = popup.querySelector(
                    ".custom-voucher-inner"
                );
                if (popupContent) {
                    popupContent.classList.remove("animate-fadeIn");
                    this.displayAlert(popup, "");
                }
                setTimeout(() => {
                    popup.classList.add("hidden");
                }, 300);
            };

            closeBtn.addEventListener("click", closePopup);
            popup.addEventListener("click", function (e) {
                if (e.target === popup) closePopup();
            });
            closeBtn.hasEventListener = true;
        }

        const voucherListContainer = popup.querySelector(".custom-scrollbar");
        if (voucherListContainer && !voucherListContainer.hasEventListener) {
            voucherListContainer.addEventListener("click", (e) => {
                const applyBtn = e.target.closest(".apply-voucher-btn");

                if (applyBtn) {
                    if (this.currentDiscountPercent > 0) {
                        const targetCard = applyBtn.closest(
                            ".custom-voucher-card"
                        );
                        const newCode = targetCard.dataset.voucherCode;
                        const currentCode = this.voucherCodeInput.value;

                        if (newCode === currentCode) {
                            this.displayAlert(
                                popup,
                                `Mã ${newCode} đã được áp dụng rồi.`,
                                false
                            );
                            return;
                        }

                        this.displayAlert(
                            popup,
                            `Bạn chỉ có thể áp dụng 1 mã voucher duy nhất. Vui lòng nhấn "Hủy" mã ${this.voucherCodeInput.value} ở bên dưới hoặc ngoài trang thanh toán trước.`,
                            true
                        );
                        return;
                    }

                    const targetCard = applyBtn.closest(".custom-voucher-card");
                    if (!targetCard) return;

                    const isValid = targetCard.dataset.isValid === "true";
                    const code = targetCard.dataset.voucherCode;
                    const percent = this.getDiscountPercentFromCard(targetCard);

                    if (isValid) {
                        this.applyVoucher(code, percent, popup);
                    } else {
                        this.displayAlert(
                            popup,
                            "Voucher này chưa đủ điều kiện (giá trị đơn hàng tối thiểu hoặc không áp dụng cho loại phòng này).",
                            true
                        );
                    }
                }
            });
            voucherListContainer.hasEventListener = true;
        }

        const searchBtn = popup.querySelector("#searchVoucherBtn");

        if (searchBtn && searchInput) {
            // Reset event listener
            const newSearchBtn = searchBtn.cloneNode(true);
            searchBtn.parentNode.replaceChild(newSearchBtn, searchBtn);

            const handleSearch = () => {
                const searchCode = searchInput.value.toUpperCase().trim();
                this.displayAlert(popup, "");

                if (!searchCode) {
                    this.displayAlert(popup, "Vui lòng nhập mã voucher.", true);
                    return;
                }

                // Check if already applied
                if (this.currentDiscountPercent > 0) {
                    const currentAppliedCode = this.voucherCodeInput.value;

                    // If same code, cancel it
                    if (searchCode === currentAppliedCode) {
                        this.clearVoucher();
                        this.displayAlert(
                            popup,
                            `Mã ${searchCode} đã được hủy thành công.`,
                            false
                        );
                        return;
                    }

                    this.displayAlert(
                        popup,
                        `Bạn chỉ có thể áp dụng 1 mã voucher duy nhất. Vui lòng nhấn "Hủy" mã ${currentAppliedCode} trước khi áp dụng mã mới.`,
                        true
                    );
                    return;
                }

                const card = popup.querySelector(
                    `[data-voucher-code="${searchCode}"]`
                );

                if (card) {
                    const isValid = card.dataset.isValid === "true";
                    const percent = this.getDiscountPercentFromCard(card);

                    if (isValid) {
                        this.applyVoucher(searchCode, percent, popup);
                    } else {
                        this.displayAlert(
                            popup,
                            `Mã "${searchCode}" không hợp lệ cho đơn hàng này (không đủ giá trị tối thiểu hoặc không áp dụng cho loại phòng này).`,
                            true
                        );
                    }
                } else {
                    this.displayAlert(
                        popup,
                        `Mã "${searchCode}" không tồn tại.`,
                        true
                    );
                }
            };

            newSearchBtn.addEventListener("click", handleSearch);
            searchInput.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    handleSearch();
                }
            });
        }
    }

    // === ROOM SELECTION ===
    handleRoomSelection(btn) {
        const roomId = btn.dataset.roomId;
        const selectedRoomCard = btn.closest(".room-card");

        // Set active card
        this.setActiveRoomCard(selectedRoomCard);

        const indexToUse = this.addRoomCard();
        const select = document.getElementById(
            `room_type_select_${indexToUse}`
        );
        if (select) {
            select.value = roomId;
            this.handleRoomTypeChange(indexToUse);
            select.dispatchEvent(new Event("change"));
        }

        const targetCard = document.getElementById(`room_item_${indexToUse}`);
        if (targetCard) {
            targetCard.scrollIntoView({ behavior: "smooth", block: "center" });
        }

        // Update hero section
        this.updateHeroSection(selectedRoomCard);
    }

    setActiveRoomCard(targetCard) {
        document
            .querySelectorAll(".room-card")
            .forEach((card) => card.classList.remove("room-card--active"));
        if (targetCard) {
            targetCard.classList.add("room-card--active");
        }
    }

    updateHeroSection(selectedRoomCard) {
        if (!selectedRoomCard) return;

        // Get info from data attributes
        const roomName = selectedRoomCard.dataset.roomName || "";
        const roomImage = selectedRoomCard.dataset.roomImage || "";
        const roomPrice = parseFloat(selectedRoomCard.dataset.roomPrice) || 0;

        // Update hero section
        const heroTitle = document.getElementById("heroPrimaryTitle");
        const heroImage = document.getElementById("heroPrimaryImage");
        const heroPrice = document.getElementById("heroPriceValue");

        if (heroTitle && roomName) {
            heroTitle.textContent = roomName;
            heroTitle.setAttribute("data-default-title", roomName);
        }

        if (heroImage && roomImage) {
            heroImage.src = roomImage;
            heroImage.setAttribute("data-default-image", roomImage);
        }

        if (heroPrice && roomPrice > 0) {
            heroPrice.textContent =
                Math.round(roomPrice).toLocaleString("vi-VN") + " VND";
            heroPrice.setAttribute("data-default-price", roomPrice);
        }
    }

    addRoomCard() {
        const newHtml = this.buildRoomCardHtml(this.roomIndex);
        const roomsContainer = document.getElementById("roomsContainer");
        roomsContainer.insertAdjacentHTML("beforeend", newHtml);

        const currentIndex = this.roomIndex;
        this.roomIndex++;

        const newCard = document.getElementById(`room_item_${currentIndex}`);
        if (newCard) {
            newCard.classList.add("selected-room-card--pulse");
            setTimeout(
                () => newCard.classList.remove("selected-room-card--pulse"),
                1000
            );
        }

        return currentIndex;
    }

    getRoomTypeOptionsHtml() {
        if (!this.roomTypeOptionsHtml) {
            const template = document.getElementById("roomTypeOptionsTemplate");
            this.roomTypeOptionsHtml = template
                ? template.innerHTML.trim()
                : "";
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
        const roomItem = document.querySelector(
            `.room-item[data-room-index="${index}"]`
        );
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
        const tenLoai = selectedOption.getAttribute("data-ten-loai");
        const anh = selectedOption.getAttribute("data-anh");
        const price =
            parseFloat(selectedOption.getAttribute("data-price")) || 0;
        const basePrice =
            parseFloat(selectedOption.getAttribute("data-base-price")) || 0;
        const giaKhuyenMai =
            parseFloat(selectedOption.getAttribute("data-gia-khuyen-mai")) || 0;
        const giaCoBan =
            parseFloat(selectedOption.getAttribute("data-gia-co-ban")) || 0;

        // Update display info
        document.getElementById(`room_name_${index}`).textContent = tenLoai;

        // Update price
        const priceDiv = document.getElementById(`room_price_${index}`);
        const formatNumber = (num) => Math.round(num).toLocaleString("vi-VN");
        if (giaKhuyenMai > 0) {
            priceDiv.innerHTML = `
                <span class="text-red-600 font-bold text-lg">${formatNumber(
                    giaKhuyenMai
                )}</span>
                <span class="text-gray-500 line-through text-sm">${formatNumber(
                    giaCoBan
                )}</span>
                <span class="text-gray-600 text-sm">VNĐ/đêm</span>
            `;
        } else {
            priceDiv.innerHTML = `
                <span class="text-blue-600 font-bold text-lg">${formatNumber(
                    giaCoBan
                )}</span>
                <span class="text-gray-600 text-sm">VNĐ/đêm</span>
            `;
        }

        // Update image
        const imageDiv = document.getElementById(`room_image_${index}`);
        if (anh && anh.trim() !== "") {
            const imagePath = anh.startsWith("/") ? anh : "/" + anh;
            imageDiv.innerHTML = `<img src="${imagePath}" alt="${tenLoai}" class="w-full h-full object-cover">`;
        } else {
            imageDiv.innerHTML = `<div class="w-full h-full bg-gray-200 flex items-center justify-center"><i class="fas fa-image text-gray-400 text-4xl"></i></div>`;
        }

        // Show room details
        document
            .getElementById(`room_details_${index}`)
            .classList.remove("hidden");
        const placeholderBlock = select
            .closest(".selected-room-card__body")
            ?.querySelector(".selected-room-card__placeholder");
        if (placeholderBlock) {
            placeholderBlock.classList.add("hidden");
        }

        // Show quantity section
        const quantityInput = document.getElementById(`room_quantity_${index}`);
        if (quantityInput) {
            quantityInput
                .closest(".quantity-section")
                .classList.remove("hidden");
            document
                .getElementById(`subtotal_section_${index}`)
                .classList.remove("hidden");
        }

        // Get room availability
        const checkin = this.checkinInput?.value;
        const checkout = this.checkoutInput?.value;
        if (checkin && checkout) {
            this.updateRoomAvailability(index, loaiPhongId, checkin, checkout);
        } else {
            // Use default room count
            const soLuongTrong =
                parseInt(selectedOption.getAttribute("data-so-luong-trong")) ||
                0;
            const availabilityEl = document.getElementById(
                `room_availability_${index}`
            );
            if (availabilityEl) {
                availabilityEl.innerHTML = `
                    <i class="fas fa-bed text-blue-500"></i> Còn ${soLuongTrong} phòng (vui lòng chọn ngày để xem số phòng trống)
                `;
            }
            if (quantityInput) {
                quantityInput.setAttribute("data-max", soLuongTrong);
                const maxQuantityEl = document.getElementById(
                    `max_quantity_${index}`
                );
                if (maxQuantityEl) {
                    maxQuantityEl.textContent = `${soLuongTrong}`;
                }
                const quantityErrorEl = document.getElementById(
                    `quantity_error_${index}`
                );
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

        if (selectElement.tagName === "SELECT") {
            selectedOption = selectElement.options[selectElement.selectedIndex];
            if (!selectedOption.value) {
                // Hide display if no room selected
                const roomItem = selectElement.closest(".room-item");
                const roomIndex = roomItem.getAttribute("data-room-index");
                document
                    .querySelector(`#room_name_${roomIndex}`)
                    .closest(".selected-room-details")
                    .classList.add("hidden");
                document
                    .querySelector(`#room_quantity_${roomIndex}`)
                    .closest(".quantity-section")
                    .classList.add("hidden");
                document
                    .getElementById(`subtotal_section_${roomIndex}`)
                    .classList.add("hidden");
                return;
            }
            price = parseFloat(selectedOption.getAttribute("data-price")) || 0;
            roomItem = selectElement.closest(".room-item");

            // Show selected room info
            const roomIndex = roomItem.getAttribute("data-room-index");
            const tenLoai = selectedOption.getAttribute("data-ten-loai");
            const anh = selectedOption.getAttribute("data-anh");
            const soLuongTrong =
                parseInt(selectedOption.getAttribute("data-so-luong-trong")) ||
                0;
            const giaKhuyenMai =
                parseFloat(
                    selectedOption.getAttribute("data-gia-khuyen-mai")
                ) || 0;
            const giaCoBan =
                parseFloat(selectedOption.getAttribute("data-gia-co-ban")) || 0;

            // Update room name
            document.getElementById(`room_name_${roomIndex}`).textContent =
                tenLoai;

            // Update price
            const priceDiv = document.getElementById(`room_price_${roomIndex}`);
            const formatNumber = (num) =>
                Math.round(num).toLocaleString("vi-VN");

            if (giaKhuyenMai > 0) {
                priceDiv.innerHTML = `
                    <span class="text-red-600 font-bold text-lg">${formatNumber(
                        giaKhuyenMai
                    )}</span>
                    <span class="text-gray-500 line-through text-sm">${formatNumber(
                        giaCoBan
                    )}</span>
                    <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                `;
            } else {
                priceDiv.innerHTML = `
                    <span class="text-blue-600 font-bold text-lg">${formatNumber(
                        giaCoBan
                    )}</span>
                    <span class="text-gray-600 text-sm">VNĐ/đêm</span>
                `;
            }

            // Update availability (only if not already updated from API)
            const availabilityEl = document.getElementById(
                `room_availability_${roomIndex}`
            );
            const checkin = this.checkinInput?.value;
            const checkout = this.checkoutInput?.value;
            if (
                availabilityEl &&
                (!checkin ||
                    !checkout ||
                    !availabilityEl.innerHTML.includes("(từ"))
            ) {
                availabilityEl.innerHTML = `
                    <i class="fas fa-bed text-blue-500"></i> Còn ${soLuongTrong} phòng trống
                `;
            }

            // Update image
            const imageDiv = document.getElementById(`room_image_${roomIndex}`);
            if (anh && anh.trim() !== "") {
                const imagePath = anh.startsWith("/") ? anh : "/" + anh;
                imageDiv.innerHTML = `<img src="${imagePath}" alt="${tenLoai}" class="w-20 h-20 object-cover rounded-lg ml-3">`;
            } else {
                imageDiv.innerHTML = `<div class="w-full h-full bg-gray-200 flex items-center justify-center"><i class="fas fa-image text-gray-400 text-4xl"></i></div>`;
            }

            // Show room details
            document
                .getElementById(`room_details_${roomIndex}`)
                .classList.remove("hidden");

            // Update quantity section
            const quantityInput = document.getElementById(
                `room_quantity_${roomIndex}`
            );
            if (quantityInput) {
                quantityInput.setAttribute("data-max", soLuongTrong);
                quantityInput.setAttribute(
                    "oninput",
                    `validateRoomQuantity(this, ${roomIndex})`
                );
                const maxQuantityEl = document.getElementById(
                    `max_quantity_${roomIndex}`
                );
                if (maxQuantityEl) {
                    maxQuantityEl.textContent = `${soLuongTrong}`;
                }
                const quantityErrorEl = document.getElementById(
                    `quantity_error_${roomIndex}`
                );
                if (quantityErrorEl) {
                    quantityErrorEl.textContent = `Số lượng không được vượt quá ${soLuongTrong} phòng`;
                }
                quantityInput
                    .closest(".quantity-section")
                    .classList.remove("hidden");
                const subtotalSection = document.getElementById(
                    `subtotal_section_${roomIndex}`
                );
                if (subtotalSection) {
                    subtotalSection.classList.remove("hidden");
                }
            }

            // Update onclick handlers
            const decreaseBtn = quantityInput
                .closest(".quantity-section")
                .querySelector('button[onclick*="decreaseRoomQuantity"]');
            const increaseBtn = quantityInput
                .closest(".quantity-section")
                .querySelector('button[onclick*="increaseRoomQuantity"]');
            decreaseBtn.setAttribute(
                "onclick",
                `decreaseRoomQuantity(${roomIndex})`
            );
            increaseBtn.setAttribute(
                "onclick",
                `increaseRoomQuantity(${roomIndex})`
            );
        } else if (
            selectElement.tagName === "INPUT" &&
            selectElement.type === "hidden"
        ) {
            price = parseFloat(selectElement.getAttribute("data-price")) || 0;
            roomItem = selectElement.closest(".room-item");
        }

        if (!roomItem) return;

        const quantityInput = roomItem.querySelector(".room-quantity");
        const roomIndex = roomItem.getAttribute("data-room-index");
        const subtotalSpan = document.getElementById(
            "room_subtotal_" + roomIndex
        );

        if (quantityInput && subtotalSpan) {
            const quantity = parseInt(quantityInput.value) || 1;
            const { soDem } = this.getDatesAndDays();
            const subtotal = price * quantity * soDem;
            const formattedSubtotal =
                Math.round(subtotal).toLocaleString("vi-VN");
            subtotalSpan.textContent = formattedSubtotal + " VNĐ";
        }

        this.updateTotalPrice();
    }

    // Room quantity functions
    increaseRoomQuantity(roomIndex) {
        const quantityInput = document.getElementById(
            "room_quantity_" + roomIndex
        );
        if (!quantityInput) return;

        const maxQuantity =
            parseInt(quantityInput.getAttribute("data-max")) || 0;
        const currentValue = parseInt(quantityInput.value) || 1;

        if (maxQuantity > 0 && currentValue < maxQuantity) {
            quantityInput.value = currentValue + 1;
            quantityInput.dispatchEvent(new Event("change"));
            this.updateRoomQuantity(roomIndex);
        } else if (maxQuantity === 0) {
            quantityInput.value = currentValue + 1;
            this.updateRoomQuantity(roomIndex);
        }
    }

    decreaseRoomQuantity(roomIndex) {
        const quantityInput = document.getElementById(
            "room_quantity_" + roomIndex
        );
        if (!quantityInput) return;

        const currentValue = parseInt(quantityInput.value) || 1;
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
            quantityInput.dispatchEvent(new Event("change"));
            this.updateRoomQuantity(roomIndex);
        }
    }

    updateRoomQuantity(roomIndex) {
        const quantityInput = document.getElementById(
            "room_quantity_" + roomIndex
        );
        if (!quantityInput) return;

        // Validate and limit quantity
        const maxQuantity =
            parseInt(quantityInput.getAttribute("data-max")) || 0;
        let currentQuantity = parseInt(quantityInput.value) || 1;

        if (maxQuantity > 0 && currentQuantity > maxQuantity) {
            quantityInput.value = maxQuantity;
            currentQuantity = maxQuantity;
        }

        if (currentQuantity < 1) {
            quantityInput.value = 1;
            currentQuantity = 1;
        }

        const roomItem = quantityInput.closest(".room-item");
        const select = roomItem.querySelector(".room-type-select");

        if (!select || !select.value) {
            const subtotalSpan = document.getElementById(
                "room_subtotal_" + roomIndex
            );
            if (
                subtotalSpan &&
                subtotalSpan.textContent &&
                subtotalSpan.textContent !== "0"
            ) {
                const currentPriceText = subtotalSpan.textContent.replace(
                    /[^\d]/g,
                    ""
                );
                const currentPrice = parseInt(currentPriceText) || 0;
                if (currentPrice > 0) {
                    const { soDem } = this.getDatesAndDays();
                    const pricePerNight =
                        currentPrice /
                        (parseInt(quantityInput.value) || 1) /
                        soDem;
                    const newSubtotal = pricePerNight * currentQuantity * soDem;
                    const formattedSubtotal =
                        Math.round(newSubtotal).toLocaleString("vi-VN");
                    subtotalSpan.textContent = formattedSubtotal + " VNĐ";
                }
            }
            return;
        }

        // Get price from select or hidden input
        let price = 0;
        if (select.tagName === "SELECT") {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption) {
                price =
                    parseFloat(selectedOption.getAttribute("data-price")) || 0;
            }
        } else if (select.type === "hidden") {
            price = parseFloat(select.getAttribute("data-price")) || 0;
        }

        // Update price display
        if (price > 0) {
            const subtotalSpan = document.getElementById(
                "room_subtotal_" + roomIndex
            );
            if (subtotalSpan) {
                const { soDem } = this.getDatesAndDays();
                const subtotal = price * currentQuantity * soDem;
                const formattedSubtotal =
                    Math.round(subtotal).toLocaleString("vi-VN");
                subtotalSpan.textContent = formattedSubtotal + " VNĐ";
            }
        }

        // Update summary
        const summaryEl = document.getElementById(
            `room_${roomIndex}_summary_quantity`
        );
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
        document.querySelectorAll(".room-item").forEach((roomItem) => {
            const select = roomItem.querySelector(".room-type-select");
            const quantityInput = roomItem.querySelector(".room-quantity");

            if (select && select.value && quantityInput) {
                let price = 0;
                let roomName = "";
                let roomImage = "";

                if (select.tagName === "SELECT") {
                    const selectedOption = select.options[select.selectedIndex];
                    price =
                        parseFloat(selectedOption.getAttribute("data-price")) ||
                        0;
                    roomName =
                        selectedOption.getAttribute("data-ten-loai") || "";
                    roomImage = selectedOption.getAttribute("data-anh") || "";
                } else if (
                    select.tagName === "INPUT" &&
                    select.type === "hidden"
                ) {
                    price = parseFloat(select.getAttribute("data-price")) || 0;
                    roomName = select.getAttribute("data-room-type-name") || "";
                    roomImage = select.getAttribute("data-room-image") || "";
                }

                const quantity = parseInt(quantityInput.value) || 1;
                const roomIndex = roomItem.getAttribute("data-room-index");
                totalBeforeDiscount += price * quantity * soDem;

                if (roomName) {
                    // Compute extra fee per type for legacy/selected rooms panel
                    const { soDem } = this.getDatesAndDays();
                    // Try to read any guest rows inside this room item
                    let sumAdults = 0;
                    const guestRows = roomItem.querySelectorAll(
                        "[data-guest-row] select"
                    );
                    if (guestRows && guestRows.length > 0) {
                        guestRows.forEach((sel) => {
                            sumAdults += parseInt(sel.value) || 0;
                        });
                    } else {
                        sumAdults = this.maxAdultsPerRoom * quantity;
                    }
                    const capacity = quantity * this.maxAdultsPerRoom;
                    const extraGuestsForType = Math.max(
                        0,
                        sumAdults - capacity
                    );
                    const extraFeeForType =
                        extraGuestsForType *
                        price *
                        this.extraFeePercent *
                        soDem;

                    roomsSummary.push({
                        name: roomName,
                        quantity: quantity,
                        price: price,
                        roomIndex: roomIndex,
                        image: roomImage,
                        extraFee: extraFeeForType,
                    });
                }
            }
        });

        // Update room summary list
        const roomsSummaryList = document.getElementById("roomsSummaryList");
        if (roomsSummaryList) {
            if (roomsSummary.length > 0) {
                roomsSummaryList.classList.remove("summary-room-list--empty");
                const totalRoomsSelected = roomsSummary.reduce(
                    (sum, room) => sum + room.quantity,
                    0
                );
                roomsSummaryList.innerHTML = roomsSummary
                    .map(
                        (room) =>
                            `<div class="summary-room-line">
                        <div>
                            <p class="font-medium">${room.name}</p>
                            <small>${this.formatCurrency(
                                room.price
                            )} / đêm</small>
                            ${
                                room.extraFee && room.extraFee > 0
                                    ? `<div class="text-xs text-amber-700">Phụ phí: +${this.formatCurrency(
                                          room.extraFee
                                      )}</div>`
                                    : ""
                            }
                        </div>
                        <div class="summary-room-actions">
                            <span>x${room.quantity}</span>
                            <button type="button" class="summary-room-remove" aria-label="Xóa ${
                                room.name
                            }" onclick="window.bookingManager.removeRoom(${
                                room.roomIndex
                            })">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>`
                    )
                    .join("");

                if (this.selectedRoomsSummaryChip) {
                    this.selectedRoomsSummaryChip.classList.remove("hidden");
                }
                if (this.summaryRoomCount) {
                    this.summaryRoomCount.textContent = totalRoomsSelected;
                }
            } else {
                roomsSummaryList.classList.add("summary-room-list--empty");
                roomsSummaryList.innerHTML =
                    '<p class="summary-room-empty">Chưa có phòng nào</p>';
                if (this.selectedRoomsSummaryChip) {
                    this.selectedRoomsSummaryChip.classList.add("hidden");
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
            totalAfterDiscount = Math.max(
                0,
                totalBeforeDiscount - discountAmount
            );
        }

        // Update display
        this.soDemLuuTruElement.textContent = `Số đêm: ${soDem} đêm`;
        this.totalAfterDiscountDiv.innerHTML = `Tổng: ${this.formatCurrency(
            totalAfterDiscount
        )}`;
        this.totalAfterDiscountDiv.classList.add(
            "text-xl",
            "font-bold",
            "text-red-600"
        );

        if (discountPercent > 0) {
            this.totalBeforeDiscountDiv.innerHTML = `Giá gốc: <span class="line-through text-gray-500">${this.formatCurrency(
                totalBeforeDiscount
            )}</span>`;
            this.totalBeforeDiscountDiv.classList.remove("hidden");
        } else {
            this.totalBeforeDiscountDiv.classList.add("hidden");
        }

        this.finalBookingPriceInput.value = Math.round(totalAfterDiscount);
    }

    updateRoomCardQuantity(roomId) {
        const quantityInput = document.getElementById(
            `room_card_quantity_${roomId}`
        );
        if (!quantityInput) return;

        const currentQuantity = parseInt(quantityInput.value) || 0;
        const maxQuantity = parseInt(quantityInput.dataset.maxQuantity) || 0;

        // Validate quantity
        if (currentQuantity < 0) {
            quantityInput.value = 0;
        } else if (currentQuantity > maxQuantity) {
            quantityInput.value = maxQuantity;
        }

        // Update button states
        this.updateQuantityButtons(quantityInput);

        // Update hidden inputs for form submission
        this.updateRoomCardHiddenInputs();

        // Render per-room guest selectors
        this.renderGuestRows(roomId);

        // Recalculate totals
        this.tinhTongTien();
    }

    updateRoomCardAdults(roomId) {
        // Default adults per room changed; re-render guest rows to use as default for new rows
        this.renderGuestRows(roomId);
    }

    updateRoomCardHiddenInputs() {
        const container = document.getElementById("roomCardHiddenInputs");
        if (!container) return;

        // Clear existing hidden inputs FIRST to prevent duplicates
        container.innerHTML = "";

        let roomIndex = 0;
        // Create hidden inputs for each room card with quantity > 0
        // Only process each room type ONCE (no duplicates)
        const processedRoomIds = new Set();
        document
            .querySelectorAll(".room-card-quantity, .room-card-quantity-modern")
            .forEach((quantityInput) => {
                const quantity = parseInt(quantityInput.value) || 0;
                const roomId = quantityInput.dataset.roomId;

                // Skip if already processed or invalid
                if (!roomId || processedRoomIds.has(roomId) || quantity <= 0) {
                    return;
                }

                // Mark as processed
                processedRoomIds.add(roomId);

                // Sum guests from per-room rows if present; else assume defaults
                let adultsTotal = 0;
                let childrenTotal = 0;
                let infantsTotal = 0;

                const rowsContainer = document.getElementById(
                    `room_card_guest_rows_${roomId}`
                );

                if (rowsContainer) {
                    // Get adults from adult selectors
                    const adultSelects = rowsContainer.querySelectorAll(
                        '[data-guest-type="adults"]'
                    );
                    adultSelects.forEach((sel) => {
                        adultsTotal += parseInt(sel.value) || 0;
                    });

                    // Get children from children selectors
                    const childSelects = rowsContainer.querySelectorAll(
                        '[data-guest-type="children"]'
                    );
                    childSelects.forEach((sel) => {
                        childrenTotal += parseInt(sel.value) || 0;
                    });

                    // Get infants from infant selectors
                    const infantSelects = rowsContainer.querySelectorAll(
                        '[data-guest-type="infants"]'
                    );
                    infantSelects.forEach((sel) => {
                        infantsTotal += parseInt(sel.value) || 0;
                    });
                }

                // If no guest rows, use default adults
                if (
                    adultsTotal === 0 &&
                    !rowsContainer?.querySelector("[data-guest-row]")
                ) {
                    adultsTotal = this.maxAdultsPerRoom * quantity;
                }

                // Create hidden inputs in the format the controller expects: rooms[index][field]
                const roomIdInput = document.createElement("input");
                roomIdInput.type = "hidden";
                roomIdInput.name = `rooms[${roomIndex}][loai_phong_id]`;
                roomIdInput.value = roomId;

                const quantityHiddenInput = document.createElement("input");
                quantityHiddenInput.type = "hidden";
                quantityHiddenInput.name = `rooms[${roomIndex}][so_luong]`;
                quantityHiddenInput.value = quantity;

                const adultsHiddenInput = document.createElement("input");
                adultsHiddenInput.type = "hidden";
                adultsHiddenInput.name = `rooms[${roomIndex}][so_nguoi]`;
                adultsHiddenInput.value = adultsTotal;

                // Add children hidden input
                const childrenHiddenInput = document.createElement("input");
                childrenHiddenInput.type = "hidden";
                childrenHiddenInput.name = `rooms[${roomIndex}][so_tre_em]`;
                childrenHiddenInput.value = childrenTotal;

                // Add infants hidden input
                const infantsHiddenInput = document.createElement("input");
                infantsHiddenInput.type = "hidden";
                infantsHiddenInput.name = `rooms[${roomIndex}][so_em_be]`;
                infantsHiddenInput.value = infantsTotal;

                container.appendChild(roomIdInput);
                container.appendChild(quantityHiddenInput);
                container.appendChild(adultsHiddenInput);
                container.appendChild(childrenHiddenInput);
                container.appendChild(infantsHiddenInput);

                roomIndex++;
            });
    }

    clearRoomCardQuantity(roomId) {
        const quantityInput = document.getElementById(
            `room_card_quantity_${roomId}`
        );
        if (quantityInput) {
            quantityInput.value = 0;
            this.updateRoomCardQuantity(roomId);
        }
    }

    // Utility function for hotel details
    showHotelDetails() {
        alert(
            "Tính năng chi tiết khách sạn sẽ được phát triển trong tương lai!"
        );
    }
}

// Global functions for room card quantity management
window.increaseRoomCardQuantity = function (roomId) {
    if (window.bookingManager) {
        const quantityInput = document.getElementById(
            `room_card_quantity_${roomId}`
        );
        if (quantityInput) {
            const currentValue = parseInt(quantityInput.value) || 0;
            const maxQuantity =
                parseInt(quantityInput.dataset.maxQuantity) || 0;

            if (currentValue < maxQuantity) {
                quantityInput.value = currentValue + 1;
                window.bookingManager.updateRoomCardQuantity(roomId);
            }
        }
    }
};

window.decreaseRoomCardQuantity = function (roomId) {
    if (window.bookingManager) {
        const quantityInput = document.getElementById(
            `room_card_quantity_${roomId}`
        );
        if (quantityInput) {
            const currentValue = parseInt(quantityInput.value) || 0;

            if (currentValue > 0) {
                quantityInput.value = currentValue - 1;
                window.bookingManager.updateRoomCardQuantity(roomId);
            }
        }
    }
};

window.updateRoomCardQuantity = function (roomId) {
    if (window.bookingManager) {
        window.bookingManager.updateRoomCardQuantity(roomId);
    }
};

// Global functions for backward compatibility
window.updateRoomPrice = function (selectElement) {
    if (window.bookingManager) {
        window.bookingManager.updateRoomPrice(selectElement);
    }
};

window.increaseRoomQuantity = function (roomIndex) {
    if (window.bookingManager) {
        window.bookingManager.increaseRoomQuantity(roomIndex);
    }
};

window.decreaseRoomQuantity = function (roomIndex) {
    if (window.bookingManager) {
        window.bookingManager.decreaseRoomQuantity(roomIndex);
    }
};

window.updateRoomQuantity = function (roomIndex) {
    if (window.bookingManager) {
        window.bookingManager.updateRoomQuantity(roomIndex);
    }
};

window.updateTotalPrice = function () {
    if (window.bookingManager) {
        window.bookingManager.updateTotalPrice();
    }
};

window.removeRoom = function (index) {
    if (window.bookingManager) {
        window.bookingManager.removeRoom(index);
    }
};

window.handleRoomTypeChange = function (index) {
    if (window.bookingManager) {
        window.bookingManager.handleRoomTypeChange(index);
    }
};

window.showHotelDetails = function () {
    if (window.bookingManager) {
        window.bookingManager.showHotelDetails();
    }
};

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    window.bookingManager = new BookingManager();
});

document.addEventListener("DOMContentLoaded", function () {
    const modalOverlay = document.getElementById("roomDetailsModal");
    const closeModalBtn = document.getElementById("closeModalBtn");
    // Đảm bảo DOM element chứa tất cả các nút phòng nghỉ là đúng
    const roomSelectionGrid = document.getElementById("roomSelectionGrid");

    // DOM elements in modal
    const modalRoomName = document.getElementById("modalRoomName");
    const modalRoomSize = document.getElementById("modalRoomSize");
    const modalRoomImage = document.getElementById("modalRoomImage");
    const modalRoomDescription = document.getElementById(
        "modalRoomDescription"
    );
    // KHÔNG cần modalRoomAmenities vì bạn dùng dữ liệu mẫu tĩnh trong HTML

    // Mở modal
    // Dùng Event Delegation trên container chung để bắt sự kiện click nút chi tiết
    roomSelectionGrid.addEventListener("click", function (e) {
        // Tìm element gần nhất có class 'view-room-details'
        const detailButton = e.target.closest(".view-room-details");

        if (detailButton) {
            e.preventDefault(); // Ngăn chặn hành vi mặc định (nếu là thẻ <a>)
            const roomData = detailButton.dataset;

            // 1. Điền dữ liệu cơ bản
            modalRoomName.textContent = roomData.roomName;
            // Dùng ternary operator để xử lý nếu data-room-size không tồn tại
            modalRoomSize.textContent = roomData.roomSize || "";
            modalRoomImage.src = roomData.roomImage;
            modalRoomDescription.textContent = roomData.roomDescription;

            // 2. Cập nhật giá phòng
            const roomPrice = roomData.roomPrice || "0";
            const modalRoomPrice = document.getElementById("modalRoomPrice");
            if (modalRoomPrice) {
                modalRoomPrice.textContent =
                    formatCurrency(parseFloat(roomPrice)) + "/đêm";
            }

            // 3. Load rating và reviews từ data attributes
            loadRoomRatingAndReviewsFromData(detailButton);

            // 4. Cập nhật form đánh giá với thông tin phòng hiện tại
            updateReviewForm(roomData.roomId, roomData.roomName);

            // 2. BỎ QUA LOGIC ĐIỀN TIỆN ÍCH ĐỘNG (vì đã dùng HTML tĩnh)

            // 3. Hiển thị modal
            modalOverlay.classList.add("visible");
            // Ẩn menu khi mở popup
            document.body.classList.add("modal-open");
        }
    });

    // Đóng modal
    closeModalBtn.addEventListener("click", closeRoomDetailsModal);
    modalOverlay.addEventListener("click", function (e) {
        if (e.target === modalOverlay) {
            closeRoomDetailsModal();
        }
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape" && modalOverlay.classList.contains("visible")) {
            closeRoomDetailsModal();
        }
    });

    function closeRoomDetailsModal() {
        modalOverlay.classList.remove("visible");
        // Hiện lại menu khi đóng popup
        document.body.classList.remove("modal-open");
    }

    // === HELPER FUNCTIONS ===

    // Format currency function
    function formatCurrency(number) {
        return (
            Math.round(number).toLocaleString("vi-VN", {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }) + " VNĐ"
        );
    }

    // Load room rating and reviews từ data attributes
    function loadRoomRatingAndReviewsFromData(buttonElement) {
        try {
            // Lấy dữ liệu từ data attributes
            const averageRating =
                parseFloat(buttonElement.dataset.averageRating) || 0;
            const totalReviews =
                parseInt(buttonElement.dataset.totalReviews) || 0;
            const recentReviewsJson =
                buttonElement.dataset.recentReviews || "[]";

            let recentReviews = [];
            try {
                recentReviews = JSON.parse(recentReviewsJson);
            } catch (e) {
                console.warn("Error parsing recent reviews:", e);
                recentReviews = [];
            }

            // Update rating summary
            updateRatingSummary(averageRating, totalReviews);

            // Update recent reviews
            updateRecentReviews(recentReviews);
        } catch (error) {
            console.error("Error loading reviews from data:", error);
            // Fallback to show no reviews
            updateRatingSummary(0, 0);
            updateRecentReviews([]);
        }
    }

    // Cập nhật form đánh giá với thông tin phòng hiện tại
    function updateReviewForm(roomId, roomName) {
        // Cập nhật tên phòng trong form
        const reviewFormRoomName =
            document.getElementById("reviewFormRoomName");
        if (reviewFormRoomName && roomName) {
            reviewFormRoomName.textContent = roomName;
        }

        // Cập nhật ID phòng trong form
        const reviewFormRoomId = document.getElementById("reviewFormRoomId");
        if (reviewFormRoomId && roomId) {
            reviewFormRoomId.value = roomId;
        }

        // Kiểm tra xem phòng này có đánh giá không và quyết định hiển thị gì
        checkRoomReviewStatus(roomId);
    }

    // Kiểm tra trạng thái đánh giá của phòng và hiển thị form phù hợp
    function checkRoomReviewStatus(roomId) {
        const existingReviewsSection = document.getElementById(
            "existingReviewsSection"
        );
        const newReviewForm = document.getElementById("newReviewForm");

        // Lấy dữ liệu từ data attributes để kiểm tra
        const currentButton = document.querySelector(
            `[data-room-id="${roomId}"]`
        );
        if (currentButton) {
            const totalReviews =
                parseInt(currentButton.dataset.totalReviews) || 0;
            const averageRating =
                parseFloat(currentButton.dataset.averageRating) || 0;

            if (totalReviews > 0) {
                // Có đánh giá - hiển thị section đánh giá, ẩn form
                if (existingReviewsSection) {
                    existingReviewsSection.style.display = "flex";
                    // Cập nhật thông tin đánh giá
                    const reviewSummaryText =
                        document.getElementById("reviewSummaryText");
                    if (reviewSummaryText) {
                        reviewSummaryText.textContent = `⭐ ${averageRating} / 5 (${totalReviews} đánh giá)`;
                    }
                }
                if (newReviewForm) {
                    newReviewForm.style.display = "none";
                }

                // Cập nhật danh sách đánh giá gần đây
                updateReviewList(roomId);
            } else {
                // Chưa có đánh giá - ẩn section đánh giá, hiển thị form
                if (existingReviewsSection) {
                    existingReviewsSection.style.display = "none";
                }
                if (newReviewForm) {
                    newReviewForm.style.display = "block";
                }

                // Hiển thị "Chưa có đánh giá nào" trong danh sách
                const reviewListContainer = document.getElementById(
                    "reviewListContainer"
                );
                if (reviewListContainer) {
                    reviewListContainer.innerHTML =
                        '<p class="text-gray-500 italic">Chưa có đánh giá nào.</p>';
                }
            }
        }
    }

    // Cập nhật danh sách đánh giá gần đây
    function updateReviewList(roomId) {
        const reviewListContainer = document.getElementById(
            "reviewListContainer"
        );
        if (!reviewListContainer || !roomId) return;

        // Lấy dữ liệu đánh giá từ data attributes
        const currentButton = document.querySelector(
            `[data-room-id="${roomId}"]`
        );
        if (currentButton) {
            const recentReviewsJson =
                currentButton.dataset.recentReviews || "[]";

            try {
                const recentReviews = JSON.parse(recentReviewsJson);

                if (recentReviews && recentReviews.length > 0) {
                    let reviewsHtml = "";
                    recentReviews.forEach((review) => {
                        const stars = generateStars(review.rating);
                        const imageHtml = review.image
                            ? `
                            <img src="${review.image}"
                                 alt="Ảnh đánh giá"
                                 class="w-32 h-32 object-cover rounded-lg mt-2 border border-gray-200 shadow-sm">
                        `
                            : "";

                        // Kiểm tra xem có phải đánh giá của user hiện tại không
                        const currentUserId =
                            window.bookingConfig?.userId || null;
                        const isOwner =
                            currentUserId &&
                            review.user_id &&
                            currentUserId == review.user_id;
                        const editButtons = isOwner
                            ? `
                            <div class="flex gap-3 mt-3">
                                <button type="button"
                                        onclick="toggleEdit(this)"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    ✏️ Chỉnh sửa
                                </button>
                                <form action="/client/danh-gia/${
                                    review.id || ""
                                }"
                                      method="POST"
                                      onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này không?')"
                                      style="display: inline;">
                                    <input type="hidden" name="_token" value="${
                                        document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            ?.getAttribute("content") || ""
                                    }">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800 font-medium text-sm">
                                        🗑️ Xóa
                                    </button>
                                </form>
                            </div>
                        `
                            : "";

                        reviewsHtml += `
                            <div x-data="{ editing: false }" class="bg-gray-50 p-4 rounded-lg shadow mb-3 flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800 text-lg">
                                        ${review.user_name || "Khách ẩn danh"}
                                    </p>
                                    <p class="text-gray-600 text-sm mt-1">${
                                        review.comment || ""
                                    }</p>
                                    ${imageHtml}
                                    <p class="text-gray-400 text-xs mt-1">${
                                        review.created_at || ""
                                    }</p>
                                    ${editButtons}
                                </div>
                                <div class="flex items-center space-x-1">
                                    ${stars}
                                </div>
                            </div>
                        `;
                    });
                    reviewListContainer.innerHTML = reviewsHtml;
                } else {
                    reviewListContainer.innerHTML =
                        '<p class="text-gray-500 italic">Chưa có đánh giá nào.</p>';
                }
            } catch (e) {
                console.warn("Error parsing reviews:", e);
                reviewListContainer.innerHTML =
                    '<p class="text-gray-500 italic">Chưa có đánh giá nào.</p>';
            }
        }
    }

    // Update rating summary
    function updateRatingSummary(averageRating, totalReviews) {
        const starsContainer = document.getElementById("modalRatingStars");
        const scoreElement = document.getElementById("modalRatingScore");
        const countElement = document.getElementById("modalRatingCount");

        if (starsContainer) {
            starsContainer.innerHTML = generateStars(averageRating);
        }

        if (scoreElement) {
            scoreElement.textContent = `${averageRating.toFixed(1)}/5`;
        }

        if (countElement) {
            countElement.textContent = `(${totalReviews} đánh giá)`;
        }
    }

    // Generate stars HTML
    function generateStars(rating) {
        let starsHtml = "";
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                starsHtml += '<i class="fas fa-star text-yellow-400"></i>';
            } else {
                starsHtml += '<i class="fas fa-star text-gray-300"></i>';
            }
        }
        return starsHtml;
    }

    // Update recent reviews
    function updateRecentReviews(reviews) {
        const container = document.getElementById("modalRecentReviews");
        if (!container) return;

        if (reviews && reviews.length > 0) {
            let reviewsHtml = "";
            reviews.slice(0, 2).forEach((review) => {
                // Chỉ hiển thị 2 review gần nhất
                const userName = review.user_name || "Anonymous";
                const firstLetter = userName.charAt(0).toUpperCase();
                const avatarUrl = `https://ui-avatars.com/api/?name=${firstLetter}&background=3b82f6&color=fff`;

                reviewsHtml += `
                    <div class="recent-review-item">
                        <div class="review-avatar">
                            <img src="${avatarUrl}" alt="${userName}">
                        </div>
                        <div class="review-content">
                            <div class="review-stars">
                                ${generateStars(review.rating)}
                            </div>
                            <p class="review-text">${review.comment}</p>
                            <span class="review-author">${userName}</span>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = reviewsHtml;
        } else {
            container.innerHTML =
                '<div class="no-reviews"><p class="text-gray-500 text-sm">Chưa có đánh giá nào</p></div>';
        }
    }

    // === JAVASCRIPT CHO TOGGLE FUNCTIONS ===

    // Toggle description function
    window.toggleDescription = function () {
        const description = document.getElementById("modalRoomDescription");
        const toggleBtn = document.querySelector(".description-toggle");

        if (description.style.webkitLineClamp === "none") {
            description.style.webkitLineClamp = "3";
            description.style.lineClamp = "3";
            toggleBtn.textContent = "Xem thêm";
        } else {
            description.style.webkitLineClamp = "none";
            description.style.lineClamp = "none";
            toggleBtn.textContent = "Thu gọn";
        }
    };

    // Toggle review form function
    window.toggleReviewForm = function () {
        const container = document.querySelector(".review-form-container");
        const toggleBtn = document.querySelector(".review-form-toggle");
        const icon = toggleBtn.querySelector(".toggle-icon");

        if (container.style.display === "none") {
            container.style.display = "block";
            toggleBtn.classList.add("expanded");
            toggleBtn.querySelector("span").textContent = "Ẩn form đánh giá";
        } else {
            container.style.display = "none";
            toggleBtn.classList.remove("expanded");
            toggleBtn.querySelector("span").textContent =
                "Viết đánh giá của bạn";
        }
    };

    // === JAVASCRIPT CHO PHẦN COMMENT VÀ ĐÁNH GIÁ ===

    // Toggle review form
    const toggleReviewFormBtn = document.getElementById("toggleReviewForm");
    const reviewFormContainer = document.getElementById("reviewFormContainer");
    const cancelReviewBtn = document.getElementById("cancelReview");
    const roomReviewForm = document.getElementById("roomReviewForm");

    if (toggleReviewFormBtn && reviewFormContainer) {
        toggleReviewFormBtn.addEventListener("click", function () {
            reviewFormContainer.classList.toggle("hidden");

            if (!reviewFormContainer.classList.contains("hidden")) {
                toggleReviewFormBtn.innerHTML =
                    '<i class="fas fa-times"></i><span>Hủy viết đánh giá</span>';
                // Focus vào form đầu tiên
                const firstInput = reviewFormContainer.querySelector(
                    'input[type="radio"]'
                );
                if (firstInput) firstInput.focus();
            } else {
                toggleReviewFormBtn.innerHTML =
                    '<i class="fas fa-edit"></i><span>Viết đánh giá của bạn</span>';
            }
        });
    }

    if (cancelReviewBtn && reviewFormContainer) {
        cancelReviewBtn.addEventListener("click", function () {
            reviewFormContainer.classList.add("hidden");
            toggleReviewFormBtn.innerHTML =
                '<i class="fas fa-edit"></i><span>Viết đánh giá của bạn</span>';

            // Reset form
            if (roomReviewForm) {
                roomReviewForm.reset();
                updateStarRating();
            }
        });
    }

    // Handle star rating
    function updateStarRating() {
        const starInputs = document.querySelectorAll(
            '.star-rating input[type="radio"]'
        );
        const starLabels = document.querySelectorAll(".star-rating .star");

        starInputs.forEach((input, index) => {
            input.addEventListener("change", function () {
                const rating = parseInt(this.value);

                starLabels.forEach((star, starIndex) => {
                    const starValue = 5 - starIndex; // Reverse order
                    if (starValue <= rating) {
                        star.style.color = "#f59e0b";
                    } else {
                        star.style.color = "#d1d5db";
                    }
                });
            });
        });

        // Hover effects
        starLabels.forEach((star, index) => {
            star.addEventListener("mouseenter", function () {
                const rating = 5 - index; // Reverse order

                starLabels.forEach((s, sIndex) => {
                    const sValue = 5 - sIndex;
                    if (sValue <= rating) {
                        s.style.color = "#fbbf24";
                    } else {
                        s.style.color = "#d1d5db";
                    }
                });
            });

            star.addEventListener("mouseleave", function () {
                // Reset to selected state
                const checkedInput = document.querySelector(
                    '.star-rating input[type="radio"]:checked'
                );
                if (checkedInput) {
                    const selectedRating = parseInt(checkedInput.value);
                    starLabels.forEach((s, sIndex) => {
                        const sValue = 5 - sIndex;
                        if (sValue <= selectedRating) {
                            s.style.color = "#f59e0b";
                        } else {
                            s.style.color = "#d1d5db";
                        }
                    });
                } else {
                    starLabels.forEach((s) => (s.style.color = "#d1d5db"));
                }
            });
        });
    }

    // Initialize star rating
    updateStarRating();

    // Function to add new review to the list
    function addNewReviewToList(rating, title, content) {
        const reviewsContainer = document.getElementById("reviewsContainer");
        const noReviewsMessage = reviewsContainer.querySelector(
            ".no-reviews-message"
        );

        // Ẩn thông báo "chưa có đánh giá" nếu có
        if (noReviewsMessage) {
            noReviewsMessage.style.display = "none";
        }

        // Tạo HTML cho đánh giá mới
        const newReviewHtml = `
            <div class="review-item new-review" style="animation: slideInFromTop 0.5s ease;">
                <div class="review-header">
                    <div class="reviewer-info">
                        <div class="reviewer-avatar">
                            <img src="https://ui-avatars.com/api/?name=Bạn&background=0D8ABC&color=fff" alt="Avatar">
                        </div>
                        <div class="reviewer-details">
                            <h6 class="reviewer-name">Bạn</h6>
                            <div class="review-meta">
                                <div class="review-rating">
                                    ${generateStarRating(rating)}
                                </div>
                                <span class="review-date">Vừa xong</span>
                            </div>
                        </div>
                    </div>
                    <div class="review-actions">
                        <button type="button" class="action-btn">
                            <i class="fas fa-thumbs-up"></i>
                            <span>0</span>
                        </button>
                    </div>
                </div>
                <div class="review-content">
                    <h6 class="review-title">${title}</h6>
                    <p class="review-text">${content}</p>
                </div>
            </div>
        `;

        // Thêm đánh giá mới vào đầu danh sách
        reviewsContainer.insertAdjacentHTML("afterbegin", newReviewHtml);

        // Cập nhật số lượng đánh giá
        updateReviewCount();

        // Scroll đến đánh giá mới
        const newReview = reviewsContainer.querySelector(".review-item");
        newReview.scrollIntoView({ behavior: "smooth", block: "nearest" });

        // Xóa highlight sau 3 giây
        setTimeout(() => {
            newReview.classList.remove("new-review");
        }, 3000);

        // Thêm event listener cho nút like của đánh giá mới
        const newActionBtn = newReview.querySelector(".action-btn");
        if (newActionBtn) {
            newActionBtn.addEventListener("click", function () {
                const icon = this.querySelector("i");
                const countSpan = this.querySelector("span");
                let currentCount = parseInt(countSpan.textContent);

                if (icon.classList.contains("fa-thumbs-up")) {
                    // Toggle like
                    if (this.classList.contains("liked")) {
                        this.classList.remove("liked");
                        countSpan.textContent = currentCount - 1;
                        this.style.background = "#f3f4f6";
                        this.style.color = "#6b7280";
                    } else {
                        this.classList.add("liked");
                        countSpan.textContent = currentCount + 1;
                        this.style.background = "#dbeafe";
                        this.style.color = "#2563eb";
                    }
                }
            });
        }
    }

    // Function to generate star rating HTML
    function generateStarRating(rating) {
        let starsHtml = "";
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) {
                starsHtml += '<i class="fas fa-star text-yellow-400"></i>';
            } else {
                starsHtml += '<i class="far fa-star text-gray-300"></i>';
            }
        }
        return starsHtml;
    }

    // Function to update review count
    function updateReviewCount() {
        const reviewItems = document.querySelectorAll(".review-item");
        const totalReviewsDisplay = document.getElementById(
            "totalReviewsDisplay"
        );
        const averageRatingDisplay = document.getElementById(
            "averageRatingDisplay"
        );
        const averageStarsDisplay = document.getElementById(
            "averageStarsDisplay"
        );

        if (reviewItems.length > 0) {
            // Cập nhật số lượng đánh giá
            if (totalReviewsDisplay) {
                totalReviewsDisplay.textContent = `Dựa trên ${reviewItems.length} đánh giá`;
            }

            // Tính điểm trung bình (giả lập - trong thực tế bạn sẽ tính từ database)
            let totalRating = 0;
            reviewItems.forEach((item) => {
                const stars = item.querySelectorAll(
                    ".review-rating .fas.fa-star"
                );
                totalRating += stars.length;
            });

            const averageRating = (totalRating / reviewItems.length).toFixed(1);

            if (averageRatingDisplay) {
                averageRatingDisplay.textContent = averageRating;
            }

            // Cập nhật sao trung bình
            if (averageStarsDisplay) {
                const avgRating = Math.round(parseFloat(averageRating));
                let starsHtml = "";
                for (let i = 1; i <= 5; i++) {
                    if (i <= avgRating) {
                        starsHtml +=
                            '<i class="fas fa-star text-yellow-400"></i>';
                    } else {
                        starsHtml +=
                            '<i class="far fa-star text-gray-300"></i>';
                    }
                }
                averageStarsDisplay.innerHTML = starsHtml;
            }
        }
    }

    // Comment functionality is now handled by the included comment component
    // All form submissions, filtering, and interactions are handled by Laravel forms and Alpine.js
});

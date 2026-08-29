document.addEventListener("DOMContentLoaded", function() {
    const viewBtns = document.querySelectorAll('.btn-view-details');
    const detailsDrawerEl = document.getElementById('detailsDrawer');
    const detailsDrawer = bootstrap.Offcanvas.getOrCreateInstance(detailsDrawerEl);

    const adjustDrawerEl = document.getElementById('adjustDrawer');
    const adjustDrawer = bootstrap.Offcanvas.getOrCreateInstance(adjustDrawerEl);

    const content = document.getElementById('drawerContent');
    const adjContent = document.getElementById('adjustContent');

    viewBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            detailsDrawer.show();
            content.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>`;

            fetch(`${BASE_URL}/admin/inventory/get-details/${id}`)
                .then(res => res.json())
                .then(data => {
                    content.innerHTML = `
                        <div class="text-center mb-4">
                            <div class="p-3 bg-light rounded-4 mb-2"><i class="fas fa-box-open fs-2 text-muted"></i></div>
                            <h5 class="fw-bold mb-1">${data.name}</h5>
                            <span class="badge bg-dark">${data.cat_name}</span>
                        </div>
                        <div class="row g-3 px-2 mb-4 text-start">
                            <div class="col-6"><label class="info-label">SKU</label><p class="info-value">${data.sku}</p></div>
                            <div class="col-6"><label class="info-label">Barcode</label><p class="info-value">${data.barcode_value}</p></div>
                            <div class="col-6"><label class="info-label">Current Stock</label><p class="info-value fs-6 text-maroon">${data.quantity_avail} ${data.unit}</p></div>
                            <div class="col-6"><label class="info-label">Batch No.</label><p class="info-value text-primary">${data.batch_number}</p></div>
                        </div>

                        <div class="d-grid gap-2">
                            <!-- NEW BUTTON: TRIGGERS ADJUSTMENT DRAWER -->
                            <button type="button" id="btnStaffAdjust" class="btn btn-warning py-3 fw-bold rounded-3 shadow-sm text-dark">
                                <i class="fas fa-adjust me-2"></i>PROCESS STOCK ADJUSTMENT
                            </button>
                        </div>
                    `;

                    document.getElementById('btnStaffAdjust').addEventListener('click', function() {
                        detailsDrawer.hide();
                        openAdjustForm(data);
                    });
                });
        });
    });

    // THE FORMAL GRAYSCALE ADJUSTMENT FORM
    function openAdjustForm(data) {
        adjContent.innerHTML = `
            <div class="mb-4">
                <h6 class="fw-bold mb-3" style="color: #b30000; font-size: 12px; letter-spacing: 0.5px;">
                    <i class="fas fa-pencil-alt me-2" style="color:#333"></i>ADD STOCK ADJUSTMENT
                </h6>
                <h5 class="fw-bold text-dark" style="font-size: 14px;">STOCK ADJUSTMENT FORM</h5>
            </div>

            <form action="${BASE_URL}/staff/inventory/adjust_stock" method="POST">
                <input type="hidden" name="batch_id" value="${data.batch_id}">
                <input type="hidden" name="product_id" value="${data.product_id}">
                <input type="hidden" name="qty_before" value="${data.quantity_avail}">

                <div class="row g-3 text-start">
                    <div class="col-6">
                        <label class="formal-label">Product</label>
                        <input type="text" class="formal-input read-only-input" value="${data.name}" readonly>
                    </div>
                    <div class="col-6">
                        <label class="formal-label">Batch Number</label>
                        <input type="text" class="formal-input read-only-input" value="${data.batch_number}" readonly>
                    </div>
                    <div class="col-6">
                        <label class="formal-label">System Quantity</label>
                        <input type="text" class="formal-input read-only-input" value="${data.quantity_avail}" readonly>
                    </div>
                    <div class="col-6">
                        <label class="formal-label">Actual Corrected Qty *</label>
                        <input type="number" name="qty_after" class="formal-input" required>
                    </div>
                    <div class="col-12">
                        <label class="formal-label">Adjustment Reason *</label>
                        <select name="reason" class="form-select formal-input" required>
                            <option value="Physical Count">Physical Inventory Count</option>
                            <option value="Damage">Damaged Goods</option>
                            <option value="Expired">Expired Stock</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="formal-label">Staff Remarks (Notes) *</label>
                        <textarea name="notes" class="formal-input" rows="4" required placeholder="Describe the reason for adjustment..."></textarea>
                    </div>
                </div>

                <div class="mt-5 d-flex gap-2">
                    <button type="submit" class="btn btn-save-adj flex-grow-1 py-3" style="background:#1a2a6c">✓ Confirm Adjustment</button>
                    <button type="button" class="btn btn-cancel-adj px-4" data-bs-dismiss="offcanvas">Cancel</button>
                </div>
            </form>
        `;
        adjustDrawer.show();
    }
});
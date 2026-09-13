<?= view('partials/supplier/head') ?>
<link rel="stylesheet" href="<?= base_url('public/css/admin/inventory.css') ?>">

<div class="wrapper">
    <?= view('partials/supplier/sidebar') ?>
    <div id="content">
        <?= view('partials/supplier/header') ?>

        <div class="container-fluid p-4" style="font-size: 11px;">
            <div class="d-flex align-items-center mb-4">
                <button class="btn btn-sm btn-white shadow-sm rounded-pill px-3 me-3" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Back</button>
                <h5 class="fw-bold mb-0">My Profile</h5>
            </div>

            <div class="dashboard-banner mb-4 p-3 text-white shadow-sm">
                <h6 class="fw-bold mb-1"><i class="fas fa-truck me-2"></i>Supplier Profile</h6>
                <p class="mb-0 opacity-75" style="font-size: 10px;">Update your contact information, categories, and password</p>
            </div>

            <?php if(session()->getFlashdata('error')): ?>
                <div class="alert alert-danger py-2 small"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('success')): ?>
                <div class="alert alert-success py-2 small"><?= session()->getFlashdata('success') ?></div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="custom-table-container">
                        <form action="<?= base_url('supplier/account/profile/update') ?>" method="POST" enctype="multipart/form-data" id="profileForm">

                            <div class="text-center mb-4">
                                <img src="<?= $profile->avatar_path ? base_url($profile->avatar_path) : base_url('public/images/default-avatar.png') ?>" id="avatarPreview" class="rounded-circle mb-2" style="width:90px; height:90px; object-fit:cover; border:3px solid #eee;">
                                <div>
                                    <label class="btn btn-xs btn-outline-dark rounded-pill px-3" style="cursor:pointer;">
                                        <i class="fas fa-camera me-1"></i>Change Logo/Photo
                                        <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none;">
                                    </label>
                                </div>
                            </div>

                            <p class="fw-bold mb-3" style="font-size:12px;">Company Information</p>
                            <div class="row g-3 mb-4">
                                <div class="<?= $profile->registration_ref ? 'col-md-8' : 'col-md-12' ?>">
                                    <label class="formal-label">Company Name</label>
                                    <input type="text" class="formal-input read-only-input" value="<?= esc($profile->name) ?>" readonly>
                                </div>
                                <?php if ($profile->registration_ref): ?>
                                <div class="col-md-4">
                                    <label class="formal-label">Reference #</label>
                                    <input type="text" class="formal-input read-only-input" value="<?= esc($profile->registration_ref) ?>" readonly>
                                </div>
                                <?php endif; ?>

                                <div class="col-md-6"><label class="formal-label">Contact Person *</label><input type="text" name="contact_person" class="formal-input" value="<?= esc($profile->contact_person) ?>" required></div>
                                <div class="col-md-6"><label class="formal-label">Login Email *</label><input type="email" name="email" class="formal-input" value="<?= esc($profile->login_email) ?>" required></div>
                                <div class="col-md-6"><label class="formal-label">Phone *</label><input type="text" name="phone" class="formal-input" value="<?= esc($profile->phone) ?>" required></div>
                                <div class="col-md-6"><label class="formal-label">Payment Terms</label><input type="text" name="payment_terms" class="formal-input" value="<?= esc($profile->payment_terms) ?>" placeholder="e.g. Net 30"></div>
                                <div class="col-md-6"><label class="formal-label">Average Lead Time (days)</label><input type="number" name="lead_time_days" class="formal-input" value="<?= esc($profile->lead_time_days) ?>" min="1"></div>
                                <div class="col-md-6"><label class="formal-label">TIN</label><input type="text" name="tin" class="formal-input" value="<?= esc($profile->tin) ?>" placeholder="e.g. 123-456-789-000"></div>
                                <div class="col-md-6">
                                    <label class="formal-label">Business Address</label>
                                    <textarea name="address" class="formal-input" rows="1" style="resize:vertical;"><?= esc($profile->address) ?></textarea>
                                </div>
                            </div>

                            <hr>
                            <p class="fw-bold mb-2 mt-3" style="font-size:12px;">Product Categories You Supply *</p>
                            <div id="categoryChipBox" class="p-3 rounded-3 border mb-2 d-flex flex-wrap gap-2" style="min-height:48px;">
                                <span id="emptyCatMsg" class="text-muted" style="font-size:11px; display:none;">No categories added yet.</span>
                            </div>
                            <div class="d-flex gap-2 flex-wrap mb-4">
                                <select id="categoryPicker" class="formal-input mb-0" style="flex:1; min-width:220px;">
                                    <option value="">+ Choose a category to add…</option>
                                    <?php foreach($all_categories as $cat): ?>
                                        <option value="<?= $cat['category_id'] ?>" data-name="<?= esc($cat['name']) ?>"><?= esc($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="btnAddCategoryChip" class="btn btn-dark rounded-pill px-4">Add</button>
                            </div>
                            <div id="hiddenCategoryInputs"></div>

                            <hr>
                            <p class="fw-bold mb-3 mt-3" style="font-size:12px;">Business Permit / DTI Registration</p>
                            <?php if (!empty($profile->permit_path)): ?>
                                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 border mb-2" style="background:#f8f9fa;">
                                    <span style="font-size:11px;"><i class="fas fa-file-invoice me-2 text-success"></i>File on record</span>
                                    <a href="<?= base_url($profile->permit_path) ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill px-3">View File</a>
                                </div>
                            <?php else: ?>
                                <div class="p-3 rounded-3 border mb-2 text-muted" style="font-size:11px; background:#f8f9fa;">No permit on file yet.</div>
                            <?php endif; ?>
                            <div class="upload-dashed-box text-center p-3 mb-4" id="dropZone">
                                <input type="file" name="permit" id="permitFile" class="d-none" accept=".pdf,.jpg,.jpeg,.png" onchange="updatePermitFileName()">
                                <label for="permitFile" style="cursor: pointer;">
                                    <i class="fas fa-upload text-muted mb-1"></i>
                                    <p class="mb-0 fw-bold small" id="permitFileNameDisplay"><?= !empty($profile->permit_path) ? 'Click to replace with a new file' : 'Click to upload' ?></p>
                                    <small class="text-muted" style="font-size: 8px;">PDF, JPG, PNG · Max 5MB</small>
                                </label>
                            </div>

                            <hr>
<p class="fw-bold mb-3 mt-3" style="font-size:12px;">Bank Payment Details</p>
<div class="row g-3 mb-4">
    <div class="col-md-6"><label class="formal-label">Bank Name</label><input type="text" name="bank_name" class="formal-input" value="<?= esc($profile->bank_name) ?>"></div>
    <div class="col-md-6"><label class="formal-label">Account Name</label><input type="text" name="bank_account_name" class="formal-input" value="<?= esc($profile->bank_account_name) ?>"></div>
    <div class="col-md-6"><label class="formal-label">Account Number</label><input type="text" name="bank_account_number" class="formal-input" value="<?= esc($profile->bank_account_number) ?>"></div>
</div>

                            <hr>
                            <p class="fw-bold mb-3 mt-3" style="font-size:12px;">Change Password (optional)</p>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6"><label class="formal-label">New Password</label><input type="password" name="password" class="formal-input" placeholder="Leave blank to keep current" minlength="8"></div>
                                <div class="col-md-6"><label class="formal-label">Confirm New Password</label><input type="password" name="confirm_password" class="formal-input" minlength="8"></div>
                            </div>

                            <button type="submit" class="btn btn-dark px-2 py-2 fw-bold rounded-pill shadow">✓ SAVE CHANGES</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="inventory-kpi-card">
                            <small class="text-muted fw-bold d-block mb-1">TOTAL PURCHASE ORDERS</small>
                            <h3 class="fw-bold mb-0"><?= $kpis['total_pos'] ?></h3>
                        </div>
                        <div class="inventory-kpi-card">
                            <small class="text-muted fw-bold d-block mb-1">RECEIVED / FULFILLED</small>
                            <h3 class="fw-bold mb-0"><?= $kpis['received_pos'] ?></h3>
                        </div>
                        <div class="inventory-kpi-card">
                            <small class="text-muted fw-bold d-block mb-1">ON-TIME DELIVERY RATE</small>
                            <h3 class="fw-bold mb-0"><?= $scorecard->on_time_rate !== null ? $scorecard->on_time_rate . '%' : 'No data yet' ?></h3>
                        </div>
                        <div class="inventory-kpi-card">
                            <small class="text-muted fw-bold d-block mb-1">PARTNER SINCE</small>
                            <h3 class="fw-bold mb-0"><?= $profile->created_at ? date('M Y', strtotime($profile->created_at)) : 'This month' ?></h3>
                        </div>
                        <div class="inventory-kpi-card">
                            <small class="text-muted fw-bold d-block mb-1">ACCOUNT STATUS</small>
                            <h3 class="fw-bold mb-0 text-success">ACTIVE</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.category-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: #1a0505; color: #fff; border-radius: 999px;
    padding: 5px 8px 5px 14px; font-size: 11px; font-weight: 600;
}
.category-chip .chip-remove {
    cursor: pointer; background: rgba(255,255,255,0.2); border: none; color: #fff;
    width: 18px; height: 18px; border-radius: 50%; font-size: 11px; line-height: 1;
    display: flex; align-items: center; justify-content: center;
}
.category-chip .chip-remove:hover { background: rgba(255,255,255,0.4); }
</style>

<script>
    setTimeout(function() {
        ['flashError', 'flashSuccess'].forEach(function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }
        });
    }, 5000);
</script>

<script>
    // seed with the categories this supplier already has selected
    let categories = <?= json_encode(array_map(function($c) {
        return ['id' => (int) $c['category_id'], 'name' => $c['name']];
    }, $selected_categories)) ?>;

    const chipBox = document.getElementById('categoryChipBox');
    const emptyCatMsg = document.getElementById('emptyCatMsg');
    const hiddenInputsWrap = document.getElementById('hiddenCategoryInputs');
    const categoryPicker = document.getElementById('categoryPicker');

    function renderChips() {
        chipBox.querySelectorAll('.category-chip').forEach(el => el.remove());
        emptyCatMsg.style.display = categories.length === 0 ? 'block' : 'none';

        categories.forEach((cat, index) => {
            const chip = document.createElement('span');
            chip.className = 'category-chip';
            chip.textContent = cat.name;
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'chip-remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', () => {
                categories.splice(index, 1);
                renderChips();
            });
            chip.appendChild(removeBtn);
            chipBox.appendChild(chip);
        });

        // disable already-added options in the dropdown so they can't be added twice
        Array.from(categoryPicker.options).forEach(opt => {
            if (!opt.value) return;
            opt.disabled = categories.some(c => c.id === parseInt(opt.value, 10));
        });

        // rebuild hidden inputs used on submit
        hiddenInputsWrap.innerHTML = '';
        categories.forEach(cat => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'existing_categories[]';
            input.value = cat.id;
            hiddenInputsWrap.appendChild(input);
        });
    }

    document.getElementById('btnAddCategoryChip').addEventListener('click', function() {
        const pickedOption = categoryPicker.options[categoryPicker.selectedIndex];
        if (!pickedOption || !pickedOption.value) {
            alert('Please choose a category from the list first.');
            return;
        }
        categories.push({ id: parseInt(pickedOption.value, 10), name: pickedOption.dataset.name });
        categoryPicker.value = '';
        renderChips();
    });

    document.getElementById('profileForm').addEventListener('submit', function(e) {
        if (categories.length === 0) {
            e.preventDefault();
            alert('Please add at least one product category before saving.');
        }
    });

    renderChips();

    // ---- avatar + permit preview ----
    document.getElementById('avatarInput').addEventListener('change', function() {
        if (this.files.length > 0) document.getElementById('avatarPreview').src = URL.createObjectURL(this.files[0]);
    });
    function updatePermitFileName() {
        const input = document.getElementById('permitFile');
        const display = document.getElementById('permitFileNameDisplay');
        if (input.files.length > 0) {
            display.innerText = "✓ " + input.files[0].name;
            display.style.color = "#087b38";
        }
    }
</script>

<?= view('partials/supplier/footer') ?>
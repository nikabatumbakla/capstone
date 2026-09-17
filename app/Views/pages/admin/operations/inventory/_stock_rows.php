<?php foreach($inventory as $row): 
    $isLow = $row['total_stock'] <= $row['reorder_level'];
    $isNearExpiry = $row['nearest_expiry'] && strtotime($row['nearest_expiry']) <= strtotime('+6 months') && strtotime($row['nearest_expiry']) >= strtotime('today');
?>
<tr>
    <td class="ps-4">
        <span class="fw-bold d-block"><?= esc($row['product_name']) ?></span>
        <small class="text-muted"><?= $row['batch_count'] ?> batch<?= $row['batch_count'] != 1 ? 'es' : '' ?> on record</small>
    </td>
    <td><code><?= esc($row['barcode_value'] ?: '—') ?></code></td>
    <td><span class="badge bg-light text-dark border"><?= esc($row['category_name']) ?></span></td>
    <td>
        <?php if($row['batch_count'] > 1): ?>
            <span class="text-muted">Multiple</span>
        <?php elseif($row['batch_count'] == 1): ?>
            <span class="text-muted">1 batch</span>
        <?php else: ?>
            <span class="text-muted">—</span>
        <?php endif; ?>
    </td>
    <td class="fw-bold <?= $isLow ? 'text-danger' : '' ?>"><?= $row['total_stock'] ?></td>
    <td>
        <?php if($isLow): ?>
            <span class="badge bg-danger">Low Stock</span>
        <?php elseif($isNearExpiry): ?>
            <span class="badge bg-warning text-dark">Near Expiry</span>
        <?php else: ?>
            <span class="badge bg-success">OK</span>
        <?php endif; ?>
    </td>
    <td class="text-center">
    <button class="btn btn-xs btn-outline-dark rounded-circle btn-view" data-id="<?= $row['pid'] ?>" title="View" style="width:30px; height:30px;"><i class="fas fa-eye"></i></button>
    <button class="btn btn-xs btn-outline-secondary rounded-circle btn-edit" data-id="<?= $row['pid'] ?>" title="Edit Info" style="width:30px; height:30px;"><i class="fas fa-edit"></i></button>
    <button class="btn btn-xs btn-outline-maroon rounded-circle btn-add-stock" data-pid="<?= $row['pid'] ?>" title="Add Stock" style="width:30px; height:30px;"><i class="fas fa-plus"></i></button>
    <?php if($row['total_stock'] <= 0): ?>
        <a href="<?= base_url('admin/inventory/delete-product/'.$row['pid']) ?>"
           class="btn btn-xs btn-outline-danger rounded-circle"
           title="Delete Product (0 stock)"
           style="width:30px; height:30px;"
           onclick="return confirm('This product has zero stock. Delete it permanently? If it has past order history, this will be blocked automatically.')">
            <i class="fas fa-trash"></i>
        </a>
    <?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php if(empty($inventory)): ?>
<tr><td colspan="7" class="text-center py-5 text-muted">No products found.</td></tr>
<?php endif; ?>
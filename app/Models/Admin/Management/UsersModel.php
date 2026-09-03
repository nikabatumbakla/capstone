<?php

namespace App\Models\Admin\Management;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

public function getCounts(): array
{
    $registryBase = fn() => $this->db->table('users')->groupStart()
        ->whereIn('role', ['admin', 'staff', 'customer'])
        ->orWhere('is_verified', 1)
        ->groupEnd();

    return [
        'active'    => $registryBase()->where('is_active', 1)->countAllResults(),
        'staff'     => $this->db->table('users')->where('role', 'staff')->countAllResults(),
        'clients'   => $this->db->table('users')->where('role', 'institutional_client')->where('is_verified', 1)->countAllResults(),
        'suppliers' => $this->db->table('users')->where('role', 'supplier')->where('is_verified', 1)->countAllResults(),
        'pending'   => $this->db->table('users')->whereIn('role', ['supplier', 'institutional_client'])->where('is_verified', 0)->countAllResults(),
    ];
}

// Established registry — never includes an unreviewed application
public function getRegistry(string $roleGroup = '', string $search = '', int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function ($b) use ($roleGroup, $search) {
        $b->groupStart()->whereIn('role', ['admin', 'staff', 'customer'])->orWhere('is_verified', 1)->groupEnd();
        if ($roleGroup === 'staff') $b->where('role', 'staff');
        if ($roleGroup === 'clients') $b->where('role', 'institutional_client');
        if ($roleGroup === 'suppliers') $b->where('role', 'supplier');
        if ($search !== '') $b->groupStart()->like('full_name', $search)->orLike('email', $search)->groupEnd();
        return $b;
    };

    $countBuilder = $this->db->table('users');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('users');
    $apply($builder);
    $builder->orderBy('role', 'ASC')->orderBy('full_name', 'ASC')->limit($perPage, $offset);

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

// Applications awaiting their FIRST-EVER decision — separate list entirely
public function getPendingApplications(string $roleFilter = '', int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;
    $apply = function ($b) use ($roleFilter) {
        $b->whereIn('role', ['supplier', 'institutional_client'])->where('is_verified', 0);
        if ($roleFilter !== '') $b->where('role', $roleFilter);
        return $b;
    };

    $countBuilder = $this->db->table('users');
    $apply($countBuilder);
    $total = $countBuilder->countAllResults();

    $builder = $this->db->table('users');
    $apply($builder);
    $builder->orderBy('created_at', 'ASC')->limit($perPage, $offset); // oldest applications first

    return ['data' => $builder->get()->getResultArray(), 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage))];
}

    // Pulls the user row PLUS whichever role-specific profile table applies
    public function getUserDetails(int $id)
    {
        $user = $this->db->table('users')->where('user_id', $id)->get()->getRow();
        if (!$user) return null;
        unset($user->password_hash);

        $user->staff_profile = null;
        $user->customer_profile = null;
        $user->supplier_profile = null;
        $user->client_profile = null;

        switch ($user->role) {
            case 'staff':
                $user->staff_profile = $this->db->table('staff_profiles')->where('user_id', $id)->get()->getRow();
                break;
            case 'customer':
                $user->customer_profile = $this->db->table('customer_profiles')->where('user_id', $id)->get()->getRow();
                break;
            case 'supplier':
                $user->supplier_profile = $this->db->table('suppliers')->where('user_id', $id)->get()->getRow();
                break;
            case 'institutional_client':
                $user->client_profile = $this->db->table('institutional_clients')->where('user_id', $id)->get()->getRow();
                break;
        }
        return $user;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $builder = $this->db->table('users')->where('email', $email);
        if ($excludeId) $builder->where('user_id !=', $excludeId);
        return $builder->countAllResults() > 0;
    }

    public function employeeIdExists(string $empId, ?int $excludeUserId = null): bool
    {
        $builder = $this->db->table('staff_profiles')->where('employee_id', $empId);
        if ($excludeUserId) $builder->where('user_id !=', $excludeUserId);
        return $builder->countAllResults() > 0;
    }

    // Creates/updates the account AND its matching role-specific profile row, in one transaction.
    // Whichever role is selected gets exactly one matching profile table kept in sync — never orphaned.
    public function saveAccount(array $userPayload, array $profilePayload, ?int $id = null): int
    {
        $this->db->transStart();

        if ($id) {
            $this->db->table('users')->where('user_id', $id)->update($userPayload);
            $userId = $id;
        } else {
            $this->db->table('users')->insert($userPayload);
            $userId = $this->db->insertID();
        }

        $role = $userPayload['role'];

        if ($role === 'staff' && !empty($profilePayload)) {
            $this->upsertProfile('staff_profiles', 'profile_id', $userId, $profilePayload);
        }
        if ($role === 'customer' && !empty($profilePayload)) {
            $this->upsertProfile('customer_profiles', 'profile_id', $userId, $profilePayload);
        }
        if ($role === 'supplier' && !empty($profilePayload)) {
            $this->upsertProfile('suppliers', 'supplier_id', $userId, $profilePayload, ['is_active' => 1]);
        }
        if ($role === 'institutional_client' && !empty($profilePayload)) {
            $this->upsertProfile('institutional_clients', 'client_id', $userId, $profilePayload, ['is_active' => 1]);
        }

        $this->db->transComplete();
        return $userId;
    }

    private function upsertProfile(string $table, string $pk, int $userId, array $payload, array $insertExtras = []): void
    {
        $existing = $this->db->table($table)->where('user_id', $userId)->get()->getRow();
        $payload['user_id'] = $userId;
        if ($existing) {
            $this->db->table($table)->where($pk, $existing->$pk)->update($payload);
        } else {
            $this->db->table($table)->insert(array_merge($payload, $insertExtras));
        }
    }

    public function removeAccount(int $id): void
    {
        // Deactivate, don't hard-delete: users are referenced across orders, POs, logs, alerts, etc.
        $this->db->table('users')->where('user_id', $id)->update(['is_active' => 0]);
    }

    public function getStaffModules(): array
    {
        return ['inventory', 'sales', 'pos', 'procurement', 'reports'];
    }

    public function updateAccessFlags(int $id, bool $isActive, bool $isVerified, ?string $notes = null): void
{
    $this->db->table('users')->where('user_id', $id)->update([
        'is_active'   => $isActive ? 1 : 0,
        'is_verified' => $isVerified ? 1 : 0,
        'verification_notes' => $notes,
    ]);
}


// Checks whether this user has any real activity tied to their account across the system.
// If they do, hard-deleting would corrupt foreign-key history — must deactivate instead.
public function hasActivity(int $userId): bool
{
    $checks = [
        ['table' => 'sales_orders', 'column' => 'created_by'],
        ['table' => 'purchase_orders', 'column' => 'created_by'],
        ['table' => 'stock_movements', 'column' => 'scanned_by'],
        ['table' => 'stock_adjustment_logs', 'column' => 'adjusted_by'],
        ['table' => 'pos_transactions', 'column' => 'cashier_id'],
        ['table' => 'alerts', 'column' => 'assigned_to'],
        ['table' => 'goods_receipts', 'column' => 'received_by'],
        ['table' => 'sales_returns', 'column' => 'processed_by'],
        ['table' => 'procurement_returns', 'column' => 'processed_by'],
    ];

    foreach ($checks as $check) {
        if ($this->db->table($check['table'])->where($check['column'], $userId)->countAllResults() > 0) {
            return true;
        }
    }
    return false;
}

// Permanently removes the account. Also clears any linked supplier/client profile row.
// Only call this after hasActivity() confirms it's safe.
public function hardDeleteAccount(int $userId): void
{
    $this->db->transStart();
    $this->db->transException(true); // makes failures throw instead of silently rolling back
    $this->db->table('terms_agreement_log')->where('user_id', $userId)->delete();
    $this->db->table('suppliers')->where('user_id', $userId)->delete();
    $this->db->table('institutional_clients')->where('user_id', $userId)->delete();
    $this->db->table('staff_profiles')->where('user_id', $userId)->delete();
    $this->db->table('customer_profiles')->where('user_id', $userId)->delete();
    $this->db->table('users')->where('user_id', $userId)->delete();
    $this->db->transComplete();
}

public function getOwnProfile(int $userId)
{
    $user = $this->db->table('users')->where('user_id', $userId)->get()->getRow();
    if ($user) unset($user->password_hash);
    return $user;
}

public function updateOwnProfile(int $userId, array $payload): void
{
    $this->db->table('users')->where('user_id', $userId)->update($payload);
}

public function updateAvatar(int $userId, string $path): void
{
    $this->db->table('users')->where('user_id', $userId)->update(['avatar_path' => $path]);
}

public function getPendingCategoriesForSupplier(int $supplierId): array
{
    return $this->db->table('supplier_categories as sc')
        ->select('c.category_id, c.name, c.is_active')
        ->join('categories as c', 'c.category_id = sc.category_id')
        ->where('sc.supplier_id', $supplierId)
        ->where('c.is_active', 0)
        ->get()->getResultArray();
}

public function activateCategory(int $categoryId): void
{
    $this->db->table('categories')->where('category_id', $categoryId)->update(['is_active' => 1]);
}

}
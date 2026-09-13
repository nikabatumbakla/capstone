<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class GuestPartyModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    // Reuses an existing walk-in record on an exact (case-insensitive) name match —
    // keeps a repeat walk-in partner from fragmenting into duplicate rows across orders.
    public function findOrCreateGuestSupplier(array $data): int
    {
        $name = trim($data['name'] ?? '');
        $existing = $this->db->table('guest_suppliers')->where('LOWER(name)', strtolower($name))->get()->getRow();
        if ($existing) return (int) $existing->guest_supplier_id;

        $this->db->table('guest_suppliers')->insert([
            'name'           => $name,
            'contact_person' => $data['contact_person'] ?: null,
            'phone'          => $data['phone'] ?: null,
            'email'          => $data['email'] ?: null,
            'address'        => $data['address'] ?: null,
            'tin'            => $data['tin'] ?: null,
        ]);
        return (int) $this->db->insertID();
    }

    public function findOrCreateGuestClient(array $data): int
    {
        $name = trim($data['name'] ?? '');
        $existing = $this->db->table('guest_clients')->where('LOWER(name)', strtolower($name))->get()->getRow();
        if ($existing) return (int) $existing->guest_client_id;

        $this->db->table('guest_clients')->insert([
            'name'           => $name,
            'contact_person' => $data['contact_person'] ?: null,
            'phone'          => $data['phone'] ?: null,
            'email'          => $data['email'] ?: null,
            'address'        => $data['address'] ?: null,
            'tin'            => $data['tin'] ?: null,
        ]);
        return (int) $this->db->insertID();
    }
}
<?php

namespace App\Libraries;

class ScorecardCalculator
{
    public static function calculate($db, int $supplierId): object
    {
        $received = $db->table('purchase_orders')
            ->select('po_id, expected_date, received_date, created_at')
            ->where('supplier_id', $supplierId)
            ->whereIn('status', ['received', 'partial'])
            ->where('received_date IS NOT NULL', null, false)
            ->get()->getResultArray();

        $totalOrders = count($received);

        if ($totalOrders === 0) {
            return (object) [
                'total_orders'         => 0,
                'on_time_rate'         => null,
                'accuracy_rate'        => null,
                'avg_lead_time_actual' => null,
            ];
        }

        $onTimeCount = 0;
        $leadTimes = [];
        foreach ($received as $po) {
            if ($po['expected_date'] && $po['received_date'] <= $po['expected_date']) {
                $onTimeCount++;
            }
            if ($po['created_at']) {
                $leadTimes[] = (strtotime($po['received_date']) - strtotime($po['created_at'])) / 86400;
            }
        }

        $poIds = array_column($received, 'po_id');
        $totalGrrs    = $db->table('goods_receipts')->whereIn('po_id', $poIds)->countAllResults();
        $accurateGrrs = $db->table('goods_receipts')->whereIn('po_id', $poIds)->where('status', 'complete')->countAllResults();

        return (object) [
            'total_orders'         => $totalOrders,
            'on_time_rate'         => ($onTimeCount / $totalOrders) * 100,
            'accuracy_rate'        => $totalGrrs > 0 ? ($accurateGrrs / $totalGrrs) * 100 : null,
            'avg_lead_time_actual' => !empty($leadTimes) ? array_sum($leadTimes) / count($leadTimes) : null,
        ];
    }
}
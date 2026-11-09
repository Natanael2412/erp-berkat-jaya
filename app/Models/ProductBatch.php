<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Entitas stok per rol fisik (batch) untuk kabel/meteran.
 * Setiap rol yang diterima saat Inbound PO dicatat sebagai baris terpisah
 * agar kasir dapat memilih & memotong dari rol fisik yang spesifik.
 */
class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'goods_receipt_id',
        'purchase_order_item_id',
        'batch_sku',
        'initial_qty',
        'remaining_qty',
        'unit_cost',
        'received_date',
        'is_active',
    ];

    protected $casts = [
        'initial_qty' => 'integer',
        'remaining_qty' => 'integer',
        'unit_cost' => 'decimal:4',
        'received_date' => \App\Casts\DateOnly::class,
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class, 'product_batch_id');
    }
}


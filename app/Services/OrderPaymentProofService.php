<?php

namespace App\Services;

use App\Models\OrderPaymentProof;
use App\Utils\ImageManager;
use Illuminate\Http\UploadedFile;

class OrderPaymentProofService
{
    /**
     * Keeps the transfer screenshot uploaded by whoever marked the order paid online.
     */
    public function store(int $orderId, UploadedFile $image, string $uploadedByType, ?int $uploadedById, ?string $uploadedByName): OrderPaymentProof
    {
        return OrderPaymentProof::create([
            'order_id' => $orderId,
            'image' => ImageManager::upload(OrderPaymentProof::IMAGE_DIRECTORY.'/', 'webp', $image),
            'storage' => config('filesystems.disks.default') ?? 'public',
            'uploaded_by_type' => $uploadedByType,
            'uploaded_by_id' => $uploadedById,
            'uploaded_by_name' => $uploadedByName,
        ]);
    }
}

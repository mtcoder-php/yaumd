<?php

namespace App\Traits;

use App\Services\AuditService;

trait Auditable
{
    public static function bootAuditable(): void
    {
        // Yaratilganda
        static::created(function ($model) {
            AuditService::log(
                'created',
                $model,
                null,
                $model->toArray()
            );
        });

        // Yangilanganda
        static::updated(function ($model) {
            AuditService::log(
                'updated',
                $model,
                $model->getOriginal(),
                $model->getChanges()
            );
        });

        // O'chirilganda
        static::deleted(function ($model) {
            AuditService::log(
                'deleted',
                $model,
                $model->toArray(),
                null
            );
        });
    }
}

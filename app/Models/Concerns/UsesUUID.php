<?php

namespace IronGate\Pkgtrends\Models\Concerns;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait UsesUUID
{
    public static function bootUsesUUID(): void
    {
        self::creating(function (self $entity) {
            if (empty($entity->getAttribute($entity->getUUIDAttributeName()))) {
                $entity->setAttribute($entity->getUUIDAttributeName(), Uuid::uuid4()->toString());
            }
        });
    }

    public function initializeUsesUUID(): void
    {
        $this->keyType      = 'string';
        $this->incrementing = false;
    }

    public static function findByUuid($uuid): ?self
    {
        return self::query()->where((new self)->getUUIDAttributeName(), $uuid)->first();
    }

    public static function findOrFailByUuid($uuid): self
    {
        $entity = self::findByUuid($uuid);

        if ($entity === null) {
            throw (new ModelNotFoundException)->setModel(self::class, $uuid);
        }

        return $entity;
    }

    public function getUUIDAttributeName(): string
    {
        return $this->getKeyName();
    }
}

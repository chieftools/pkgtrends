<?php

namespace IronGate\Pkgtrends\Models\Traits;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;

trait UsesUUID
{
    /**
     * Boot the uses UUID trait.
     */
    public static function bootUsesUUID()
    {
        self::creating(function (Model $entity) {
            if (empty($entity->getAttribute($entity->getUUIDAttributeName()))) {
                $entity->setAttribute($entity->getUUIDAttributeName(), Uuid::uuid4()->toString());
            }
        });
    }

    /**
     * Find by the UUID attribute.
     *
     * @param string $uuid
     *
     * @return mixed
     */
    public static function findByUuid($uuid)
    {
        $entity = new self;

        return $entity->query()->where($entity->getUUIDAttributeName(), $uuid)->first();
    }

    /**
     * Find or fail by the UUID attribute.
     *
     * @param string $uuid
     *
     * @return mixed
     */
    public static function findOrFailByUuid($uuid)
    {
        $entity = new self;

        return $entity->query()->where($entity->getUUIDAttributeName(), $uuid)->firstOrFail();
    }

    /**
     * Get the UUID attribute name.
     *
     * @return string
     */
    public function getUUIDAttributeName()
    {
        return $this->getKeyName();
    }
}

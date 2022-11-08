<?php
namespace ResourceTree\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Item;

/**
 *
 * @Entity
 */
class LinkedItem extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    /**
     *
     * @Column(type="integer")
     */
    protected $resourceClassId;
    /**
     *
     * @Column(type="integer")
     */
    protected $parentItemId;
    /**
     *
     * @Column(type="integer")
     */
    protected $parentResourceClassId;
    /**
     * @return mixed
     */
    public function getParentResourceClassId()
    {
        return $this->parentResourceClassId;
    }

    /**
     * @return mixed
     */
    public function getParentItemId()
    {
        return $this->parentItemId;
    }

    /**
     * @return mixed
     */
    public function getResourceClassId()
    {
        return $this->resourceClassId;
    }

    public function getId()
    {
        return $this->id;
    }


}

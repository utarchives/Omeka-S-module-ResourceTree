<?php
namespace ResourceTree\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Item;

/**
 *
 * @Entity
 */
class RootItem extends AbstractEntity
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

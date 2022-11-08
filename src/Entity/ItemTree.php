<?php
namespace ResourceTree\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Item;

/**
 *
 * @Entity
 */
class ItemTree extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\Item")
     * @JoinColumn(nullable=false)
     */
    protected $parentItem;
    /**
     * @ManyToOne(targetEntity="Omeka\Entity\Item")
     * @JoinColumn(nullable=false)
     */
    protected $childItem;
    /**
     *
     * @Column(type="integer")
     */
    protected $depth;
    /**
     *
     * @Column(type="boolean")
     */
    protected $isHere;
    /**
     *
     * @Column(type="boolean")
     */
    protected $isParent;

    /**
     * @return mixed
     */
    public function getIsParent()
    {
        return $this->isParent;
    }

    /**
     * @param mixed $isParent
     */
    public function setIsParent($isParent)
    {
        $this->isParent = $isParent;
    }

    /**
     * @return mixed
     */
    public function getIsHere()
    {
        return $this->isHere;
    }

    /**
     * @param mixed $isHere
     */
    public function setIsHere($isHere)
    {
        $this->isHere = $isHere;
    }

    /**
     * @return mixed
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param mixed $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * @return mixed
     */
    public function getParentItem()
    {
        return $this->parentItem;
    }

    /**
     * @return mixed
     */
    public function getChildItem()
    {
        return $this->childItem;
    }

    public function getId()
    {
        return $this->id;
    }


}

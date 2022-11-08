<?php
namespace ResourceTree\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\ResourceClass;
use Omeka\Entity\ResourceTemplate;
use Omeka\Entity\User;
use DateTime;

/**
 *
 * @Entity
 */
class ChildItem extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     */
    protected $id;
    /**
     * @OneToOne(targetEntity="Omeka\Entity\Item")
     * @JoinColumn(nullable=false)
     */
    protected $item;
    /**
     * @Column(type="integer")
     */
    protected $sort;
    /**
     * @Column(type="boolean")
     */
    protected $isHere;
    /**
     * @Column(type="integer")
     */
    protected $depth;
    /**
     * @Column(type="integer")
     */
    protected $parentItemId;
    /**
     * @ManyToOne(targetEntity="Omeka\Entity\ResourceClass", inversedBy="resources")
     * @JoinColumn(onDelete="SET NULL")
     */
    protected $resourceClass;
    /**
     * @Column(type="integer")
     */
    protected $targetResourceClassId;
    /**
     * @Column(type="string")
     */
    protected $title;
    /**
     * @OneToMany(
     *     targetEntity="Omeka\Entity\Value",
     *     mappedBy="resource",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove", "detach"}
     * )
     * @OrderBy({"id" = "ASC"})
     */
    protected $values;
    /**
     * @ManyToOne(targetEntity="Omeka\Entity\ResourceTemplate")
     * @JoinColumn(onDelete="SET NULL")
     */
    protected $resourceTemplate;
    /**
     * @return mixed
     */
    public function getTargetResourceClassId()
    {
        return $this->targetResourceClassId;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @return mixed
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getItem()
    {
        return $this->item;
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
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @return mixed
     */
    public function getIsHere()
    {
        return $this->isHere;
    }

    /**
     * @return mixed
     */
    public function getDepth()
    {
        return $this->depth;
    }

    public function __construct()
    {
        $this->values = new ArrayCollection;
        $this->media = new ArrayCollection;
        $this->siteBlockAttachments = new ArrayCollection;
        $this->itemSets = new ArrayCollection;
    }
    public function getResourceTemplate()
    {
        return $this->resourceTemplate;
    }
    public function getId()
    {
        return $this->id;
    }

}

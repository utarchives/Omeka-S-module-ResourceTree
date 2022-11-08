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
class ParentItem extends AbstractEntity
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
    protected $childItemId;
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
    public function getChildItemId()
    {
        return $this->childItemId;
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

    public function getResourceName()
    {
        return 'items';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function getSiteBlockAttachments()
    {
        return $this->siteBlockAttachments;
    }

    //     public function getItemSets()
    //     {
    //         return $this->itemSets;
    //     }
        public function setOwner(User $owner = null)
        {
            $this->owner = $owner;
        }

        public function getOwner()
        {
            return $this->owner;
        }

        public function setResourceClass(ResourceClass $resourceClass = null)
        {
            $this->resourceClass = $resourceClass;
        }

        public function getResourceClass()
        {
            return $this->resourceClass;
        }

        public function setResourceTemplate(ResourceTemplate $resourceTemplate = null)
        {
            $this->resourceTemplate = $resourceTemplate;
        }

        public function getResourceTemplate()
        {
            return $this->resourceTemplate;
        }

        public function setIsPublic($isPublic)
        {
            $this->isPublic = (bool) $isPublic;
        }

        public function isPublic()
        {
            return (bool) $this->isPublic;
        }

        public function setCreated(DateTime $dateTime)
        {
            $this->created = $dateTime;
        }

        public function getCreated()
        {
            return $this->created;
        }

        public function setModified(DateTime $dateTime)
        {
            $this->modified = $dateTime;
        }

        public function getModified()
        {
            return $this->modified;
        }

        public function getValues()
        {
            return $this->values;
        }
}

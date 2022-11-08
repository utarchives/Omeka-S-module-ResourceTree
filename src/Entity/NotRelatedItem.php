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
class NotRelatedItem extends AbstractEntity
{

    /**
     * @Id
     * @Column(type="integer")
     */
    protected $id;
    /**
     * @ManyToOne(targetEntity="Omeka\Entity\User")
     * @JoinColumn(onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\ResourceClass", inversedBy="resources")
     * @JoinColumn(onDelete="SET NULL")
     */
    protected $resourceClass;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\ResourceTemplate")
     * @JoinColumn(onDelete="SET NULL")
     */
    protected $resourceTemplate;

    /**
     * @Column(type="boolean")
     */
    protected $isPublic = true;

    /**
     * @Column(type="datetime")
     */
    protected $created;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $modified;

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
     * @OneToMany(
     *     targetEntity="Omeka\Entity\Media",
     *     mappedBy="item",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove", "detach"},
     *     indexBy="id"
     * )
     * @OrderBy({"position" = "ASC"})
     */
    protected $media;

    /**
     * @OneToMany(targetEntity="Omeka\Entity\SiteBlockAttachment", mappedBy="item")
     */
    protected $siteBlockAttachments;

    public function __construct()
    {
        $this->values = new ArrayCollection;
        $this->media = new ArrayCollection;
        $this->siteBlockAttachments = new ArrayCollection;
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

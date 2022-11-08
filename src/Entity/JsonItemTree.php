<?php
namespace ResourceTree\Entity;

use Omeka\Entity\AbstractEntity;
/**
 *
 * @Entity
 */
class JsonItemTree extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     *
     * @Column(type="text")
     */
    protected $itemTree;
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getItemTree()
    {
        return $this->itemTree;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param mixed $itemTree
     */
    public function setItemTree($itemTree)
    {
        $this->itemTree = $itemTree;
    }






}

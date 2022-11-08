<?php
namespace ResourceTree\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Omeka\Api\Manager as Api;

class ResourceTreeGenerator extends AbstractPlugin
{
    protected $api;
    protected $relatedItemsData;
    protected $items;
    protected $targetProperty;
    protected $viewManager;
    protected $connection;
    protected $setting;
    /**
     * @return  <array, mixed>
     */
    public function getItems()
    {
        return $this->items;
    }

    public function __construct(
        Api $api,
        RelatedItemsData $relatedItemsData,
        $targetProperty,
        $viewManager,
        $connection,
        $setting
        ) {
            $this->api = $api;
            $this->relatedItemsData = $relatedItemsData;
            $this->items = $this->relatedItemsData->getItems($this->relatedItemsData->getFolderResourceClassId(), true, 100000, 1);
            $this->targetProperty = $targetProperty;
            $this->viewManager = $viewManager;
            $this->connection = $connection;
            $this->setting = $setting;
    }
    /**
     *
     * @param integer $resourceClassId
     * @return string
     */
    protected function getIcon($resourceClassId) {
        switch ($resourceClassId) {
            case $this->relatedItemsData->getFolderResourceClassId():
                return 'fa fa-folder';
            case $this->relatedItemsData->getSearchFolderResourceClassId():
                return 'fa fa-folder';
            case $this->relatedItemsData->getDocumentResourceClassId():
                return 'fa fa-file';
            default:
                return 'fa fa-folder';
        }
    }

    public function createJsonItemTree($siteParameter, $isPublic) {
        $linkTree = $this->getJsTree(false, $siteParameter, $isPublic);
        return $linkTree;
//         $this->api->delete('json_item_trees', []);
//         $this->api->create('json_item_trees', ['item_tree' => json_encode($linkTree)]);
    }
    protected function getIdentity($id) {
//         if (!empty($this->targetProperty)) {
//             $result = $this->getIdentifierByItemId($id);
//             if (empty(strval($result))) {
//                 return $id;
//             }
//             return $result;
//         }
        return $id;
    }
    public function getIdentifierByItemId ($id) {
        $propertyId = (integer) $this->setting->get('cleanurl_item')['property'];
        $prefix = $this->setting->get('cleanurl_identifier_prefix');

        $checkUnspace = false;
        if ($prefix) {
            $bind[] = $prefix . '%';
            // Check prefix with a space and a no-break space.
            $unspace = str_replace([' ', ' '], '', $prefix);
            if ($prefix != $unspace && $this->view->setting('cleanurl_identifier_unspace')) {
                $checkUnspace = true;
                $sqlWhereText = 'AND (value.value LIKE ? OR value.value LIKE ?)';
                $bind[] = $unspace . '%';
            }
            // Normal prefix.
            else {
                $sqlWhereText = 'AND value.value LIKE ?';
            }
        }
        // No prefix.
        else {
            $sqlWhereText = '';
        }

        $sql = "
            SELECT value.value
            FROM value
                LEFT JOIN resource ON (value.resource_id = resource.id)
            WHERE value.property_id = '$propertyId'
                AND resource.id = ?
                $sqlWhereText
            ORDER BY value.id
            LIMIT 1
        ";
        $bind = [
            $id,
        ];
                $identifier = $this->connection->fetchColumn($sql, $bind);

                // Keep only the identifier without the configured prefix.
                if ($identifier) {
                    if ($prefix) {
                        $length = $checkUnspace && strpos($identifier, $unspace) === 0
                        // May be a prefix with space.
                        ? strlen($unspace)
                        // Normal prefix.
                        : strlen($prefix);
                        $identifier = trim(substr($identifier, $length));
                    }
                    return $identifier;
                }

                return '';
    }
    /**
     * Translate jsTree node format to ItemSetTree link.
     *
     * @param array $jstree
     * @return array
     */
    public function fromJstree(array $jstree)
    {
        $buildPages = function ($pagesIn) use (&$buildPages) {
            $pagesOut = [];
            foreach ($pagesIn as $page) {
                if (isset($page['data']['remove']) && $page['data']['remove']) {
                    // Remove pages set to be removed.
                    continue;
                }
                $pagesOut[] = [
                    'type' => $page['data']['type'],
                    'data' => $page['data']['data'],
                    'links' => $page['children'] ? $buildPages($page['children']) : [],
                ];
            }
            return $pagesOut;
        };
        return $buildPages($jstree);
    }
    protected function createSiteParameter($query) {
        $joinSql = "";
        if (isset($query['item_set_id'])) {
            $itemSets = $query['item_set_id'];
            if (!is_array($itemSets)) {
                $itemSets = [$itemSets];
            }
            $itemSets = array_filter($itemSets, 'is_numeric');
            if ($itemSets) {
                $itemSetFilter = implode(',', $itemSets);
                $joinSql .= "inner join item_item_set b on a.id = b.item_id and b.item_set_id in ($itemSetFilter) ";
            }
        }
        if (isset($query['resource_class_id']) && is_numeric($query['resource_class_id'])) {
            $joinSql .= "inner join resource_class c on a.resource_class_id = c.id and c.id = {$query['resource_class_id']} ";
        }
        $returnValue['join'] = $joinSql;
        $returnValue['params'] = [];
        $returnValue['where'] = '';
        if (isset($query['property'])) {
            $propertyQuery = $this->createPropertyQuery($query);
            $returnValue['join'] .= $propertyQuery['join'];
            $returnValue['params'] = $propertyQuery['params'];
            $returnValue['where'] = $propertyQuery['where'];
        }


        return $returnValue;
    }
    protected function createPropertyQuery($query) {
        $joinSql = '';
        $whereSql = '';
        $params = [];
        $count = 0;
        foreach ($query['property'] as $queryRow) {
            if (!(is_array($queryRow)
                && array_key_exists('property', $queryRow)
                && array_key_exists('type', $queryRow)
                )) {
                    continue;
                }
            $propertyId = $queryRow['property'];
            $queryType = $queryRow['type'];
            $joiner = isset($queryRow['joiner']) ? $queryRow['joiner'] : null;
            $value = isset($queryRow['text']) ? $queryRow['text'] : null;

            if (!$value && $queryType !== 'nex' && $queryType !== 'ex') {
                continue;
            }

            $valuesAlias = "z_$count";
            $positive = true;

            switch ($queryType) {
                case 'neq':
                    $params[] = $value;
                    $params[] = $value;
                    $where = " %s ($valuesAlias.value != ? or $valuesAlias.uri != ?) ";
                    break;
                case 'eq':
                    $params[] = $value;
                    $params[] = $value;
                    $where = " %s ($valuesAlias.value = ? or $valuesAlias.uri = ?) ";
                    break;
                case 'nin':
                    $params[] = "%$value%";
                    $params[] = "%$value%";
                    $where = " %s ($valuesAlias.value not like ? or $valuesAlias.uri not like ?) ";
                    break;
                case 'in':
                    $params[] = "%$value%";
                    $params[] = "%$value%";
                    $where = " %s ($valuesAlias.value like ? or $valuesAlias.uri like ?) ";
                    break;
                case 'nex':
                    $where = " %s  $valuesAlias.id is null ";
                case 'ex':
                    $where = " %s  $valuesAlias.id is not null ";
                default:
                    continue 2;
            }
            $joinSql = " left join value $valuesAlias on a.id = $valuesAlias.resource_id ";
            // Narrow to specific property, if one is selected
            if ($propertyId) {
                $joinSql .= " and $valuesAlias.property_id = $propertyId ";
            }

            if ($joiner == 'or') {
                $where = sprintf($where, 'or');
            } else {
                $where = sprintf($where, 'and');
            }
            $whereSql .= $where;
            $count++;
        }
        return ['join' => $joinSql, 'where' => $whereSql, 'params' => $params];
    }
    protected function createParentSql($siteParameter, $isPublic) {

        $whereSql = '';
        if ($isPublic) {
            $whereSql .= ' and a.is_public ';
        }
        $withDocument = (integer) $this->setting->get('resource_tree_with_document_class', true);
        if (!$withDocument) {
            $whereSql .= ' and a.resource_class_id != ' . $this->relatedItemsData->getDocumentResourceClassId();
        }
        $sql = 'select
distinct
a.id,
a.resource_class_id,
a.title
  from parent_item a
  %s
where depth = 1
%s ' . $whereSql;
        $optionSql = $this->createSiteParameter($siteParameter);
        $sql = sprintf($sql, $optionSql['join'], $optionSql['where']);
        return ['sql' => $sql, 'params' => $optionSql['params']];
    }
    protected function createChildSql($siteParameter, $isPublic) {
        $whereSql = '';
        if ($isPublic) {
            $whereSql = ' and a.is_public ';
        }
        $withDocument = (integer) $this->setting->get('resource_tree_with_document_class', true);
        if (!$withDocument) {
            $whereSql .= ' and a.resource_class_id != ' . $this->relatedItemsData->getDocumentResourceClassId();
        }
        $sql = 'select
distinct
a.id,
a.resource_class_id,
a.title
  from child_item a
  %s
where parent_item_id = ? and is_here = 1
  %s ' . $whereSql;
        $optionSql = $this->createSiteParameter($siteParameter);
        $sql = sprintf($sql, $optionSql['join'], $optionSql['where']);
        return ['sql' => $sql, 'params' => $optionSql['params']];
    }
    public function getJsTree($withIcon, $siteParameter, $isPublic)
    {
        $parentQuery = $this->createParentSql($siteParameter, $isPublic);
        $items = $this->connection->fetchAll($parentQuery['sql'], $parentQuery['params'], []);
        $childQuery = $this->createChildSql($siteParameter, $isPublic);
        $itemLinks = function ($items) use (&$itemLinks, $withIcon, $childQuery) {
            $linksOut = [];
            foreach ($items as $item) {
                //                 if(!$this->checkAvailableFolder($notRelatedFolders, $item->item())) {
                //                     continue;
                //                 }
                $params = [];
                $params[] = $item['id'];
                foreach ($childQuery['params'] as $param) {
                    $params[] = $param;
                }
                 $children = $this->connection->fetchAll($childQuery['sql'], $params, []);
                if (count($children) > 0) {
                    $linksOut[] = [
                        'id' => 'resourcetree' . $item['id'],
                        'text' => $item['title'],
                        'icon' => !$withIcon ? false :$this->getIcon($item['resource_class_id']),
                        // "a_attr" => [
                        //     'title' => $item['title'],
                        // ],
                        'data' => [
                            'type' => $item['resource_class_id'],
                            'data' => [
                                'id' => $this->getIdentity($item['id']),
                                //                             'label' => $item['title'],
                            ],
                        ],
                        'children' => $itemLinks($children)
                    ];
                } else {
                    $linksOut[] = [
                        'id' => 'resourcetree' . $item['id'],
                        'text' =>$item['title'],
                        'icon' => !$withIcon ? false : $this->getIcon($item['resource_class_id']),
                        // "a_attr" => [
                        //     'title' => $item['title'],
                        // ],
                        'data' => [
                            'type' => $item['resource_class_id'],
                            'data' => [
                                'id' => $this->getIdentity($item['id']),
                                //                                 'label' => $item['title'],
                                //                                 'property' => $propertyId,
                            ],
                        ],
                    ];
                }

            }
            return $linksOut;
        };
        $links = $itemLinks($items);
        return $links;
    }
    public function getAdminJsTree($withIcon = false)
    {
        $itemLinks = function ($items) use (&$itemLinks, $withIcon) {
            $subjectLinks = function ($parentItem) use (&$subjectLinks, $withIcon) {
                $linksOut = [];
                //                 $response = $this->api->search('child_items', ['parent_item_id' => $parentItem->id(), 'is_here' => true]);
                //                 $items = $response->getContent();
                $sql = 'select
id,
resource_class_id,
title
  from child_item
where parent_item_id = ? and is_here = ?';
                $items = $this->connection->fetchAll($sql, [$parentItem['id'], 1], []);
                foreach ($items as $item) {
                    $propertyId = $this->relatedItemsData->getResourceValuePropertyId($item['id']);
                    $linksOut[] = [
                        'text' => $item['title'],
                        'icon' => !$withIcon ? false : $this->getIcon($item['resource_class_id']),
                        'data' => [
                            'type' => $item['resource_class_id'],
                            'data' => [
                                'id' => $item['id'],
//                                 'label' => $item['title'],
                                'property' => $propertyId,
                            ],
                        ],
                        'children' => $subjectLinks($item)
                    ];
                }
                return $linksOut;
            };
            $linksOut = [];
            foreach ($items as $item) {
                //                 if(!$this->checkAvailableFolder($notRelatedFolders, $item->item())) {
                //                     continue;
                //                 }
                $linksOut[] = [
                    'text' => $item['title'],
                    'icon' => !$withIcon ? false :$this->getIcon($item['resource_class_id']),
                    'data' => [
                        'type' => $item['resource_class_id'],
                        'data' => [
                            'id' => $item['id'],
//                             'label' => $item['title'],
                        ],
                    ],
                    'children' => $subjectLinks($item)
                ];
            }
            return $linksOut;
        };
        $sql = 'select
id,
resource_class_id,
title
  from parent_item
where depth = ?';
        $items = $this->connection->fetchAll($sql, [1], []);
        $links = $itemLinks($items);
        return $links;
    }
}


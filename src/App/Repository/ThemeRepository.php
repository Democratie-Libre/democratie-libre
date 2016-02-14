<?php

namespace App\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class ThemeRepository extends NestedTreeRepository
{
    /**
     * Finds all root themes.
     */
    public function findRoots()
    {
        return $this->findBy(['lvl' => 0]);
    }

    /**
     * Returns an array containing the ids of the children.
     */
    public function getChildrenId($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false)
    {
        $children = $this->getChildren($node, $direct, $sortByField, $direction, $includeNode);

        $childrenId = [];

        foreach ($children as $child) {
            $childrenId[] = $child->getId();
        }

        return $childrenId;
    }
}

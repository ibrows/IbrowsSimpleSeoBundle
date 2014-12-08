<?php

namespace Ibrows\SimpleSeoBundle\Model;

use Doctrine\ORM\EntityManager;

class ContentManager implements ContentManagerInterface
{

    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var
     */
    protected $entityClass;

    /**
     * @param EntityManager $em
     * @param                             $entityClass
     */
    public function __construct(EntityManager $em, $entityClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
    }

    /**
     * @param mixed $entityClass
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;
    }


    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(){
        return $this->em->getRepository($this->entityClass);
    }

    /**
     * @return array
     */
    public function findAllAlias()
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->select('a.pathinfo, a.alias');
        $qb->where('a.alias IS NOT null');
        $qb->addOrderBy('a.id','ASC');
        if($this->em->getClassMetadata($this->entityClass)->hasField('position')){
            $qb->addOrderBy('a.position','ASC');
        }
        try {
            return $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }
    }


    /**
     * @param $key
     * @param $locale
     * @return ContentInterface
     */
    public function findMetaTag($key, $locale = null)
    {
        $criteria = array('keyword'=>$key);
        if($locale && property_exists($this->entityClass,'locale')){
            $criteria['locale']=$locale;
        }
        return $this->getRepository()->findOneBy($criteria);
    }
}

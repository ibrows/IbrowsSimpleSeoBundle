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
    public function findMetaTag($key, $locale)
    {
        return $this->getRepository()->findOneBy(array('key'=>$key,'locale'=>$locale));
    }
}

<?php

namespace Ibrows\SimpleSeoBundle\Model;

use Doctrine\ORM\EntityManager;
use Ibrows\SimpleSeoBundle\Routing\KeyGenerator;
use Symfony\Component\Routing\RouterInterface;

class ContentManager implements ContentManagerInterface
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var KeyGenerator
     */
    protected $generator;

    /**
     * @var RouterInterface
     */
    protected $router;


    /**
     * @param EntityManager $em
     * @param string $entityClass
     * @param KeyGenerator $generator
     * @param RouterInterface $router
     */
    public function __construct(EntityManager $em, $entityClass, KeyGenerator $generator, RouterInterface $router)
    {
        $this->em = $em;
        $this->generator = $generator;
        $this->entityClass = $entityClass;
        $this->router = $router;
    }

    /**
     * @param $alias
     * @param $path
     * @param array $pathParameters
     * @param null $locale
     * @return ContentInterface
     */
    public function createNewAlias($alias, $path, array $pathParameters = array(), $locale = null)
    {
        $rc = new \ReflectionClass($this->entityClass);
        $object = $rc->newInstance();
        $object->setAlias($alias);
        $path = $this->router->generate($path, $pathParameters);
        $path = str_replace('/app_dev.php', '', $path);
        $key = $this->generator->generateMetaTagKeyFromRelativePath($path, $this->router, $locale);
        $object->setKeyword($key);
        return $object;
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
    protected function getRepository()
    {
        return $this->em->getRepository($this->entityClass);
    }

    /**
     * @param $alias
     * @return null|object
     */
    public function findByAlias($alias){
        return $this->getRepository()->findOneBy(array('alias'=>$alias));
    }

    /**
     * @return array
     */
    public function findAllAlias()
    {
        $qb = $this->getRepository()->createQueryBuilder('a');
        $qb->select('a.pathinfo, a.alias');
        $qb->where('a.alias IS NOT null');
        $qb->addOrderBy('a.id', 'ASC');
        if ($this->em->getClassMetadata($this->entityClass)->hasField('position')) {
            $qb->addOrderBy('a.position', 'ASC');
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
        $criteria = array('keyword' => $key);
        if ($locale && property_exists($this->entityClass, 'locale')) {
            $criteria['locale'] = $locale;
        }

        return $this->getRepository()->findOneBy($criteria);
    }
}

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
     * Returns true if there is already an alias of another path+pathParameters+locale
     * @param $alias
     * @param $path
     * @param array $pathParameters
     * @param null $locale
     * @return ContentInterface
     */
    public function checkIsAliasExistsAlready($alias, $path, array $pathParameters = array(), $locale = null)
    {
        $already = $this->findByAlias($alias);
        if ($already) {
            $seo = $this->createNewAlias($alias, $path, $pathParameters, $locale);
            if ($seo->getKeyword() == $already->getKeyword()) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $alias
     * @param $path
     * @param array $pathParameters
     * @param null $locale
     * @return ContentInterface
     */
    public function addOrUpdateAlias($alias, $path, array $pathParameters = array(), $locale = null, $flush = true)
    {
        $seo = $this->createNewAlias($alias, $path, $pathParameters, $locale);
        $allReady = $this->findMetaTag($seo->getKeyword());
        if ($allReady) {
            $allReady->setAlias($alias);
            $this->em->persist($allReady);
        } else {
            $this->em->persist($seo);
        }
        if ($flush) {
            $this->em->flush();
        }
    }


    /**
     * @param  AliasMapperInterface $object
     */
    public function checkAlias(AliasMapperInterface $object)
    {
        $checked = $this->checkIsAliasExistsAlready($object->getAlias(), $object->getFrontendViewRouteName(),$object->getFrontendViewParameters(), $object->getFrontendViewRouteLocale() );
        if ($checked) {
            $object->addError('allready exists');
        }
    }

    /**
     * @param AliasMapperInterface $object
     */
    public function setCurrentAlias(AliasMapperInterface $object)
    {
        $seo = $this->createNewAlias($object->getAlias(), $object->getFrontendViewRouteName(),$object->getFrontendViewParameters(), $object->getFrontendViewRouteLocale() );
        $allReady = $this->findMetaTag($seo->getKeyword());
        if ($allReady) {
            $object->setAlias($allReady->getAlias());
        }
    }

    /**
     * @param AliasMapperInterface $object
     */
    public function addAlias(AliasMapperInterface $object)
    {
        $this->addOrUpdateAlias($object->getAlias(), $object->getFrontendViewRouteName(),$object->getFrontendViewParameters(), $object->getFrontendViewRouteLocale() );
    }

    /**
     * @param EntityManager $em
     */
    public function setEm($em)
    {
        $this->em = $em;
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
    public function findByAlias($alias)
    {
        return $this->getRepository()->findOneBy(array('alias' => $alias));
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

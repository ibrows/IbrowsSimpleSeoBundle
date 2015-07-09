<?php

namespace Ibrows\SimpleSeoBundle\Model;

use Doctrine\ORM\EntityManager;
use Ibrows\SimpleSeoBundle\Routing\KeyGenerator;
use Ibrows\SimpleSeoBundle\Routing\UrlGenerator;
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
     * @var AliasStringGenerator
     */
    protected $aliasStringGenerator;

    /**
     * @var RouterInterface
     */
    protected $router;


    /**
     * @param EntityManager $em
     * @param string $entityClass
     * @param KeyGenerator $generator
     * @param RouterInterface $router
     * @param AliasStringGenerator $stringGenerator
     * @throws \Exception
     */
    public function __construct(EntityManager $em, $entityClass, KeyGenerator $generator, RouterInterface $router, AliasStringGenerator $stringGenerator)
    {
        $this->em = $em;
        if(!$this->implementsContentInterface($entityClass)){
            throw new \Exception("The Entity Class '$entityClass' must implement the 'ContentInterface'");
        }
        $this->entityClass = $entityClass;
        $this->generator = $generator;
        $this->router = $router;
        $this->aliasStringGenerator = $stringGenerator;
    }

    /**
     * @param $className
     * @return bool
     */
    protected function implementsContentInterface($className)
    {
        $rc = new \ReflectionClass($className);
        if($rc->implementsInterface('Ibrows\SimpleSeoBundle\Model\ContentInterface')){
            return true;
        }
        return false;
    }

    /**
     * @param $alias
     * @param $path
     * @param array $pathParameters
     * @param null $locale
     * @param null $title
     * @param null $keywords
     * @param null $description
     * @return ContentInterface
     * @internal param array $metatags
     */
    public function createNewAlias($alias, $path, array $pathParameters = array(), $locale = null, $title = null, $keywords = null, $description = null)
    {
        $rc = new \ReflectionClass($this->entityClass);
        $object = $rc->newInstance();
        if(!$object instanceof ContentInterface){
            return;
        }
        $object->setAlias($alias);
        $pathParameters[UrlGenerator::GENERATE_NORMAL_ROUTE] = true;
        $path = $this->router->generate($path, $pathParameters);
        $path = str_replace('/app_dev.php', '', $path);
        $key = $this->generator->generateMetaTagKeyFromRelativePath($path, $this->router, $locale);
        $object->setKeyword($key);
        $object->setTitle($title);
        $object->setKeywords($keywords);
        $object->setDescription($description);

        return $object;
    }

    /**
     * gets back a currently non-existing generated alias, from the given arguments
     * @param array $arguments
     * @return string
     */
    public function createAliasString( array $arguments){
        return $this->aliasStringGenerator->generateAliasString($arguments,$this);
    }


    /**
     *  gets back a currently non-existing generated alias, from the given object
     * @param AliasGeneratorArgumentsInterface $aliasGeneratorArguments
     * @return string
     */
    public function createAliasStringFromObject(AliasGeneratorArgumentsInterface $aliasGeneratorArguments){
        return $this->createAliasString($aliasGeneratorArguments->getAliasArguments());
    }

    /**
     * Returns true if there is already an alias of another path+pathParameters+locale
     * @param       $alias
     * @param       $path
     * @param array $pathParameters
     * @param null  $locale
     * @return ContentInterface
     */
    public function checkIsAliasExistsAlready($alias, $path, array $pathParameters = array(), $locale = null)
    {
        if ($alias === null) {
            return false;
        }
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
     * @param bool $flush
     * @param null $title
     * @param null $keywords
     * @param null $description
     * @return ContentInterface
     */
    public function addOrUpdateAlias($alias, $path, array $pathParameters = array(), $locale = null, $flush = true, $title = null, $keywords = null, $description = null)
    {
        $seo = $this->createNewAlias($alias, $path, $pathParameters, $locale, $title, $keywords, $description);
        $allReady = $this->findMetaTag($seo->getKeyword());
        if ($allReady) {
            $allReady->setAlias($alias);
            $allReady->setTitle($title);
            $allReady->setKeywords($keywords);
            $allReady->setDescription($description);
            $this->em->persist($allReady);
        } else {
            if ($alias == null) {
                // no alias to add
                return;
            }
            $this->em->persist($seo);
        }
        if ($flush) {
            $this->em->flush();
        }
    }

    public function setCurrentMetaTagContent(ContentMapperInterface $object)
    {
        $seo = $this->createNewAlias($object->getAlias(), $object->getFrontendViewRouteName(), $object->getFrontendViewParameters(), $object->getFrontendViewRouteLocale(), $object->getTitle(), $object->getKeywords(), $object->getDescription());
        $allReady = $this->findMetaTag($seo->getKeyword());
        if ($allReady) {
            $object->setAlias($allReady->getAlias());
            $object->setTitle($allReady->getTitle());
            $object->setKeywords($allReady->getKeywords());
            $object->setDescription($allReady->getDescription());
        }
    }

    /**
     * @param  AliasMapperInterface $object
     */
    public function checkAlias(AliasMapperInterface $object)
    {
        $checked = $this->checkIsAliasExistsAlready($object->getAlias(), $object->getFrontendViewRouteName(), $object->getFrontendViewParameters(), $object->getFrontendViewRouteLocale());
        if ($checked) {
            $object->addError('allready exists');
        }
    }

    /**
     * @param AliasMapperInterface $object
     */
    public function setCurrentAlias(AliasMapperInterface $object)
    {
        $seo = $this->createNewAlias($object->getAlias(), $object->getFrontendViewRouteName(), $object->getFrontendViewParameters(), $object->getFrontendViewRouteLocale());
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
        $this->addOrUpdateAlias($object->getAlias(), $object->getFrontendViewRouteName(), $object->getFrontendViewParameters(), $object->getFrontendViewRouteLocale());
    }

    public function addMetaTagContent(ContentMapperInterface $object)
    {
        $this->addOrUpdateAlias($object->getAlias(), $object->getFrontendViewRouteName(), $object->getFrontendViewParameters(), $object->getFrontendViewRouteLocale(), true, $object->getTitle(), $object->getKeywords(), $object->getDescription());
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
     * @return boolean
     */
    public function aliasExists($alias)
    {
        return  ($this->findByAlias($alias) != null);
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

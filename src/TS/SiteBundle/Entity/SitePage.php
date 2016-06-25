<?php
namespace TS\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\Common\Collections\ArrayCollection;


/**
 * @ORM\Entity
 */
class SitePage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
     /**
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="sitePages")
     * @Assert\NotNull()
     */
    private $site;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $url;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull()
     */
    private $html;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $showInfoBlock;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->showInfoBlock = false;
        $this->html = '';
    }

    public function __clone()
    {
        if ($this->id) {
            $this->site = null;
        }
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return SitePage
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set html
     *
     * @param string $html
     * @return SitePage
     */
    public function setHtml($html)
    {
        $this->html = $html;
    
        return $this;
    }

    /**
     * Get html
     *
     * @return string 
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return SitePage
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set showInfoBlock
     *
     * @param boolean $showInfoBlock
     * @return SitePage
     */
    public function setShowInfoBlock($showInfoBlock)
    {
        $this->showInfoBlock = $showInfoBlock;

        return $this;
    }

    /**
     * Get showInfoBlock
     *
     * @return boolean 
     */
    public function getShowInfoBlock()
    {
        return $this->showInfoBlock;
    }

    /**
     * Set site
     *
     * @param \TS\SiteBundle\Entity\Site $site
     * @return SitePage
     */
    public function setSite(\TS\SiteBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \TS\SiteBundle\Entity\Site 
     */
    public function getSite()
    {
        return $this->site;
    }
}
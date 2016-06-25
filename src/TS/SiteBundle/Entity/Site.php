<?php

namespace TS\SiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Tournament Site
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TS\SiteBundle\Entity\SiteRepository")
 */
class Site
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", mappedBy="site")
     */
    private $tournament;
    
    /**
     * @ORM\OneToMany(targetEntity="SitePage", mappedBy="site", cascade={"persist", "remove"})
     */
    private $sitePages;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="site", cascade={"persist"})
     */
    private $files;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $htmlTitle;
    
    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $htmlSubtitle;
    
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $locationAddress;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $metaKeywords;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $metaDescription;

    /**
     * @ORM\ManyToOne(targetEntity="File")
     */
    private $headerBackgroundImage;

    /**
     * @ORM\ManyToOne(targetEntity="File")
     */
    private $facebookImage;

    /**
     * @ORM\ManyToOne(targetEntity="File")
     */
    private $infoBlockImage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $infoBlockImagePosition;

    /**
     * @ORM\ManyToOne(targetEntity="File")
     */
    private $frontImage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $frontImagePosition;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $headerBackgroundImagePosition;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sitePages = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->metaKeywords = '';
        $this->metaDescription = '';
        $this->locationAddress = '';
        $this->isPublished = false;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->tournament = null;
            $this->sitePages = new ArrayCollection();
            $this->files = new ArrayCollection();
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
     * Set htmlSubtitle
     *
     * @param string $htmlSubtitle
     * @return Site
     */
    public function setHtmlSubtitle($htmlSubtitle)
    {
        $this->htmlSubtitle = $htmlSubtitle;

        return $this;
    }

    /**
     * Get htmlSubtitle
     *
     * @return string 
     */
    public function getHtmlSubtitle()
    {
        return $this->htmlSubtitle;
    }

    /**
     * Set locationAddress
     *
     * @param string $locationAddress
     * @return Site
     */
    public function setLocationAddress($locationAddress)
    {
        $this->locationAddress = $locationAddress;

        return $this;
    }

    /**
     * Get locationAddress
     *
     * @return string 
     */
    public function getLocationAddress()
    {
        return $this->locationAddress;
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return Site
     */
    public function setTournament(\TS\ApiBundle\Entity\Tournament $tournament = null)
    {
        $this->tournament = $tournament;

        return $this;
    }

    /**
     * Get tournament
     *
     * @return \TS\ApiBundle\Entity\Tournament 
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * Add files
     *
     * @param \TS\SiteBundle\Entity\File $files
     * @return Site
     */
    public function addFile(\TS\SiteBundle\Entity\File $files)
    {
        $this->files[] = $files;

        return $this;
    }

    /**
     * Remove files
     *
     * @param \TS\SiteBundle\Entity\File $files
     */
    public function removeFile(\TS\SiteBundle\Entity\File $files)
    {
        $this->files->removeElement($files);
    }

    /**
     * Get files
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set metaKeywords
     *
     * @param string $metaKeywords
     * @return Site
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * Get metaKeywords
     *
     * @return string 
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     * @return Site
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get metaDescription
     *
     * @return string 
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Add sitePages
     *
     * @param \TS\SiteBundle\Entity\SitePage $sitePages
     * @return Site
     */
    public function addSitePage(\TS\SiteBundle\Entity\SitePage $sitePages)
    {
        $this->sitePages[] = $sitePages;

        return $this;
    }

    /**
     * Remove sitePages
     *
     * @param \TS\SiteBundle\Entity\SitePage $sitePages
     */
    public function removeSitePage(\TS\SiteBundle\Entity\SitePage $sitePages)
    {
        $this->sitePages->removeElement($sitePages);
    }

    /**
     * Get sitePages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSitePages()
    {
        return $this->sitePages;
    }

    /**
     * Set headerBackgroundImage
     *
     * @param string $headerBackgroundImage
     * @return Site
     */
    public function setHeaderBackgroundImage($headerBackgroundImage)
    {
        $this->headerBackgroundImage = $headerBackgroundImage;

        return $this;
    }

    /**
     * Get headerBackgroundImage
     *
     * @return string 
     */
    public function getHeaderBackgroundImage()
    {
        return $this->headerBackgroundImage;
    }

    /**
     * Set facebookImage
     *
     * @param \TS\SiteBundle\Entity\File $facebookImage
     * @return Site
     */
    public function setFacebookImage(\TS\SiteBundle\Entity\File $facebookImage = null)
    {
        $this->facebookImage = $facebookImage;

        return $this;
    }

    /**
     * Get facebookImage
     *
     * @return \TS\SiteBundle\Entity\File 
     */
    public function getFacebookImage()
    {
        return $this->facebookImage;
    }

    /**
     * Set infoBlockImage
     *
     * @param \TS\SiteBundle\Entity\File $infoBlockImage
     * @return Site
     */
    public function setInfoBlockImage(\TS\SiteBundle\Entity\File $infoBlockImage = null)
    {
        $this->infoBlockImage = $infoBlockImage;

        return $this;
    }

    /**
     * Get infoBlockImage
     *
     * @return \TS\SiteBundle\Entity\File
     */
    public function getInfoBlockImage()
    {
        return $this->infoBlockImage;
    }

    /**
     * Set isPublished
     *
     * @param boolean $isPublished
     * @return Site
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * Get isPublished
     *
     * @return boolean 
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set frontImage
     *
     * @param \TS\SiteBundle\Entity\File $frontImage
     * @return Site
     */
    public function setFrontImage(\TS\SiteBundle\Entity\File $frontImage = null)
    {
        $this->frontImage = $frontImage;

        return $this;
    }

    /**
     * Get frontImage
     *
     * @return \TS\SiteBundle\Entity\File 
     */
    public function getFrontImage()
    {
        return $this->frontImage;
    }

    /**
     * Set infoBlockImagePosition
     *
     * @param string $infoBlockImagePosition
     * @return Site
     */
    public function setInfoBlockImagePosition($infoBlockImagePosition)
    {
        $this->infoBlockImagePosition = $infoBlockImagePosition;

        return $this;
    }

    /**
     * Get infoBlockImagePosition
     *
     * @return string 
     */
    public function getInfoBlockImagePosition()
    {
        return $this->infoBlockImagePosition;
    }

    /**
     * Set frontImagePosition
     *
     * @param string $frontImagePosition
     * @return Site
     */
    public function setFrontImagePosition($frontImagePosition)
    {
        $this->frontImagePosition = $frontImagePosition;

        return $this;
    }

    /**
     * Get frontImagePosition
     *
     * @return string 
     */
    public function getFrontImagePosition()
    {
        return $this->frontImagePosition;
    }

    /**
     * Set headerBackgroundImagePosition
     *
     * @param string $headerBackgroundImagePosition
     * @return Site
     */
    public function setHeaderBackgroundImagePosition($headerBackgroundImagePosition)
    {
        $this->headerBackgroundImagePosition = $headerBackgroundImagePosition;

        return $this;
    }

    /**
     * Get headerBackgroundImagePosition
     *
     * @return string 
     */
    public function getHeaderBackgroundImagePosition()
    {
        return $this->headerBackgroundImagePosition;
    }

    /**
     * Set htmlTitle
     *
     * @param string $htmlTitle
     * @return Site
     */
    public function setHtmlTitle($htmlTitle)
    {
        $this->htmlTitle = $htmlTitle;

        return $this;
    }

    /**
     * Get htmlTitle
     *
     * @return string 
     */
    public function getHtmlTitle()
    {
        return $this->htmlTitle;
    }
}

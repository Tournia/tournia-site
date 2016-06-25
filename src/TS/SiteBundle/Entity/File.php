<?php
namespace TS\SiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass="TS\SiteBundle\Entity\FileRepository")
 * @ORM\HasLifecycleCallbacks
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;
    
     /**
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="files")
     * @Assert\NotNull()
     */
    private $site;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $specialType;
    
    
    // temp variable used for storing \Symfony\Component\HttpFoundation\File\UploadedFile 
    private $uploadFile;

    public function getAbsolutePath()
    {
        return null === $this->getFileName()
            ? null
            : $this->getUploadRootDir().'/'.$this->getFileName();
    }

    public function getWebPath()
    {
        return null === $this->getFileName()
            ? null
            : $this->getUploadDir().'/'.$this->getFileName();
    }

    protected function getUploadRootDir()
    {
        // the absolute directory fileName where uploaded
        // documents should be saved
        return __DIR__.'/../../../../public_html'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return '/files/'. $this->site->getId();
    }
    
    public function upload(\Symfony\Component\HttpFoundation\File\UploadedFile $file, Site $site)
	{
	    // the file property can be empty if the field is not required
	    if (null === $file) {
	        return;
	    }
	    
	    $this->setSite($site);
	    $this->setSize($file->getClientSize());
	    
	    // set the fileName property, but sanitize it at least to avoid any security issues
	    
	    $this->fileName = $this->sanitize($file->getClientOriginalName());
	    
	    $this->uploadFile = $file;
	}
	
	/**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function saveUpload()
    {
        if (null === $this->uploadFile) {
            return;
        }
	    
	    // move takes the target directory and then the target filename to move to
	    $this->uploadFile->move(
	        $this->getUploadRootDir(),
	        $this->getFileName()
	    );

        unset($this->uploadFile);
    }

    /**
     * @ORM\PreRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
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
     * Set fileName
     *
     * @param string $fileName
     * @return File
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    
        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->getId() ."-". $this->fileName;
    }

    /**
     * Get original fileName
     *
     * @return string 
     */
    public function getOriginalFileName()
    {
        return $this->fileName;
    }

    /**
     * Get name for config selection
     *
     * @return string 
     */
    public function getConfigName()
    {
        if (!is_null($this->specialType)) {
            return "Samples - ". $this->fileName;
        } else {
            return $this->fileName;
        }
    }

    
    /**
	 * Function: sanitize
	 * Returns a sanitized string, typically for URLs.
	 *
	 * Parameters:
	 *     $string - The string to sanitize.
	 *     $force_lowercase - Force the string to lowercase?
	 *     $anal - If set to *true*, will remove all non-alphanumeric characters.
	 */
	private function sanitize($string, $force_lowercase = true, $anal = true) {
	    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
	                   "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
	                   "â€”", "â€“", ",", "<", ">", "/", "?");
	    $clean = trim(str_replace($strip, "", strip_tags($string)));
	    $clean = preg_replace('/\s+/', "-", $clean);
	    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9.]/", "", $clean) : $clean ;
	    return ($force_lowercase) ?
	        (function_exists('mb_strtolower')) ?
	            mb_strtolower($clean, 'UTF-8') :
	            strtolower($clean) :
	        $clean;
	}

    /**
     * Set size
     *
     * @param integer $size
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;
    
        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }
    
    /**
     * Get size in human readable text
     */
    public function getSizeText() {
    	$kb = 1024;
    	$mb = $kb * 1024;
    	$gb = $mb * 1024;
    	
    	$res = '';
    	if ($this->size < $kb) {
    		$res = $this->size ." byte";
    	} else if ($this->size < $mb) {
    		$res = number_format($this->size/$kb, 2) ." KB";
    	} else if ($this->size < $gb) {
    		$res = number_format($this->size/$mb, 2) ." MB";
    	} else {
    		$res = number_format($this->size/$gb, 2) ." GB";
    	}
    	return $res;
    }

    /**
     * Set site
     *
     * @param \TS\SiteBundle\Entity\Site $site
     * @return File
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

    /**
     * Set specialType
     *
     * @param string $specialType
     * @return File
     */
    public function setSpecialType($specialType)
    {
        $this->specialType = $specialType;

        return $this;
    }

    /**
     * Get specialType
     *
     * @return string 
     */
    public function getSpecialType()
    {
        return $this->specialType;
    }
}
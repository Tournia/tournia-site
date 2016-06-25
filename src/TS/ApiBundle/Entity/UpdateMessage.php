<?php

namespace TS\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UpdateMessage
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="TS\ApiBundle\Entity\UpdateMessageRepository")
 */
class UpdateMessage
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
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $text;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $datetime;
    
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\LoginAccount")
     **/
    private $loginAccount;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $updateSection;
    
    /**
     * @ORM\ManyToOne(targetEntity="\TS\ApiBundle\Entity\Tournament", inversedBy="updateMessages")
     *
     */
    private $tournament;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->datetime = new \DateTime("now");
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
     * Set title
     *
     * @param string $title
     * @return UpdateMessage
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
     * Set text
     *
     * @param string $text
     * @return UpdateMessage
     */
    public function setText($text)
    {
        $this->text = $text;
    
        return $this;
    }

    /**
     * Get text
     *
     * @return string 
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return UpdateMessage
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return UpdateMessage
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    
        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set updateSection
     *
     * @param string $updateSection
     * @return UpdateMessage
     */
    public function setUpdateSection($updateSection)
    {
        $this->updateSection = $updateSection;
    
        return $this;
    }

    /**
     * Get updateSection
     *
     * @return string 
     */
    public function getUpdateSection()
    {
        return $this->updateSection;
    }

    /**
     * Set loginAccount
     *
     * @param \TS\ApiBundle\Entity\LoginAccount $loginAccount
     * @return UpdateMessage
     */
    public function setLoginAccount(\TS\ApiBundle\Entity\LoginAccount $loginAccount = null)
    {
        $this->loginAccount = $loginAccount;
    
        return $this;
    }

    /**
     * Get loginAccount
     *
     * @return \TS\ApiBundle\Entity\LoginAccount 
     */
    public function getLoginAccount()
    {
        return $this->loginAccount;
    }

    /**
     * Set tournament
     *
     * @param \TS\ApiBundle\Entity\Tournament $tournament
     * @return UpdateMessage
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
}

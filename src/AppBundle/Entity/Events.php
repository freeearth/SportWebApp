<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Events
 *
 * @ORM\Table(name="events")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventsRepository")
 */
class Events
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ev_name", type="string", length=255)
     */
    private $evName;

    /**
     * @var string
     *
     * @ORM\Column(name="ev_description", type="text", nullable=true)
     */
    private $evDescription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ev_created_datetime", type="datetime")
     */
    private $evCreatedDatetime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ev_changed_datetime", type="datetime")
     */
    private $evChangedDatetime;
    
    public function __construct()   {
        #$this->roles = ['ROLE_USER'];
        $this->evCreatedDatetime= new \DateTime();
        $this->evChangedDatetime= new \DateTime();
    }
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ev_planned_datetime", type="datetime")
     */
    private $evPlannedDatetime;
    
    
    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set evName
     *
     * @param string $evName
     *
     * @return Events
     */
    public function setEvName($evName)
    {
        $this->evName = $evName;

        return $this;
    }

    /**
     * Get evName
     *
     * @return string
     */
    public function getEvName()
    {
        return $this->evName;
    }

    /**
     * Set evDescription
     *
     * @param string $evDescription
     *
     * @return Events
     */
    public function setEvDescription($evDescription)
    {
        $this->evDescription = $evDescription;

        return $this;
    }

    /**
     * Get evDescription
     *
     * @return string
     */
    public function getEvDescription()
    {
        return $this->evDescription;
    }

    /**
     * Set evCreatedDatetime
     *
     * @param \DateTime $evCreatedDatetime
     *
     * @return Events
     */
    public function setEvCreatedDatetime($evCreatedDatetime)
    {
        $this->evCreatedDatetime = $evCreatedDatetime;

        return $this;
    }

    /**
     * Get evCreatedDatetime
     *
     * @return \DateTime
     */
    public function getEvCreatedDatetime()
    {
        return $this->evCreatedDatetime;
    }

    /**
     * Set evChangedDatetime
     *
     * @param \DateTime $evChangedDatetime
     *
     * @return Events
     */
    public function setEvChangedDatetime($evChangedDatetime)
    {
        $this->evChangedDatetime = $evChangedDatetime;

        return $this;
    }

    /**
     * Get evChangedDatetime
     *
     * @return \DateTime
     */
    public function getEvChangedDatetime()
    {
        return $this->evChangedDatetime;
    }

    /**
     * Set evPlannedDatetime
     *
     * @param \DateTime $evPlannedDatetime
     *
     * @return Events
     */
    public function setEvPlannedDatetime($evPlannedDatetime)
    {
        $this->evPlannedDatetime = $evPlannedDatetime;

        return $this;
    }

    

    /**
     * Get evPlannedDatetime
     *
     * @return \DateTime
     */
    public function getEvPlannedDatetime()
    {
        return $this->evPlannedDatetime;
    }
    
    /**
     * Get user_id
     *
     * @return \Integer
     */
    public function getUser_id()
    {
        return $this->user_id;
    }
    
    
    /**
     * Set user_id
     *
     * @param \Integer $user_id
     *
     * @return Events
     */
    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }
}


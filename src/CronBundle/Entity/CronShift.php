<?php

/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 */

namespace CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CronShift
 *
 * @ORM\Table(name="cron_shift")
 * @ORM\Entity(repositoryClass="CronBundle\Repository\CronShiftRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CronShift
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="execution_date", type="datetime")
     */
    private $executionDate;




    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return CronShift
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set executionDate.
     *
     * @param \DateTime $executionDate
     *
     * @return CronShift
     */
    public function setExecutionDate($executionDate)
    {
        $this->executionDate = $executionDate;

        return $this;
    }

    /**
     * Get executionDate.
     *
     * @return \DateTime
     */
    public function getExecutionDate()
    {
        return $this->executionDate;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersistSetExecutionDate()
    {
        $this->executionDate = new \DateTime();
    }

    /**
     * @ORM\PostLoad()
     */
    public function updatedExecutionDate(): void
    {
        $this->setExecutionDate(new \DateTime('now'));
    }

}

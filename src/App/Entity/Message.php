<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @UniqueEntity(fields="slug")
 * @ORM\HasLifecycleCallbacks
 */
class Message
{
    /**
     * @ORM\Column(name="id", type="integer", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\Length(
     *      min = "2",
     *      max = "255",
     *      minMessage = "Votre titre doit au moins contenir {{ limit }} caractères",
     *      maxMessage = "Votre titre ne doit pas contenir plus de {{ limit }} caractères"
     * )
     */
    private $title;

    /**
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank(message="N'oubliez pas de renseigner le contenu de votre message")
     */
    private $content;

    /**
     * @ORM\Column(name="sendDate", type="datetime")
     * @Assert\DateTime()
     */
    private $sendDate;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sentMessages", cascade={"persist"})
     * @ORM\JoinColumn(name="sender_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid()
     */
    private $sender;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="addressedMessages", cascade={"persist"})
     * @ORM\JoinTable(name="messages_addressees")
     * @Assert\Valid()
     */
    private $addressees;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="readMessages", cascade={"persist"})
     * @ORM\JoinTable(name="messages_readers")
     * @Assert\Valid()
     */
    private $readers;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="unreadMessages", cascade={"persist"})
     * @ORM\JoinTable(name="messages_unreaders")
     * @Assert\Valid()
     */
    private $unreaders;

    public function __construct()
    {
        $this->addressees = new ArrayCollection();
        $this->readers    = new ArrayCollection();
        $this->unreaders  = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setSendDate()
    {
        $this->sendDate = new \Datetime();

        return $this;
    }

    public function getSendDate()
    {
        return $this->sendDate;
    }

    public function setSender(User $sender)
    {
        $this->sender = $sender;
        $sender->addSentMessage($this);

        return $this;
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function addAddressee(User $addressee)
    {
        $this->addressees->add($addressee);
        $addressee->addAddressedMessage($this);

        return $this;
    }

    public function hasAddressee(User $addressee)
    {
        return $this->addressees->contains($addressee);
    }

    public function removeAddressee(User $addressee)
    {
        $this->addressees->removeElement($addressee);
        $addressee->removeAddressedMessage($this);

        return $this;
    }

    public function getAddressees()
    {
        return $this->addressees;
    }

    public function addReader(User $reader)
    {
        $this->readers->add($reader);
        $this->unreaders->removeElement($reader);
        $reader->addReadMessage($this);
        $reader->removeUnreadMessage($this);

        return $this;
    }

    public function hasReader(User $reader)
    {
        return $this->readers->contains($reader);
    }

    public function removeReader(User $reader)
    {
        $this->readers->removeElement($reader);
        $reader->removeReadMessage($this);

        return $this;
    }

    public function getReaders()
    {
        return $this->readers;
    }

    public function addUnreader(User $unreader)
    {
        $this->unreaders->add($unreader);
        $unreader->addUnreadMessage($this);

        return $this;
    }

    public function hasUnreader(User $unreader)
    {
        return $this->unreaders->contains($unreader);
    }

    public function removeUnreader(User $unreader)
    {
        $this->unreaders->removeElement($unreader);
        $reader->removeUnreadMessage($this);

        return $this;
    }

    public function getUnreaders()
    {
        return $this->unreaders;
    }

    /**
     * @ORM\PrePersist()
     */
    public function initializeUnreaders()
    {
        foreach ($this->addressees as $addressee) {
            $this->addUnreader($addressee);
        }
    }

    public function editReply(Message $message)
    {
        $title = $message->getTitle();
        $content = $message->getContent();
        $sendDate = $message->getSendDate();
        $sender = $message->getSender();

        $this->addressees = new ArrayCollection();

        $this
            ->setContent(
                sprintf(
                    'Le %s à %s, %s a écrit : %s',
                    $sendDate->format('d-m-Y'),
                    $sendDate->format('H:i:s'),
                    $sender,
                    $content
                )
            )
            ->setTitle('RE: '.$title)
            ->addAddressee($sender)
        ;

        return $this;
    }
}

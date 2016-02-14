<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Knp\Rad\User\HasPassword;
use Knp\Rad\User\HasSalt;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface, \Serializable, HasPassword, HasSalt
{
    use HasPassword\HasPassword;
    use HasSalt\HasSalt;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="username", type="string", length=30, unique=true)
     */
    private $username;

    /**
     * @ORM\Column
     */
    private $password;

    /**
     * @ORM\Column
     */
    private $salt;

    /**
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\Email(message="This email adress is not valid.")
     */
    private $email;

    /**
     * @ORM\Column(name="registrationDate", type="datetime")
     */
    private $registrationDate;

    /**
     * @ORM\Column(name="roles", type="array")
     */
    private $roles;

    /**
     * @ORM\Column(name="banned", type="boolean")
     */
    private $banned;

    /**
     * @ORM\OneToMany(targetEntity="Proposal", mappedBy="mainAuthor")
     * @Assert\Valid()
     */
    private $mainProposals;

    /**
     * @ORM\OneToMany(targetEntity="ProposalDraft", mappedBy="mainAuthor")
     * @Assert\Valid()
     */
    private $mainProposalDrafts;

    /**
     * @ORM\ManyToMany(targetEntity="Proposal", mappedBy="sideAuthors", cascade={"persist"})
     * @Assert\Valid()
     */
    private $sideProposals;

    /**
     * @ORM\ManyToMany(targetEntity="ProposalDraft", mappedBy="sideAuthors", cascade={"persist"})
     * @Assert\Valid()
     */
    private $sideProposalDrafts;

    /**
     * The user claims his support to these proposals.
     *
     * @ORM\ManyToMany(targetEntity="Proposal", mappedBy="supportiveUsers", cascade={"persist"})
     * @Assert\Valid()
     */
    private $supportedProposals;

    /**
     * The user claims his opposition to these proposals.
     *
     * @ORM\ManyToMany(targetEntity="Proposal", mappedBy="opposedUsers", cascade={"persist"})
     * @Assert\Valid()
     */
    private $opposedProposals;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="sender")
     * @Assert\Valid()
     */
    private $sentMessages;

    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="addressees")
     * @Assert\Valid()
     */
    private $addressedMessages;

    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="readers")
     * @Assert\Valid()
     */
    private $readMessages;

    /**
     * @ORM\ManyToMany(targetEntity="Message", mappedBy="unreaders")
     * @Assert\Valid()
     */
    private $unreadMessages;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * Stores the name of the image file associated to the user
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * Stores temporarily the name of the old image file to delete it after the upload
     */
    private $temp;

    /**
     * @Assert\Image(
     *     minWidth = 128,
     *     maxWidth = 128,
     *     minHeight = 128,
     *     maxHeight = 128,
     *     mimeTypes = {"image/png"},
     *     maxSize = "1024k"
     * )
     */
    private $file;

    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->setBanned(false);
        $this->setRegistrationDate(new \DateTime());
        $this->setEmail(null);
        $this->mainProposals = new ArrayCollection();
        $this->sideProposals = new ArrayCollection();
        $this->supportedProposals = new ArrayCollection();
        $this->opposedProposals = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->addressedMessages = new ArrayCollection();
        $this->readMessages = new ArrayCollection();
        $this->unreadMessages = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;

        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setBanned($banned)
    {
        $this->banned = $banned;

        return $this;
    }

    public function isBanned()
    {
        return $this->banned;
    }

    public function addMainProposal(Proposal $mainProposal)
    {
        $this->mainProposals->add($mainProposal);

        return $this;
    }

    public function hasMainProposal(Proposal $mainProposal)
    {
        return $this->mainProposals->contains($mainProposal);
    }

    public function removeMainProposal(Proposal $mainProposal)
    {
        $this->mainProposals->removeElement($mainProposal);

        return $this;
    }

    public function getMainProposals()
    {
        return $this->mainProposals;
    }

    public function addMainProposalDraft(ProposalDraft $mainProposalDraft)
    {
        $this->mainProposalDrafts->add($mainProposalDraft);

        return $this;
    }

    public function hasMainProposalDraft(ProposalDraft $mainProposalDraft)
    {
        return $this->mainProposalDrafts->contains($mainProposalDraft);
    }

    public function removeMainProposalDraft(ProposalDraft $mainProposalDraft)
    {
        $this->mainProposalDrafts->removeElement($mainProposalDraft);

        return $this;
    }

    public function getMainProposalDrafts()
    {
        return $this->mainProposalDrafts;
    }

    public function addSideProposal(Proposal $sideProposal)
    {
        $this->sideProposals->add($sideProposal);

        return $this;
    }

    public function hasSideProposal(Proposal $sideProposal)
    {
        return $this->sideProposals->contains($sideProposal);
    }

    public function removeSideProposal(Proposal $sideProposal)
    {
        $this->sideProposals->removeElement($sideProposal);

        return $this;
    }

    public function getSideProposals()
    {
        return $this->sideProposals;
    }

    public function addSideProposalDraft(ProposalDraft $sideProposalDraft)
    {
        $this->sideProposalDrafts->add($sideProposalDraft);

        return $this;
    }

    public function hasSideProposalDraft(ProposalDraft $sideProposalDraft)
    {
        return $this->sideProposalDrafts->contains($sideProposalDraft);
    }

    public function removeSideProposalDraft(ProposalDraft $sideProposalDraft)
    {
        $this->sideProposalDrafts->removeElement($sideProposalDraft);

        return $this;
    }

    public function getSideProposalDrafts()
    {
        return $this->sideProposalDrafts;
    }

    public function addSupportedProposal(Proposal $supportedProposal)
    {
        $this->supportedProposals->add($supportedProposal);

        return $this;
    }

    public function hasSupportedProposal(Proposal $supportedProposal)
    {
        return $this->supportedProposals->contains($supportedProposal);
    }

    public function removeSupportedProposal(Proposal $supportedProposal)
    {
        $this->supportedProposals->removeElement($supportedProposal);

        return $this;
    }

    public function getSupportedProposals()
    {
        return $this->supportedProposals;
    }

    public function addOpposedProposal(Proposal $opposedProposal)
    {
        $this->opposedProposals->add($opposedProposal);

        return $this;
    }

    public function hasOpposedProposal(Proposal $opposedProposal)
    {
        return $this->opposedProposals->contains($opposedProposal);
    }

    public function removeOpposedProposal(Proposal $opposedProposal)
    {
        $this->opposedProposals->removeElement($opposedProposal);

        return $this;
    }

    public function getOpposedProposals()
    {
        return $this->opposedProposals;
    }

    public function addSentMessage(Message $sentMessage)
    {
        $this->sentMessages->add($sentMessage);

        return $this;
    }

    public function hasSentMessage(Message $sentMessage)
    {
        return $this->sentMessages->contains($sentMessage);
    }

    public function removeSentMessage(Message $sentMessage)
    {
        $this->sentMessages->removeElement($sentMessage);

        return $this;
    }

    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    public function addAddressedMessage(Message $addressedMessage)
    {
        $this->addressedMessages->add($addressedMessage);

        return $this;
    }

    public function hasAddressedMessage(Message $addressedMessage)
    {
        return $this->addressedMessages->contains($addressedMessage);
    }

    public function removeAddressedMessage(Message $addressedMessage)
    {
        $this->addressedMessages->removeElement($addressedMessage);

        return $this;
    }

    public function getAddressedMessages()
    {
        return $this->addressedMessages;
    }

    public function addReadMessage(Message $readMessage)
    {
        $this->readMessages->add($readMessage);

        return $this;
    }

    public function hasReadMessage(Message $readMessage)
    {
        return $this->readMessages->contains($readMessage);
    }

    public function removeReadMessage(Message $readMessage)
    {
        $this->readMessages->removeElement($readMessage);

        return $this;
    }

    public function getReadMessages()
    {
        return $this->readMessages;
    }

    public function addUnreadMessage(Message $unreadMessage)
    {
        $this->unreadMessages->add($unreadMessage);

        return $this;
    }

    public function hasUnreadMessage(Message $unreadMessage)
    {
        return $this->unreadMessages->contains($unreadMessage);
    }

    public function removeUnreadMessage(Message $unreadMessage)
    {
        $this->unreadMessages->removeElement($unreadMessage);

        return $this;
    }

    public function getUnreadMessages()
    {
        return $this->unreadMessages;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
        ]);
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list($this->id, $this->username) = unserialize($serialized);
    }

    /*****************************************************
     *****************************************************
     *
     * Methods to manage the image file
     * http://symfony.com/doc/2.3/cookbook/doctrine/file_uploads.html
     *
     *****************************************************
     *****************************************************/

    /**
     * A convenience method that returns the absolute path to the file.
     */
    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    /**
     * A convenience method that returns the web path
     * which can be used in a template to link to the uploaded file.
     */
    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'uploads/users/images';
    }

    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    public function getFile()
    {
        return $this->file;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     * Manage the path attribute
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {

            // compute a random name and try to guess the extension (more secure)
            $extension = $this->getFile()->guessExtension();
            if (!$extension) {
                // extension cannot be guessed
                $extension = 'bin';
            }

            $filename = $this->getUsername().rand(1, 99999).'.'.$extension;

            // set the path property to the filename where you've saved the file
            $this->path = $filename;
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     * Uploads the image file after persisting the user
     */
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->path
        );

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * @ORM\PostRemove()
     * Remove the image file after suppression of a user
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /*****************************************************
     *****************************************************/
}

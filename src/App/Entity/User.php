<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: \App\Repository\UserRepository::class)]
#[UniqueEntity(fields: 'username')]
#[UniqueEntity(fields: 'email')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Not persisted: hashed into $password by App\EventListener\PasswordHashListener
     * when the user is saved.
     */
    private ?string $plainPassword = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[ORM\Column(type: 'string', length: 30, unique: true)]
    private $username;

    #[ORM\Column(type: 'string', length: 64)]
    private $password;

    #[ORM\Column(type: 'string', length: 60, unique: true)]
    #[Assert\Email(message: 'This email adress is not valid.')]
    private $email;

    #[ORM\Column(type: 'datetime')]
    private $registrationDate;

    #[ORM\Column(type: 'array')]
    private $roles;

    #[ORM\Column(type: 'boolean')]
    private $banned;

    /**
     * Stores the name of the image file associated to the user
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $path;

    /**
     * Stores temporarily the name of the old image file to delete it after the upload
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $temp;

    #[Assert\Image(minWidth: 128, maxWidth: 128, minHeight: 128, maxHeight: 128, mimeTypes: ['image/png'], maxSize: '1024k')]
    private $file;

    #[ORM\OneToMany(targetEntity: \Proposal::class, mappedBy: 'author')]
    private $proposals;

    /**
     * The user claims his support to these proposals.
     */
    #[ORM\ManyToMany(targetEntity: \Proposal::class, mappedBy: 'supporters', cascade: ['persist'])]
    private $supportedProposals;

    /**
     * The user claims his opposition to these proposals.
     */
    #[ORM\ManyToMany(targetEntity: \Proposal::class, mappedBy: 'opponents', cascade: ['persist'])]
    private $opposedProposals;

    #[ORM\OneToMany(targetEntity: \PrivateDiscussion::class, mappedBy: 'admin')]
    private $adminDiscussions;

    #[ORM\ManyToMany(targetEntity: \PrivateDiscussion::class, mappedBy: 'members', cascade: ['persist'])]
    private $privateDiscussions;

    #[ORM\ManyToMany(targetEntity: \PublicDiscussion::class, mappedBy: 'followers', cascade: ['persist'])]
    private $followedDiscussions;

    #[ORM\ManyToMany(targetEntity: \AbstractDiscussion::class, mappedBy: 'unreaders', cascade: ['persist'])]
    private $unreadDiscussions;

    public function __construct()
    {
        $this->registrationDate    = new \DateTime;
        $this->roles               = ['ROLE_USER'];
        $this->banned              = false;
        $this->email               = null;
        $this->proposals           = new ArrayCollection();
        $this->supportedProposals  = new ArrayCollection();
        $this->opposedProposals    = new ArrayCollection();
        $this->adminDiscussions    = new ArrayCollection();
        $this->privateDiscussions  = new ArrayCollection();
        $this->followedDiscussions = new ArrayCollection();
        $this->unreadDiscussions   = new ArrayCollection();
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

    public function getSalt()
    {
        return null;
    }

    public function getEmail()
    {
        return $this->email;
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

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
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

    public function addProposal(Proposal $proposal)
    {
        $this->proposals->add($proposal);

        return $this;
    }

    public function hasProposal(Proposal $proposal)
    {
        return $this->proposals->contains($proposal);
    }

    public function removeProposal(Proposal $proposal)
    {
        $this->proposals->removeElement($proposal);

        return $this;
    }

    public function getProposals()
    {
        return $this->proposals;
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

    public function eraseCredentials(): void
    {
    }

    /**
     * Only what is needed to refresh the user from the session is serialized,
     * the password is replaced by a checksum (see ContextListener).
     */
    public function __serialize(): array
    {
        return [
            'id'       => $this->id,
            'username' => $this->username,
            'password' => null === $this->password ? null : hash('crc32c', $this->password),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id       = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
    }

    public function addAdminDiscussion(PrivateDiscussion $discussion)
    {
        $this->adminDiscussions->add($discussion);

        return $this;
    }

    public function removeAdminDiscussion(PrivateDiscussion $discussion)
    {
        $this->adminDiscussions->removeElement($discussion);

        return $this;
    }

    public function getAdminDiscussions()
    {
        return $this->adminDiscussions;
    }

    public function addPrivateDiscussion(PrivateDiscussion $discussion)
    {
        $this->privateDiscussions->add($discussion);

        return $this;
    }

    public function hasPrivateDiscussion(PrivateDiscussion $discussion)
    {
        return $this->privateDiscussions->contains($discussion);
    }

    public function removePrivateDiscussion(PrivateDiscussion $discussion)
    {
        $this->privateDiscussions->removeElement($discussion);

        return $this;
    }

    public function getPrivateDiscussions()
    {
        return $this->privateDiscussions;
    }

    public function addFollowedDiscussion(PublicDiscussion $discussion)
    {
        $this->followedDiscussions->add($discussion);

        return $this;
    }

    public function hasFollowedDiscussion(PublicDiscussion $discussion)
    {
        return $this->followedDiscussions->contains($discussion);
    }

    public function removeFollowedDiscussion(PublicDiscussion $discussion)
    {
        $this->followedDiscussions->removeElement($discussion);

        return $this;
    }

    public function getFollowedDiscussions()
    {
        return $this->followedDiscussions;
    }

    public function addUnreadDiscussion(AbstractDiscussion $discussion)
    {
        $this->unreadDiscussions->add($discussion);

        return $this;
    }

    public function hasUnreadDiscussion(AbstractDiscussion $discussion)
    {
        return $this->unreadDiscussions->contains($discussion);
    }

    public function removeUnreadDiscussion(AbstractDiscussion $discussion)
    {
        $this->unreadDiscussions->removeElement($discussion);

        return $this;
    }

    public function getUnreadDiscussions()
    {
        return $this->unreadDiscussions;
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

    public function setFile(?UploadedFile $file = null)
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

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
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

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
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

    #[ORM\PostRemove]
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    /*****************************************************
     *****************************************************/
}

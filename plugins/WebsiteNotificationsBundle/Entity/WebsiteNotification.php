<?php

namespace MauticPlugin\WebsiteNotificationsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Mautic\ApiBundle\Serializer\Driver\ApiMetadataDriver;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use Mautic\CoreBundle\Entity\TranslationEntityInterface;
use Mautic\CoreBundle\Entity\TranslationEntityTrait;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class WebsiteNotification.
 */
class WebsiteNotification extends FormEntity implements TranslationEntityInterface
{
    use TranslationEntityTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $image;

    /**
     * @var \Mautic\CategoryBundle\Entity\Category
     **/
    private $category;

    /**
     * @var string
     */
    private $buttonText;

    /**
     * @var string
     */
    private $buttonStyle;

    public function __clone()
    {
        $this->clearTranslations();

        parent::__clone();
    }

    /**
     * Notification constructor.
     */
    public function __construct()
    {
        $this->translationChildren = new ArrayCollection();
    }

    /**
     * @param ORM\ClassMetadata $metadata
     */
    public static function loadMetadata(ORM\ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('website_notifications')
            ->setCustomRepositoryClass('MauticPlugin\WebsiteNotificationsBundle\Entity\WebsiteNotificationRepository');

        $builder->addIdColumns();

        $builder->createField('title', 'text')
            ->build();

        $builder->createField('message', 'text')
            ->build();

        $builder->createField('url', 'text')
            ->nullable()
            ->build();

        $builder->createField('image', 'text')
            ->nullable()
            ->build();

        $builder->addCategory();

        $builder->createField('buttonText', 'text')
            ->nullable()
            ->build();

        $builder->createField('buttonStyle', 'text')
            ->nullable()
            ->build();

        self::addTranslationMetadata($builder, self::class);
    }

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint(
            'name',
            new NotBlank(
                [
                    'message' => 'mautic.core.name.required',
                ]
            )
        );
    }

    /**
     * Prepares the metadata for API usage.
     *
     * @param $metadata
     */
    public static function loadApiMetadata(ApiMetadataDriver $metadata)
    {
        $metadata->setGroupPrefix('website_notification')
            ->addListProperties(
                [
                    'id',
                    'name',
                    'title',
                    'message',
                    'url',
                    'image',
                    'language',
                    'category',
                    'buttonText',
                    'buttonStyle',
                ]
            )
            ->build();
    }

    /**
     * @param $prop
     * @param $val
     */
    protected function isChanged($prop, $val)
    {
        $getter  = 'get'.ucfirst($prop);
        $current = $this->$getter();

        if ($prop == 'translationParent' || $prop == 'category') {
            $currentId = ($current) ? $current->getId() : '';
            $newId     = ($val) ? $val->getId() : null;
            if ($currentId != $newId) {
                $this->changes[$prop] = [$currentId, $newId];
            }
        } else {
            parent::isChanged($prop, $val);
        }
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->isChanged('name', $name);
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->isChanged('description', $description);
        $this->description = $description;
    }

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
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->isChanged('category', $category);
        $this->category = $category;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->isChanged('title', $title);
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->isChanged('message', $message);
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->isChanged('url', $url);
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->isChanged('image', $image);
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getButtonText()
    {
        return $this->buttonText;
    }

    /**
     * @param string $text
     */
    public function setButtonText($text)
    {
        $this->isChanged('buttonText', $text);
        $this->buttonText = $text;
    }

    /**
     * @return string
     */
    public function getButtonStyle()
    {
        return $this->buttonStyle;
    }

    /**
     * @param string $style
     */
    public function setButtonStyle($style)
    {
        $this->isChanged('buttonStyle', $style);
        $this->buttonStyle = $style;
    }
}

<?php namespace App\Classes\Integrations\Telegram\Entities;

/**
 * Class Chat
 * @package App\Classes\Integrations\Telegram\Entities
 */
class Chat extends AbstractEntity {

    /**
     * @inheritDoc
     * @var array
     */
    protected $map = [
        'pinnedMessage' =>  Message::class
    ];

    /**
     * Type of chat, can be either “private”, “group”, “supergroup” or “channel”
     * @var null|string
     */
    protected $type = null;

    /**
     * [OPTIONAL] Title, for supergroups, channels and group chats
     * @var null|string
     */
    protected $title = null;

    /**
     * [OPTIONAL] Username, for private chats, supergroups and channels if available
     * @var null|string
     */
    protected $username = null;

    /**
     * [OPTIONAL] First name of the other party in a private chat
     * @var null|string
     */
    protected $firstName = null;

    /**
     * [OPTIONAL] Last name of the other party in a private chat
     * @var null|string
     */
    protected $lastName = null;

    /**
     * [OPTIONAL] Chat photo
     * @var null
     */
    protected $photo = null;

    /**
     * [OPTIONAL] Description, for groups, supergroups and channel chats
     * @var null|string
     */
    protected $description = null;

    /**
     * [OPTIONAL] Chat invite link, for groups, supergroups and channel chats
     * @var null|string
     */
    protected $inviteLink = null;

    /**
     * [OPTIONAL] Pinned message, for groups, supergroups and channels
     * @var null
     */
    protected $pinnedMessage = null;

    /**
     * [OPTIONAL] Default chat member permissions, for groups and supergroups
     * @var null
     */
    protected $permissions = null;

    /**
     * [OPTIONAL] For supergroups, name of group sticker set
     * @var null|string
     */
    protected $stickerSetName = null;

    /**
     * [OPTIONAL] True, if the bot can change the group sticker set
     * @var null|string
     */
    protected $canSetStickerName = null;

}

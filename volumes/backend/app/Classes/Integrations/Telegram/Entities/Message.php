<?php namespace App\Classes\Integrations\Telegram\Entities;

/**
 * Class Message
 * @package App\Classes\Integrations\Telegram\Entities
 */
class Message extends AbstractEntity {

    /**
     * @inheritDoc
     * @var array
     */
    protected $map = [
        'from'              =>  User::class,
        'chat'              =>  Chat::class,
        'forwardFrom'       =>  User::class,
        'forwardFromChat'   =>  Chat::class,
        'replyToMessage'    =>  Message::class
    ];

    /**
     * [OPTIONAL] Sender, empty for messages sent to channels
     * @var null|User
     */
    protected $from = null;

    /**
     * Date the message was sent in Unix time
     * @var null|integer
     */
    protected $date = null;

    /**
     * Conversation the message belongs to
     * @var null|Chat
     */
    protected $chat = null;

    /**
     * [OPTIONAL] For forwarded messages, sender of the original message
     * @var null|User
     */
    protected $forwardFrom = null;

    /**
     * [OPTIONAL] For messages forwarded from channels, information about the original channel
     * @var null|Chat
     */
    protected $forwardFromChat = null;

    /**
     * [OPTIONAL] For messages forwarded from channels, identifier of the original message in the channel
     * @var null|integer
     */
    protected $forwardFromMessageId = null;

    /**
     * [OPTIONAL] For messages forwarded from channels, signature of the post author if present
     * @var null|string
     */
    protected $forwardSignature = null;

    /**
     * [OPTIONAL] Sender's name for messages forwarded from users who disallow adding a link to their account in forwarded messages
     * @var null|string
     */
    protected $forwardSenderName = null;

    /**
     * [OPTIONAL] For forwarded messages, date the original message was sent in Unix time
     * @var null|integer
     */
    protected $forwardDate = null;

    /**
     * [OPTIONAL] For replies, the original message.
     * Note that the Message object in this field will not contain further reply_to_message fields even if it itself is a reply.
     * @var null|Message
     */
    protected $replyToMessage = null;

    /**
     * [OPTIONAL] Date the message was last edited in Unix time
     * @var null|integer
     */
    protected $editDate = null;

    /**
     * [OPTIONAL] The unique identifier of a media message group this message belongs to
     * @var null|string
     */
    protected $mediaGroupId = null;

    /**
     * [OPTIONAL] Signature of the post author for messages in channels
     * @var null|string
     */
    protected $authorSignature = null;

    /**
     * [OPTIONAL] For text messages, the actual UTF-8 text of the message, 0-4096 characters.
     * @var null|string
     */
    protected $text = null;

    protected $entities = null;

    protected $captionEntities = null;

    protected $audio = null;

    protected $document = null;

    protected $animation = null;

    protected $game = null;

    protected $photo = null;

    protected $sticker = null;

    protected $video = null;

    protected $voice = null;

    protected $videoNote = null;

    protected $caption = null;

    protected $contact = null;

    protected $location = null;

    protected $venue = null;

    protected $poll = null;

    protected $newChatMembers = null;

    protected $leftChatMembers = null;

    protected $newChatTitle = null;

    protected $newChatPhoto = null;

    protected $deleteChatPhoto = null;

    protected $groupChatCreated = null;

    protected $supergroupChatCreated = null;

    protected $channelChatCreated = null;

    protected $migrateToChatId = null;

    protected $migrateFromChatId = null;

    protected $pinnedMessage = null;

    protected $invoice = null;

    protected $successfulPayment = null;

    protected $connectedWebsite = null;

    protected $passportData = null;

    protected $replyMarkup = null;

}

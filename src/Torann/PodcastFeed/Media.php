<?php namespace Torann\PodcastFeed;

use DateTime;
use DOMDocument;

class Media
{
    /**
     * Title of media.
     *
     * @var string
     */
    private $title;

    /**
     * Subtitle of media.
     *
     * @var string|null
     */
    private $subtitle;

    /**
     * URL to the media web site.
     *
     * @var string
     */
    private $link;

    /**
     * Date of publication of the media.
     *
     * @var DateTime
     */
    private $pubDate;

    /**
     * description media.
     *
     * @var string
     */
    private $description;

    /**
     * URL of the media
     *
     * @var string
     */
    private $url;

    /**
     * Type of media (audio / mpeg, for example).
     *
     * @var string
     */
    private $type;

    /**
     * Length of media in bytes
     *
     * @var string
     */
     private $length;

    /**
     * Author of the media.
     *
     * @var string
     */
    private $author;

    /**
     * GUID of the media.
     *
     * @var string
     */
    private $guid;

    /**
     * isPermaLink Attribute of the GUID attribute.
     *
     * @var string
     */
     private $isPermaLink;

    /**
     * Duration of the media only as HH:MM:SS, H:MM:SS, MM:SS or M:SS.
     *
     * @var string
     */
    private $duration;

    /**
     * URL to the image representing the media..
     *
     * @var string
     */
    private $image;

     /**
     * Explicit flag of the media. Allowed: yes, explicit, true or no, clean, false
     *
     * @var string
     */
     private $explicit = null;

    /**
     * Class constructor
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->title = $this->getValue($data, 'title');
        $this->subtitle = $this->getValue($data, 'subtitle');
        $this->description = $this->getValue($data, 'description');
        $this->pubDate = $this->getValue($data, 'publish_at');
        $this->url = $this->getValue($data, 'url');
        $this->guid = $this->getValue($data, 'guid');
        $this->type = $this->getValue($data, 'type');
        $this->length = $this->getValue($data, 'length', 0);
        $this->duration = $this->getValue($data, 'duration');
        $this->author = $this->getValue($data, 'author');
        $this->image = $this->getValue($data, 'image');

        // Optional values
        $this->isPermaLink = $this->getValue($data, 'guid-is-perma-link');
        $this->explicit = $this->getValue($data, 'explicit');

        // Ensure publish date is a DateTime instance
        if (is_string($this->pubDate)) {
            $this->pubDate = new DateTime($this->pubDate);
        }
    }

    /**
     * Get value from data and escape it.
     *
     * @param  mixed  $data
     * @param  string $key
     * @param  mixed $default
     *
     * @return string
     */
    public function getValue($data, $key, $default = null)
    {
        $value = array_get($data, $key, $default);

        return htmlspecialchars($value);
    }

    /**
     * Get media publication date.
     *
     * @return  DateTime
     */
    public function getPubDate()
    {
        return $this->pubDate;
    }

    /**
     * Adds media in the DOM document setting.
     *
     * @param DOMDocument $dom
     */
    public function addToDom(DOMDocument $dom)
    {
        // Recovery of  <channel>
        $channels = $dom->getElementsByTagName("channel");
        $channel = $channels->item(0);

        // Create the <item>
        $item = $dom->createElement("item");
        $channel->appendChild($item);

        // Create the <title>
        $title = $dom->createElement("title", $this->title);
        $item->appendChild($title);

        // Create the <itunes:subtitle>
        if ($this->subtitle) {
            $itune_subtitle = $dom->createElement("itunes:subtitle", $this->subtitle);
            $item->appendChild($itune_subtitle);
        }

        // Create the <description>
        $description = $dom->createElement("description");
        $description->appendChild($dom->createCDATASection($this->description));
        $item->appendChild($description);

        // Create the <itunes:summary>
        $itune_summary = $dom->createElement("itunes:summary", $this->description);
        $item->appendChild($itune_summary);

        // Create the <pubDate>
        $pubDate = $dom->createElement("pubDate", $this->pubDate->format(DATE_RFC2822));
        $item->appendChild($pubDate);

        // Create the <enclosure>
        if($this->url && $this->type && $this->length) {
            $enclosure = $dom->createElement("enclosure");
            $enclosure->setAttribute("url", $this->url);
            $enclosure->setAttribute("type", $this->type);
            $enclosure->setAttribute("length", $this->length);
            $item->appendChild($enclosure);
        }

        // Create the author
        if ($this->author) {
            // Create the <author>
            $author = $dom->createElement("author", $this->author);
            $item->appendChild($author);

            // Create the <itunes:author>
            $itune_author = $dom->createElement("itunes:author", $this->author);
            $item->appendChild($itune_author);
        }

        // Create the <itunes:duration>
        $itune_duration = $dom->createElement("itunes:duration", $this->duration);
        $item->appendChild($itune_duration);

        // Create the <guid>
        $guid = $dom->createElement("guid", $this->guid);
        if($this->isPermaLink != null){
            $guid->setAttribute('isPermaLink', $this->isPermaLink);
        }
        $item->appendChild($guid);

        // Create the <itunes:image>
        if ($this->image) {
            $itune_image = $dom->createElement("itunes:image");
            $itune_image->setAttribute("href", $this->image);
            $item->appendChild($itune_image);
        }

        // Create the <itunes:explicit>
        if ($this->explicit != null) {
            $explicit = $dom->createElement("itunes:explicit", $this->explicit);
            $channel->appendChild($explicit);
        }
    }
}
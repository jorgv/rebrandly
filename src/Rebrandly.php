<?php
namespace Jorgv\Rebrandly;

use Jorgv\Rebrandly\Services\Http;

/**
 * Link Service
 *
 * Handles API requests for all link related actions
 */
class Rebrandly
{
    /**
     * @var array Lists of which fields are required for particular operations
     *    Used for validation of link models before submission to the API
     */
    const REQUIREDFIELDS = [
        'create' => ['destination'],
        'update' => ['destination', 'slashtag', 'title', 'domain', 'favourite'],
    ];
    /**
     * @var Http $http HTTP helper class shared by all Rebrandly SDK services
     */
    private $http;
    /**
     * @param string $apiKey
     */
    public function __construct()
    {
        $this->http = new Http(config('rebrandly.key'));
    }
    /**
     * Ensures all required fields for the requested action exist on a $link
     *
     * While the link model includes its own validation of fields on assignment
     * and hence we can trust that any set data is of the correct type etc, the
     * link model doesn't know about the API's requirements.
     *
     * @param string $action The action being performed, used to look up which
     *    fields are required
     *
     * @param array $linkArray Array describing the link being validated
     */
    private function validate($action, $linkArray)
    {
        $missing = array_flip(array_diff_key(array_flip(self::REQUIREDFIELDS[$action]), $linkArray));
        if (count($missing) > 0) {
            throw new \InvalidArgumentException("Missing required fields: " . join(' ', $missing));
        }
    }

    /**
     * Creates a link given LinkModel with any desired details
     *
     * @param array $link Any fields the user wishes to set on the link before
     *    creation
     *
     * @return $link A link populated with the response from the
     *    Rebrandly API.
     */
    public function create($slashtag, $destination, $domain = null)
    {
        $target = 'links';
        if (!isset($domain)) {
            $domain = config('rebrandly.domain');
        }
        $linkArray = [$slashtag, $destination, $domain];
        $this->validate('create', $linkArray);
        $response = $this->http->post($target, $linkArray);
        return $response;
    }

    /**
     * Gets full details of a single link given its ID
     *
     * @param string $linkId the ID of the link, as provided on creation by
     *    Rebrandly
     *
     * @return LinkModel $link A populated link as returned from the API
     */
    public function getOne($linkId)
    {
        if (!is_integer($linkId) && !is_string($linkId)) {
            $type = gettype($linkId);
            $errorText = printf('Expected linkId to be stringy or an integer, %s supplied', $type);
            throw new \InvalidArgumentException($errorText);
        }
        $target = 'links/' . $linkId;
        $response = $this->http->get($target);
        $link = LinkModel::import($response);
        return $link;
    }
    /**
     * Deletes (optionally: permanently) a link
     *
     * @param LinkModel $link The link to delete
     *
     * @param boolean $permanent Permanently deletes the link, rather than
     *    marking it as inactive.
     *
     * TODO: Check what this response actually is
     * @return array $response Whatever response the API gives us.
     */
    public function delete(LinkModel $link, $permanent = true)
    {
        $linkId = $link->getId();
        $response = $this->deleteById($linkId, $permanent);
        return $response;
    }
    /**
     * Alternate means to call delete, accepting a link ID rather than a full
     * link model.
     *
     * @param integer $linkId The link ID to delete
     *
     * @param boolean $permanent Permanently deletes the link, rather than
     *    marking it as inactive.
     *
     * TODO: Check what this response actually is
     * @return array $response Whatever response the API gives us.
     */
    public function deleteById($linkId, $permanent = true)
    {
        $target = 'links/' . $linkId;
        $params = [
            'trash' => !$permanent,
        ];
        $response = $this->http->delete($target, $params);
        return $response;
    }
    /**
     * Search for links meeting some criteria, with sorting controls
     *
     * @param array $filters A list of parameters to filter and sort by
     *
     * @return LinkModel[] $links A list of links that meet the given criteria
     */
    public function search($filters = [])
    {
        $target = 'links/';
        $response = $this->http->get($target, $filters);
        $links = [];
        foreach ($response as $linkArray) {
            $link = LinkModel::import($linkArray);
            array_push($links, $link);
        }
        return $links;
    }
    /**
     * Count links meeting some criteria
     *
     * @param array $filters A list of parameters to filter by
     *
     * @return integer $count A count of links that meet the given criteria
     */
    public function count($filters = [])
    {
        $target = 'links/count';
        $response = $this->http->get($target, $filters);
        return $response;
    }
}

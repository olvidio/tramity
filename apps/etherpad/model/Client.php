<?php

namespace etherpad\model;


use core\ConfigGlobal;

/**
 * Class Client
 *
 * @package EtherpadLite
 *
 * @method Response createGroup() creates a new group
 * @method Response createGroupIfNotExistsFor($groupMapper) this functions helps you to map your application group ids to etherpad lite group ids
 * @method Response deleteGroup($groupID) deletes a group
 * @method Response listPads($groupID) returns all pads of this group
 * @method Response createGroupPad($groupID, $padName, $text = null) creates a new pad in this group
 * @method Response listAllGroups() lists all existing groups
 *
 * @method Response createAuthor($name = null) creates a new author
 * @method Response createAuthorIfNotExistsFor($authorMapper, $name = null) this functions helps you to map your application author ids to etherpad lite author ids
 * @method Response listPadsOfAuthor($authorID) returns an array of all pads this author contributed to
 * @method Response getAuthorName($authorID) Returns the Author Name of the author
 *
 * @method Response createSession($groupID, $authorID, $validUntil) creates a new session. validUntil is an unix timestamp in seconds
 * @method Response deleteSession($sessionID) deletes a session by id
 * @method Response getSessionInfo($sessionID) returns informations about a session
 * @method Response listSessionsOfGroup($groupID) returns all sessions of a group
 * @method Response listSessionsOfAuthor($authorID) returns all sessions of an author
 *
 * @method Response getText($padID, $rev = null) returns the text of a pad
 * @method Response setText($padID, $text) sets the text of a pad
 * @method Response getHTML($padID, $rev = null) returns the text of a pad formatted as HTML
 * @method Response setHTML($padID, $html) sets the HTML of a pad
 * @method Response copyPad($sourceID, $destinationID, $force = false) copy a pad
 * @method Response appendText($padID, $text) appends text to a pad
 *
 * @method Response getChatHistory($padID, $start = null, $end = null) a part of the chat history, when start and end are given, the whole chat histroy, when no extra parameters are given
 * @method Response getChatHead($padID) returns the chatHead (last number of the last chat-message) of the pad
 *
 * @method Response createPad($padID, $text = null) creates a new (non-group) pad. Note that if you need to create a group Pad, you should call createGroupPad.
 * @method Response getRevisionsCount($padID) returns the number of revisions of this pad
 * @method Response padUsersCount($padID) returns the number of user that are currently editing this pad
 * @method Response padUsers($padID) returns the list of users that are currently editing this pad
 * @method Response deletePad($padID) deletes a pad
 * @method Response getReadOnlyID($padID) returns the read only link of a pad
 * @method Response setPublicStatus($padID, $publicStatus) sets a boolean for the public status of a pad
 * @method Response getPublicStatus($padID) return true of false
 * @method Response setPassword($padID, $password) returns ok or a error message
 * @method Response isPasswordProtected($padID) returns true or false
 * @method Response listAuthorsOfPad($padID) returns an array of authors who contributed to this pad
 * @method Response getLastEdited($padID) returns the timestamp of the last revision of the pad
 * @method Response sendClientsMessage($padID, $msg) sends a custom message of type $msg to the pad
 * @method Response checkToken() returns ok when the current api token is valid
 *
 * @method Response listAllPads() lists all pads on this epl instance
 *
 * @method copyPadWithoutHistory($sourceID, $destinationID, $force = false). copies a pad without copying the history and chat.
 *                                                If force is true and the destination pad exists, it will be overwritten.
 *
 * @method movePad($sourceID, $destinationID, $force = false). moves a pad. If force is true and the destination pad exists, it will be overwritten.
 */
class Client
{
    /**
     * Para saber la versión que está corriendo en el servidor, consultar:
     *      http://etherpad.docker:9001/api
     *
     * @var string|null
     */
    protected $api_version = null;
    protected $api_version_docker =  '1.3.0';
    protected $api_version_local = '1.2.15';
    protected $api_version_dlb = '1.2.15';

    /**
     * Se encuentra en el servidor etherpad en;
     * tramity:/opt/etherpad/etherpad-lite/APIKEY.txt
     *
     * @var string|null
     */
    protected $apikey = null;
    protected $apikey_local = '255a27fbe84ca4f15720a75ed58c603f2f325146eda850741bec357b0942e546';
    protected $apikey_dlb = '7114153c4b981f57380f3bdb65444daed5e15efca3ec54ffa48f66270f927b50';

    /**
     * @var string|null
     */
    protected $url = null;

    /**
     * @param string $apikey
     * @param string $url
     */
    public function __construct(string $apikey, string $url)
    {
        $this->apikey = $apikey;
        $this->url = $url;
    }

    /**
     * @param string $method
     * @param array $args
     * @return Response
     * @throws UnsupportedMethodException
     */
    public function __call(string $method, $args = []): Response
    {
        if (!in_array($method, array_keys(self::getMethods()))) {
            throw new UnsupportedMethodException();
        }

        $request = new Request($this->url, $this->apikey, $method, $args);

        return new Response($request->send());
    }

    /**
     * Array that holds the available methods and their required parameter names
     *
     * @return array
     */
    public static function getMethods(): array
    {
        return [
            'createGroup' => [],
            'createGroupIfNotExistsFor' => ['groupMapper'],
            'deleteGroup' => ['groupID'],
            'listPads' => ['groupID'],
            'createGroupPad' => ['groupID', 'padName', 'text'],
            'listAllGroups' => [],
            'createAuthor' => ['name'],
            'createAuthorIfNotExistsFor' => ['authorMapper', 'name'],
            'listPadsOfAuthor' => ['authorID'],
            'getAuthorName' => ['authorID'],
            'createSession' => ['groupID', 'authorID', 'validUntil'],
            'deleteSession' => ['sessionID'],
            'getSessionInfo' => ['sessionID'],
            'listSessionsOfGroup' => ['groupID'],
            'listSessionsOfAuthor' => ['authorID'],
            'getText' => ['padID', 'rev'],
            'setText' => ['padID', 'text'],
            'getHTML' => ['padID', 'rev'],
            'setHTML' => ['padID', 'html'],
            'getAttributePool' => ['padID'],
            'getChatHistory' => ['padID', 'start', 'end'],
            'getChatHead' => ['padID'],
            'createPad' => ['padID', 'text'],
            'getRevisionsCount' => ['padID'],
            'listSavedRevisions' => ['padID'],
            'padUsersCount' => ['padID'],
            'padUsers' => ['padID'],
            'deletePad' => ['padID'],
            'getReadOnlyID' => ['padID'],
            'setPublicStatus' => ['padID', 'publicStatus'],
            'getPublicStatus' => ['padID'],
            'setPassword' => ['padID', 'password'],
            'isPasswordProtected' => ['padID'],
            'listAuthorsOfPad' => ['padID'],
            'getLastEdited' => ['padID'],
            'sendClientsMessage' => ['padID', 'msg'],
            'checkToken' => [],
            'listAllPads' => [],
            // provo d'afegir...
            'copyPad' => ['sourceID', 'destinationID', 'force'],
            'appendText' => ['padID', 'text'],
            'copyPadWithoutHistory' => ['sourceID', 'destinationID', 'force'],
            'movePad' => ['sourceID', 'destinationID', 'force'],
            'mostrar_error' => [],
        ];


    }

    /*
     Response Format
     #
     
     Responses are valid JSON in the following format:
     
     {
     "code": number,
     "message": string,
     "data": obj
     }
     
     code a return code
     0 everything ok
     1 wrong parameters
     2 internal error
     3 no such function
     4 no or wrong API Key
     message a status message. Its ok if everything is fine, else it contains an error message
     data the payload
     */

    /**
     * Generates a random padID
     *
     * @return string
     */
    public function generatePadID(): string
    {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $length = 16;
        $padID = "";

        for ($i = 0; $i < $length; $i++) {
            $padID .= $chars[mt_rand() % strlen($chars)];
        }

        return $padID;
    }

    public function mostrar_error($rta)
    {
        $a_codes = [
            0 => 'everything ok',
            1 => 'wrong parameters',
            2 => 'internal error',
            3 => 'no such function',
            4 => 'no or wrong API Key',
        ];
        $code = $rta->getCode();
        $message = $rta->getMessage();

        $html = "*Error: " . $a_codes[$code];
        $html .= "<br>";
        $html .= $message;
        $html .= "**<br>";

        //echo $html;
        return $html;
    }
}

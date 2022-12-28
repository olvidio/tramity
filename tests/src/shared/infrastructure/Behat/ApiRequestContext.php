<?php

namespace Tramity\Tests\shared\infrastructure\Behat;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Session;
use Behat\MinkExtension\Context\RawMinkContext;
use Tramity\Tests\shared\infrastructure\mink\MinkHelper;
use Tramity\Tests\shared\infrastructure\mink\MinkSessionRequestHelper;

final class ApiRequestContext extends RawMinkContext
{
    private $request;

    public function __construct(Session $session)
    {
        $this->request = new MinkSessionRequestHelper(new MinkHelper($session));
    }

    /**
     * @Given I send a :method request to :url
     */
    public function iSendARequestTo($method, $url): void
    {
        $this->request->sendRequest($method, $this->locatePath($url));
    }

    /**
     * @Given I send a :method request to :url with body:
     */
    public function iSendARequestToWithBody($method, $url, PyStringNode $body): void
    {
        $this->request->sendRequestWithPyStringNode($method, $this->locatePath($url), $body);
    }
}

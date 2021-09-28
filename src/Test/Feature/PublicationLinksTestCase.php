<?php

namespace Jetimob\Orulo\Test\Feature;

use Jetimob\Orulo\Lib\Http\Api\Building\PublicationLinksRequest;
use Jetimob\Orulo\Lib\Http\Api\Building\PublicationLinksResponse;
use Jetimob\Orulo\Lib\Http\Model\PublicationLink;
use Jetimob\Orulo\Test\TestCase;

class PublicationLinksTestCase extends TestCase
{
    public function testLinksRequest()
    {
        $request = new PublicationLinksRequest('');
        $request->setPublicationLinks([PublicationLink::new('https://www.jetimob.com', true)]);

        /** @var  PublicationLinksResponse $response */
        $response = $this->makeAndAssert($request);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
